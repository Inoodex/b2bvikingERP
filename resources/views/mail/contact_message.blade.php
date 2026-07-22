<div style="background-color: #f3f4f6; padding: 24px;">
    <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; font-family: 'Segoe UI', Arial, sans-serif; color: #111827;">
        <div style="background: linear-gradient(135deg, #1f2937, #4f46e5); padding: 20px 24px;">
            <p style="margin: 0; color: #dbeafe; font-size: 12px; text-transform: uppercase; letter-spacing: 0.18em;">
                New Contact Message
            </p>
            <h1 style="margin: 8px 0 0; color: #ffffff; font-size: 22px; font-weight: 700;">
                {{ $messageData['subject'] }}
            </h1>
        </div>

        <div style="padding: 24px;">
            <p style="margin: 0 0 16px; color: #374151; font-size: 14px;">
                You have received a new message from the contact form on
                <strong>{{ $settings->site_name ?? config('app.name') }}</strong>.
            </p>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px;">
                <tr>
                    <td style="padding: 8px 0; width: 140px; color: #6b7280;">Name</td>
                    <td style="padding: 8px 0; font-weight: 600; color: #111827;">
                        {{ $messageData['first_name'] }} {{ $messageData['last_name'] }}
                    </td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #6b7280;">Email</td>
                    <td style="padding: 8px 0; font-weight: 600; color: #111827;">{{ $messageData['email'] }}</td>
                </tr>
                @if(!empty($messageData['phone']))
                <tr>
                    <td style="padding: 8px 0; color: #6b7280;">Phone</td>
                    <td style="padding: 8px 0; font-weight: 600; color: #111827;">{{ $messageData['phone'] }}</td>
                </tr>
                @endif
            </table>

            <div style="border: 1px solid #e5e7eb; border-radius: 12px; background: #f9fafb; padding: 16px;">
                <p style="margin: 0 0 8px; font-size: 13px; font-weight: 600; color: #374151;">Message</p>
                <p style="margin: 0; white-space: pre-wrap; font-size: 14px; color: #111827;">
                    {{ $messageData['message'] }}
                </p>
            </div>

            <div style="margin-top: 20px; font-size: 12px; color: #6b7280;">
                Sent from {{ $settings->site_name ?? config('app.name') }} contact form.
            </div>
        </div>
    </div>
</div>
