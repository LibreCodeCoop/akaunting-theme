@php
    $background = $options['login_background'] ? asset('public/storage/' . $options['login_background']) : null;
    $favicon = $options['favicon'] ? asset('public/storage/' . $options['favicon']) : null;
    $logo = $options['logo'] ? asset('public/storage/' . $options['logo']) : null;
@endphp
@if ($favicon)
<link rel="icon" href="{{ $favicon }}" type="image/png">
@endif
<style>
@if ($background)
#app { background-image: url("{{ $background }}") !important; }
@endif
@if (! $options['login_slider'])
#app .swiper { display: none !important; }
@endif
</style>

@if ($logo)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const original = document.querySelector('#app img[src*="akaunting-logo"]');
    if (!original) return;
    const companyLogo = document.createElement('img');
    companyLogo.src = @json($logo);
    companyLogo.alt = @json($options['brand_name'] ?: 'Company');
    companyLogo.style.cssText = 'height:3.5rem;width:auto;max-width:12rem;object-fit:contain;margin-top:0.75rem';
    original.insertAdjacentElement('afterend', companyLogo);
});
</script>
@endif
