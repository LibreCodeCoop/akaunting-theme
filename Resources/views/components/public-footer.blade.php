{{-- Preserve the Akaunting footer; this is strictly additive. --}}
@if (trim((string) ($options['footer_text'] ?? '')) !== '')
<div id="lcc-theme-login-footer" class="text-xs text-gray-500" role="contentinfo">
    @if (! empty($options['footer_url']))
        <a href="{{ $options['footer_url'] }}" target="_blank" rel="noopener noreferrer">{{ $options['footer_text'] }}</a>
    @else
        <span>{{ $options['footer_text'] }}</span>
    @endif
</div>
@endif
