<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Authenticate your CareerPath BN account</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;color:#1a1a2e;">

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f4f6f9;padding:40px 20px;">
        <tr>
            <td align="center">

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:560px;background:#ffffff;border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;">

                    {{-- Header with logo --}}
                    <tr>
                        <td style="padding:28px 40px 20px;text-align:center;background:#1a3a5c;">
                                                        <img
                                src="{{ asset('images/careerpath-logo-v2.png') }}?v={{ filemtime(public_path('images/careerpath-logo-v2.png')) }}"
                                alt="CareerPath BN"
                                style="height:60px;width:auto;display:block;margin:0 auto;"
                            >
                        </td>
                    </tr>

                    {{-- Title --}}
                    <tr>
                        <td style="padding:36px 40px 8px;">
                            <h1 style="margin:0 0 12px;font-size:22px;font-weight:700;color:#1a3a5c;">
                                Authenticate your account
                            </h1>
                            <p style="margin:0;font-size:14px;color:#6b7280;">
                                Hello {{ $name }},
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:12px 40px 0;">
                            <p style="margin:0 0 16px;font-size:14px;line-height:1.65;color:#374151;">
                                Someone signed up to CareerPath BN with
                                <strong>{{ $email }}</strong>. If it was you, authenticate
                                to create your account. Your account is not created until
                                you do.
                            </p>
                        </td>
                    </tr>

                    {{-- Button --}}
                    <tr>
                        <td style="padding:8px 40px 24px;" align="center">
                            <a
                                href="{{ $authenticateUrl }}"
                                style="display:inline-block;padding:14px 32px;background:#1a3a5c;color:#ffffff;font-size:14px;font-weight:600;text-decoration:none;border-radius:8px;"
                            >
                                Authenticate
                            </a>
                        </td>
                    </tr>

                    {{-- Not me --}}
                    <tr>
                        <td style="padding:0 40px 24px;" align="center">
                            <p style="margin:0 0 10px;font-size:13px;line-height:1.65;color:#374151;">
                                Didn't sign up? Someone may have typed your address by mistake.
                            </p>
                            <a
                                href="{{ $declineUrl }}"
                                style="display:inline-block;padding:10px 24px;background:#ffffff;color:#b91c1c;font-size:13px;font-weight:600;text-decoration:none;border-radius:8px;border:1px solid #fca5a5;"
                            >
                                This isn't me
                            </a>
                        </td>
                    </tr>

                    {{-- Expiry --}}
                    <tr>
                        <td style="padding:0 40px 24px;">
                            <p style="margin:0;font-size:13px;line-height:1.65;color:#6b7280;">
                                These links expire in {{ $expiryMinutes }} minutes. After that
                                the sign-up is discarded and you would need to register again.
                            </p>
                        </td>
                    </tr>

                    {{-- Fallback URL --}}
                    <tr>
                        <td style="padding:0 40px 28px;">
                            <p style="margin:0 0 6px;font-size:12px;color:#9ca3af;">
                                If the Authenticate button doesn't work, copy and paste this link into your browser:
                            </p>
                            <p style="margin:0;font-size:12px;color:#1a3a5c;word-break:break-all;">
                                {{ $authenticateUrl }}
                            </p>
                        </td>
                    </tr>

                    {{-- Divider --}}
                    <tr>
                        <td style="padding:0 40px;">
                            <hr style="border:none;border-top:1px solid #e5e7eb;margin:0;">
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding:24px 40px 32px;text-align:center;">
                            <p style="margin:0 0 12px;font-size:12px;line-height:1.6;color:#9ca3af;">
                                If you ignore this message, no account is created and the
                                sign-up is discarded when the links expire.
                            </p>
                            <p style="margin:0;font-size:11px;color:#c7cdd4;">
                                CareerPath BN &middot; Politeknik Brunei
                            </p>
                        </td>
                    </tr>

                </table>

                <p style="max-width:560px;margin:16px auto 0;font-size:11px;color:#9ca3af;text-align:center;line-height:1.6;">
                    This is an automated message from CareerPath BN.
                    Please do not reply to this email.
                </p>

            </td>
        </tr>
    </table>

</body>
</html>