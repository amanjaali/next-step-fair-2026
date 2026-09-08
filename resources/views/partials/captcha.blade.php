{{--
    The code from the picture.

    It sits directly above the submit button, because that is where somebody
    looks last. The picture is reloaded by appending a changing query string
    rather than by anything clever: the server draws a new challenge on every
    request, so a new URL is a new code.
--}}
<div class="mb-6">
    <label for="ns-captcha" class="ns-label">{{ __('register.captcha.label') }}</label>

    <div class="flex items-start gap-3 flex-wrap mt-2">
        <div class="shrink-0">
            <img id="ns-captcha-image"
                 src="{{ route('captcha') }}"
                 alt="{{ __('register.captcha.alt') }}"
                 width="220" height="70"
                 class="block border border-[rgba(5,7,8,0.16)] bg-bone-200"
                 dir="ltr">

            <button type="button"
                    class="font-[family-name:var(--ns-body)] text-[12.5px] font-bold text-magenta bg-transparent border-0 p-0 mt-[6px] cursor-pointer"
                    onclick="document.getElementById('ns-captcha-image').src='{{ route('captcha') }}?r='+Date.now()">
                {{ __('register.captcha.refresh') }}
            </button>
        </div>

        <div class="flex-1 min-w-[180px]">
            <input id="ns-captcha"
                   type="text"
                   name="captcha"
                   value=""
                   required
                   autocomplete="off"
                   autocapitalize="characters"
                   spellcheck="false"
                   inputmode="text"
                   maxlength="8"
                   dir="ltr"
                   placeholder="{{ __('register.captcha.placeholder') }}"
                   class="ns-input ns-num w-full uppercase tracking-[0.3em]">

            <p class="ns-meta text-[12px] mt-2 max-w-[38ch]">{{ __('register.captcha.help') }}</p>

            @error('captcha')
                <p class="ns-error mt-2">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
