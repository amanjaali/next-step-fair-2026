<?php

namespace App\Http\Controllers\Institution;

use App\Http\Controllers\Controller;
use App\Models\InstitutionUser;
use App\Models\Organization;
use App\Services\Matching\InstitutionOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The way a university gets into its own portal.
 *
 * Two doors. An institution already in the directory *claims* its entry: a member
 * of staff registers against it and Next Step approves the claim. One that is not
 * listed registers from scratch and arrives as a pending organisation.
 *
 * Either way there is no password — the code goes to the institutional e-mail
 * address, which is the thing that proves someone speaks for the institution.
 */
class PortalAuthController extends Controller
{
    public function __construct(private readonly InstitutionOtpService $otp) {}

    public function show(): View|RedirectResponse
    {
        if (Auth::guard('institution')->check()) {
            return redirect()->route('portal.dashboard');
        }

        return view('institution.signin', [
            'navKey' => null,
            'title' => __('institution.signin.title').' — '.config('nextstep.event.name'),
        ]);
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:190'],
        ], ['email.required' => __('institution.signin.errors.email')]);

        $user = InstitutionUser::active()->whereEmail($data['email'])->first();

        if ($user) {
            $this->otp->send($user);
            $request->session()->put('institution.signin_id', $user->id);
        }

        // No confirmation either way: the portal must not reveal which addresses
        // belong to a registered exhibitor.
        return redirect()->route('portal.signin.code')->with('email', $data['email']);
    }

    public function showCode(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('institution.signin_id') && ! session()->has('email')) {
            return redirect()->route('portal.signin');
        }

        $user = $this->pending($request);

        return view('institution.signin-code', [
            'navKey' => null,
            'title' => __('institution.signin.code_title').' — '.config('nextstep.event.name'),
            'email' => session('email'),
            'testingCode' => $user ? $this->otp->testingCode($user) : null,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:'.config('whatsapp.otp.length')],
        ], ['code.digits' => __('register.errors.otp')]);

        $user = $this->pending($request);

        if (! $user || $this->otp->verify($user, (string) $request->input('code')) !== 'verified') {
            return back()->withErrors(['code' => __('register.step4.wrong_code')]);
        }

        Auth::guard('institution')->login($user, remember: true);
        $request->session()->regenerate();
        $request->session()->forget('institution.signin_id');
        $user->forceFill(['last_signed_in_at' => now()])->save();

        return redirect()->intended(route('portal.dashboard'));
    }

    public function signOut(Request $request): RedirectResponse
    {
        Auth::guard('institution')->logout();
        $request->session()->regenerate();

        return redirect()->route('portal.signin')->with('status', __('institution.signin.signed_out'));
    }

    /* ------------------------------------------------------------ register -- */

    public function create(): View
    {
        return view('institution.register', [
            'navKey' => null,
            'title' => __('institution.register.title').' — '.config('nextstep.event.name'),
            // Directory entries a member of staff can claim rather than duplicate.
            'claimable' => Organization::whereIn('kind', [Organization::KIND_UNIVERSITY, Organization::KIND_INSTITUTE])
                ->where('claim_status', 'unclaimed')
                ->orderBy('slug')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:32'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
            'institution_name' => ['required_without:organization_id', 'nullable', 'string', 'max:190'],
            'country' => ['required', 'string', 'max:4'],
            'kind' => ['required', Rule::in([Organization::KIND_UNIVERSITY, Organization::KIND_INSTITUTE])],
            'consent_terms' => ['accepted'],
        ], [
            'email.required' => __('institution.register.errors.email'),
            'institution_name.required_without' => __('institution.register.errors.institution'),
            'consent_terms.accepted' => __('register.errors.terms'),
        ]);

        // Already registered: send them to sign-in rather than creating a second
        // account against the same address.
        if (InstitutionUser::whereEmail($data['email'])->exists()) {
            return redirect()->route('portal.signin')
                ->with('status', __('institution.register.already'));
        }

        $organization = $data['organization_id']
            ? Organization::findOrFail($data['organization_id'])
            : Organization::create([
                'slug' => str($data['institution_name'])->slug()->append('-'.str()->random(4))->toString(),
                'kind' => $data['kind'],
                'name' => array_fill_keys(array_keys(config('nextstep.locales')), $data['institution_name']),
                'country' => $data['country'],
                'year' => (int) config('nextstep.event.year'),
                // Not on the public directory until Next Step approves it.
                'published' => false,
            ]);

        $organization->forceFill([
            'claim_status' => 'pending',
            'claimed_at' => now(),
            'contact_email' => $data['email'],
        ])->save();

        $user = InstitutionUser::create([
            'organization_id' => $organization->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'job_title' => $data['job_title'] ?? null,
            'phone' => $data['phone'] ?? null,
            'locale' => app()->getLocale(),
            'role' => InstitutionUser::ROLE_OWNER,
        ]);

        $this->otp->send($user);
        $request->session()->put('institution.signin_id', $user->id);

        return redirect()->route('portal.signin.code')->with('email', $data['email']);
    }

    private function pending(Request $request): ?InstitutionUser
    {
        $id = $request->session()->get('institution.signin_id');

        return $id ? InstitutionUser::find($id) : null;
    }
}
