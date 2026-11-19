@if ($options['footer_credit'])
    <span aria-label="Additional service attribution"> &middot; {{ $options['footer_credit'] }}</span>
@endif
@if ($options['support_url'])
    <span> &middot; <a target="_blank" rel="noopener noreferrer" href="{{ $options['support_url'] }}">Support</a></span>
@endif
@if ($options['documentation_url'])
    <span> &middot; <a target="_blank" rel="noopener noreferrer" href="{{ $options['documentation_url'] }}">Documentation</a></span>
@endif
