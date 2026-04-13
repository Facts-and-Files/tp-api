<x-mail::message>
# METS export

{{ $introText }}

**Story:** {{ $storyTitle }}<br>
**Story ID:** {{ $storyId }}<br>
**Export Status:** {{ $status }}

@if($total > 0)
<x-mail::panel>
Pages total: {{ $total ?? 0 }}
Processed: {{ $processed ?? 0 }}
Failed: {{ $failed ?? 0 }}
</x-mail::panel>
@endif

@if($downloadUrl && $status === 'ready')
<x-mail::button :url="$downloadUrl">
Download METS XML
</x-mail::button>
@endif

Thanks,<br>
Transcribathon
</x-mail::message>
