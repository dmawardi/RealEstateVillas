@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Laravel')
<img src="{{ $url }}/images/logo/Logo_w_text.png" class="logo" alt="Bali Villa Spot Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
