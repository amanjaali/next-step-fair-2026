<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Role-based access, as specified: Super Admin, Registration Manager,
 * Content Editor, Check-in Staff and Sponsor Manager.
 *
 * Registration Volunteer is the door-desk counterpart to Registration
 * Manager: it gets only the lightweight /registration desk, never the
 * Filament admin panel — a volunteer working a shift at the door shouldn't
 * also inherit edit/cancel/export access to every registration in the system.
 *
 * Registration Monitor is the opposite trade: the Filament admin panel, but
 * strictly read-only — Fair registrations and Conference RSVPs, nothing to
 * click but View.
 */
class RoleSeeder extends Seeder
{
    public const PERMISSIONS = [
        // Registrations
        'view-registrations', 'edit-registrations', 'approve-registrations',
        'cancel-registrations', 'export-registrations', 'create-walkins',
        'access-registration-desk',
        // Messaging
        'view-messages', 'send-messages', 'manage-templates',
        // Content
        'manage-content', 'publish-content', 'manage-media',
        // Partners
        'manage-sponsors', 'manage-leads',
        // Gate
        'scan-tickets',
        // Scholarship
        'review-scholarships',
        // Platform
        'manage-users', 'manage-settings', 'view-analytics', 'manage-qr-campaigns',
    ];

    public const ROLES = [
        'Super Admin' => 'all',
        'Registration Manager' => [
            'view-registrations', 'edit-registrations', 'approve-registrations',
            'cancel-registrations', 'export-registrations', 'create-walkins',
            'access-registration-desk',
            'view-messages', 'send-messages', 'manage-templates',
            'scan-tickets', 'view-analytics', 'manage-qr-campaigns',
        ],
        'Registration Volunteer' => [
            'access-registration-desk',
        ],
        /*
         * Read-only: sees Fair registrations and Conference RSVPs in the admin
         * panel, can open a record to view it, and nothing else — no create,
         * edit, cancel, resend, regenerate, delete or export. Every other
         * resource in the admin stays invisible, the same way it already is
         * for every role below that doesn't hold its permission.
         */
        'Registration Monitor' => [
            'view-registrations',
        ],
        'Content Editor' => [
            'manage-content', 'publish-content', 'manage-media', 'view-analytics',
        ],
        'Check-in Staff' => [
            'scan-tickets',
        ],
        'Sponsor Manager' => [
            'manage-sponsors', 'manage-leads', 'view-analytics', 'manage-qr-campaigns',
        ],
        /*
         * The scholarship committee reads applications and moves them through the
         * stages. Deliberately nothing else: a committee member is often external
         * to the organisation, and has no business in registrations or messaging.
         */
        'Scholarship Committee' => [
            'review-scholarships',
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::ROLES as $name => $permissions) {
            $role = Role::findOrCreate($name, 'web');
            $role->syncPermissions($permissions === 'all' ? self::PERMISSIONS : $permissions);
        }

        /*
         * The password these accounts are created with.
         *
         * "password" only where the site is a laptop. Anywhere else the seeder
         * insists on being told one, or invents a strong one and prints it
         * once — because a known password on an account that can issue entry
         * credentials is not a placeholder, it is a way in, and the repository
         * this seeder lives in is public.
         */
        $password = (string) (env('SEED_STAFF_PASSWORD') ?: (
            app()->environment('local') ? 'password' : Str::password(20)
        ));

        if ($password !== 'password' && ! env('SEED_STAFF_PASSWORD')) {
            $this->command?->warn("Staff accounts created with this password — save it now, it is not shown again:\n\n    {$password}\n");
        }

        $accounts = [
            ['Avin Qadir', 'admin@nextstepfair.com', 'Super Admin', 'Organizer and Co-Founder'],
            ['Registration Desk', 'registration@nextstepfair.com', 'Registration Manager', 'Registration Manager'],
            ['Registration Volunteer', 'volunteer@nextstepfair.com', 'Registration Volunteer', 'Registration Volunteer'],
            ['Newsroom', 'editor@nextstepfair.com', 'Content Editor', 'Content Editor'],
            ['Gate Staff', 'gate@nextstepfair.com', 'Check-in Staff', 'Check-in Staff'],
            ['Partnerships', 'partnerships@nextstepfair.com', 'Sponsor Manager', 'Sponsor Manager'],
            ['Scholarship Committee', 'committee@nextstepfair.com', 'Scholarship Committee', 'Committee Member'],
        ];

        foreach ($accounts as [$name, $email, $role, $title]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                    'job_title' => $title,
                    'locale' => 'en',
                    'is_active' => true,
                    'default_gate' => $role === 'Check-in Staff' ? 'A' : null,
                ]
            );

            $user->syncRoles([$role]);
        }
    }
}
