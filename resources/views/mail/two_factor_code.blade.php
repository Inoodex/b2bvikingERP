<div style="background-color: #f3f4f6; padding: 24px;">
    <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 16px; overflow: hidden; font-family: 'Segoe UI', Arial, sans-serif; color: #111827;">
        <div style="background: linear-gradient(135deg, #111827, #4f46e5); padding: 20px 24px;">
            <p style="margin: 0; color: #e0e7ff; font-size: 12px; text-transform: uppercase; letter-spacing: 0.18em;">
                Admin Verification
            </p>
            <h1 style="margin: 8px 0 0; color: #ffffff; font-size: 22px; font-weight: 700;">
                Your One-Time Code
            </h1>
        </div>

        <div style="padding: 24px;">
            <p style="margin: 0 0 16px; color: #374151; font-size: 14px;">
                Use the code below to complete your admin login for
                <strong>{{ $settings->site_name ?? config('app.name') }}</strong>.
            </p>

            <div style="text-align: center; margin: 20px 0;">
                <div style="display: inline-block; padding: 12px 24px; background: #f9fafb; border: 1px dashed #cbd5f5; border-radius: 12px; font-size: 24px; letter-spacing: 0.3em; font-weight: 700; color: #1f2937;">
                    {{ $code }}
                </div>
            </div>

            <p style="margin: 0 0 16px; color: #6b7280; font-size: 13px;">
                This code will expire in {{ $expiresMinutes }} minutes. If you did not attempt to log in, please secure your account.
            </p>

            <div style="margin-top: 12px; font-size: 12px; color: #9ca3af;">
                Sent from {{ $settings->site_name ?? config('app.name') }} security system.
            </div>
        </div>
    </div>
</div>
