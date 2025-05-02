<x-mail::message>
# Document Expiry Reminder

Hello!

This mail is a reminder for your document expiry.

Your document is set to expire on {{ $expiryDate }}.

The document type is {{ $documentType }}.

### Document Details:

@foreach ($documentDetails as $field => $value)
- **{{ $field }}**: {{ $value }}
@endforeach

### Attachments:
![Document Attachment]({{asset("avadh/DMS/DMS/public/storage/profile_images/fish4.jpeg")}})

Regards,  
{{ config('app.name') }}
</x-mail::message>
