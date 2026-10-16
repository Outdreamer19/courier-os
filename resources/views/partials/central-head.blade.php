{{--
    Central marketing domain only (never a tenant's white-label site).

    The marketing pages are client-rendered, so without these tags WhatsApp,
    Instagram and Facebook link previews only ever saw the Blade shell: the
    APP_NAME title ("Ship'd JM") and the Today Shipping favicon. Crawlers do not
    run JavaScript, so share text and images have to be in the first response.
--}}
@php
    $meta = array_merge([
        'title' => config('courieros.name', 'CourierOS'),
        'description' => 'The branded website, customer portal and admin console behind Caribbean shop-and-ship courier businesses.',
        'image' => '/images/marketing/og/courieros-og.png',
    ], $meta ?? []);
    $metaImage = str_starts_with($meta['image'], 'http') ? $meta['image'] : url($meta['image']);
    $pixelId = config('courieros.marketing.meta_pixel_id');
    $gtagId = config('courieros.marketing.google_tag_id');
    $googleLead = config('courieros.marketing.google_ads_lead');
@endphp
<link rel="icon" href="/branding/courieros-icon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/branding/courieros-icon-180.png">
<meta name="theme-color" content="#210d02">
<meta name="description" content="{{ $meta['description'] }}">
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:site_name" content="{{ config('courieros.name', 'CourierOS') }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="{{ $meta['title'] }}">
<meta property="og:description" content="{{ $meta['description'] }}">
<meta property="og:image" content="{{ $metaImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $meta['title'] }}">
<meta name="twitter:description" content="{{ $meta['description'] }}">
<meta name="twitter:image" content="{{ $metaImage }}">

@if ($pixelId)
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', @json($pixelId));
fbq('track', 'PageView');
</script>
@endif

@if ($gtagId)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gtagId) }}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', @json($gtagId));
window.__courierosAds = { googleLeadSendTo: @json($googleLead) };
</script>
@endif
