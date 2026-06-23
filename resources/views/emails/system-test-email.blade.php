@component('mail::message')
# SMTP Connection Test

Hello,

This is a test email sent from the **{{ config('app.name', 'IAMJOS') }}** System settings panel to verify that your SMTP server and email configurations are working correctly.

If you are reading this message, your mail credentials and server settings are working perfectly!

**Details:**
- **Recipient:** {{ $recipientEmail }}
- **Timestamp:** {{ now()->toDayDateTimeString() }}

Thanks,<br>
{{ config('app.name', 'IAMJOS') }} System
@endcomponent
