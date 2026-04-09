<x-mail::message>
# METS export ready

The METS/ALTO export for **{{ $storyTitle }}** is now ready.

You have to be logged in into Transcribathon to download the the METS XML.

<x-mail::button :url="$downloadUrl">
Download METS XML
</x-mail::button>

If you did not request this export, you can ignore this email.

Thanks,<br>
Transcribathon Team
</x-mail::message>
