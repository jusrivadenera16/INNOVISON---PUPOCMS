<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $title }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f1f1;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; margin:0; padding:0; background-color:#f4f1f1;">
        <tr>
            <td align="center" style="padding:28px 12px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:600px; background-color:#ffffff; border:1px solid #e9e2e2; border-top:3px solid #8d1020; border-radius:7px; overflow:hidden;">
                    <tr>
                        <td style="padding:18px 22px; border-bottom:1px solid #eee9e9;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td width="52" valign="middle" style="width:52px; padding-right:12px;">
                                        <img src="{{ $logoUrl }}" width="48" alt="PUP Taguig Clinic" style="display:block; width:48px; height:auto; border:0;">
                                    </td>
                                    <td valign="middle">
                                        <div style="font-family:Arial, Helvetica, sans-serif; font-size:17px; line-height:20px; font-weight:700; color:#82111e;">PUP TAGUIG CLINIC</div>
                                        <div style="padding-top:2px; font-family:Arial, Helvetica, sans-serif; font-size:10px; line-height:14px; font-weight:700; letter-spacing:0.8px; color:#666666;">PORTAL NOTIFICATION</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:26px 28px 20px; font-family:Arial, Helvetica, sans-serif; color:#30343a;">
                            <div style="font-size:15px; line-height:22px;">Hello {{ $studentName }},</div>
                            <div style="padding-top:14px; font-size:23px; line-height:29px; font-weight:700; color:#86111f;">{{ $title }}</div>
                            <div style="padding-top:10px; font-size:14px; line-height:21px; color:#4d535c;">{{ $messageText }}</div>
                            <div style="padding-top:19px;">
                                <a href="{{ $actionUrl }}" style="display:inline-block; padding:12px 20px; border-radius:5px; background-color:#8d1020; color:#ffffff; font-family:Arial, Helvetica, sans-serif; font-size:13px; line-height:18px; font-weight:700; text-decoration:none;">{{ $actionLabel }}</a>
                            </div>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-top:18px;">
                                <tr>
                                    <td width="26" valign="top" style="width:26px; padding-top:1px; color:#8d1020;">@include('emails.partials.privacy-shield-icon')</td>
                                    <td style="font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:17px; color:#68707a;">For your privacy, request details are available only after you sign in to the Student Portal.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:13px 28px; border-top:1px solid #eee9e9; font-family:Arial, Helvetica, sans-serif; font-size:10px; line-height:16px; color:#4e555c;">
                            <strong style="color:#30343a;">Need help?</strong> Contact the PUP Taguig Clinic.<br>
                            (02) 8837 5858 to 60&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;puptclinic@gmail.com
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:10px 20px; background-color:#8d1020; font-family:Arial, Helvetica, sans-serif; font-size:10px; line-height:15px; color:#ffffff;">
                            This is an automated notification from PUP Taguig Clinic.<br>
                            Please do not reply to this email.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
