<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.

**Contact Us:**  
Email: {{ config('mail.from.address', 'contact@balivillaspot.com') }}  
Phone: {{ config('app.business_phone', '+62 123 456 7890') }}

Visit us: [www.balivillaspot.com]({{ config('app.url') }})
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
