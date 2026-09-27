{{-- GA4, Meta and TikTok pixels. Each one only renders when its id is configured. --}}
@if ($id = config('nextstep.analytics.ga4'))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $id }}', { anonymize_ip: true });
    </script>
@endif

@if ($id = config('nextstep.analytics.meta_pixel'))
    <script>
        !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
        n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
        document,'script','https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $id }}'); fbq('track', 'PageView');
    </script>
@endif

@if ($id = config('nextstep.analytics.tiktok_pixel'))
    <script>
        !function (w, d, t) { w.TiktokAnalyticsObject=t; var ttq=w[t]=w[t]||[];
        ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];
        ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
        for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
        ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js";
        ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,
        ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=d.createElement("script");o.type="text/javascript";
        o.async=!0;o.src=r+"?sdkid="+e+"&lib="+t;var a=d.getElementsByTagName("script")[0];
        a.parentNode.insertBefore(o,a)};
        ttq.load('{{ $id }}'); ttq.page(); }(window, document, 'ttq');
    </script>
@endif

{{-- Conversion event, fired once on a completed registration or RSVP. --}}
@if (session('conversion'))
    @php $conversion = session('conversion'); @endphp
    <script>
        window.addEventListener('load', function () {
            var value = @json($conversion);
            if (window.gtag) gtag('event', 'registration_complete', value);
            if (window.fbq) fbq('track', 'CompleteRegistration', value);
            if (window.ttq) ttq.track('CompleteRegistration', value);
        });
    </script>
@endif
