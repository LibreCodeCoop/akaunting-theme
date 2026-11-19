<span id="lcc-theme-footer-additions">
@if ($options['footer_credit'])
    <span aria-label="Additional service attribution"> &middot; {{ $options['footer_credit'] }}</span>
@endif
@if ($options['support_url'])
    <span> &middot; <a target="_blank" rel="noopener noreferrer" href="{{ $options['support_url'] }}">Support</a></span>
@endif
@if ($options['documentation_url'])
    <span> &middot; <a target="_blank" rel="noopener noreferrer" href="{{ $options['documentation_url'] }}">Documentation</a></span>
@endif

</span>
<script>
(function () {
    const additions = document.getElementById("lcc-theme-footer-additions");
    const footer = document.querySelector("footer.footer");
    if (additions && footer) footer.appendChild(additions);
})();
</script>
