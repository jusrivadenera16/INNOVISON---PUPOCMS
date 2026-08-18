@php
    $details = $statusCard['details'] ?? [];
    $date = $details['date'] ?? 'Your appointment date';
    $time = $details['time'] ?? 'Your appointment time';
    $service = $details['service'] ?? 'Clinic consultation';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $title }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f1f1;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; margin:0; padding:0; background-color:#f3f1f1;">
        <tr>
            <td align="center" style="padding:14px 10px;">
                <table role="presentation" width="660" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:660px; background-color:#ffffff; border:1px solid #e5e1e1; border-top:3px solid #8d1020; border-radius:7px; overflow:hidden;">
                    <tr>
                        <td style="padding:19px 38px 8px; font-family:Arial, Helvetica, sans-serif; color:#30343a;">
                            <div style="font-size:16px; line-height:22px; font-weight:700;">Hello, {{ $studentName }}!</div>
                            <div style="padding-top:7px; max-width:310px; font-size:15px; line-height:21px;">This is a friendly reminder that<br>your appointment starts in</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 38px 16px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td width="55%" valign="top" style="width:55%; padding-right:18px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; border:1px solid #f0dddd; border-radius:10px; background-color:#fff8f8;">
                                            <tr>
                                                <td align="center" style="padding:19px 10px 10px;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                                        <tr>
                                                            <td valign="middle" style="padding-right:15px; font-family:Arial, Helvetica, sans-serif; font-size:67px; line-height:73px; color:#8d1020;">&#9200;</td>
                                                            <td valign="middle" style="font-family:Arial, Helvetica, sans-serif; color:#8d1020;">
                                                                <div style="font-size:72px; line-height:67px; font-weight:700;">15</div>
                                                                <div style="padding-top:7px; font-size:22px; line-height:25px; font-weight:700;">MINUTES</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" style="padding:0 10px 11px;">
                                                    <div style="display:inline-block; padding:7px 17px; border-radius:5px; background-color:#8d1020; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:18px; font-weight:700; color:#ffffff;">UNTIL YOUR APPOINTMENT</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td valign="top">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; border:1px solid #ecced2; border-radius:10px;">
                                            <tr>
                                                <td width="47" valign="middle" style="width:47px; padding:13px 0 10px 14px;">
                                                    <div style="width:36px; height:36px; border-radius:18px; background-color:#fff0f1; font-family:Arial, Helvetica, sans-serif; font-size:19px; line-height:36px; text-align:center; color:#8d1020;">&#128197;</div>
                                                </td>
                                                <td style="padding:11px 12px 10px; font-family:Arial, Helvetica, sans-serif; border-bottom:1px solid #efe2e3;">
                                                    <div style="font-size:10px; line-height:13px; color:#4e555c;">DATE</div>
                                                    <div style="font-size:13px; line-height:17px; font-weight:700; color:#30343a;">{{ $date }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="47" valign="middle" style="width:47px; padding:10px 0 10px 14px;">
                                                    <div style="width:36px; height:36px; border-radius:18px; background-color:#fff0f1; font-family:Arial, Helvetica, sans-serif; font-size:19px; line-height:36px; text-align:center; color:#8d1020;">&#128337;</div>
                                                </td>
                                                <td style="padding:10px 12px; font-family:Arial, Helvetica, sans-serif; border-bottom:1px solid #efe2e3;">
                                                    <div style="font-size:10px; line-height:13px; color:#4e555c;">TIME</div>
                                                    <div style="font-size:13px; line-height:17px; font-weight:700; color:#30343a;">{{ $time }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="47" valign="middle" style="width:47px; padding:10px 0 13px 14px;">
                                                    <div style="width:36px; height:36px; border-radius:18px; background-color:#fff0f1; font-family:Arial, Helvetica, sans-serif; font-size:18px; line-height:36px; text-align:center; color:#8d1020;">&#129658;</div>
                                                </td>
                                                <td style="padding:10px 12px 13px; font-family:Arial, Helvetica, sans-serif;">
                                                    <div style="font-size:10px; line-height:13px; color:#4e555c;">SERVICE</div>
                                                    <div style="font-size:13px; line-height:17px; font-weight:700; color:#30343a;">{{ $service }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 38px 13px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; border:1px solid #f0d88b; border-radius:10px; background-color:#fffaf0;">
                                <tr>
                                    <td colspan="3" style="padding:12px 20px 4px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:18px; font-weight:700; color:#8d1020;">Before your appointment:</td>
                                </tr>
                                <tr>
                                    <td width="33%" valign="top" align="center" style="width:33%; padding:5px 13px 15px; border-right:1px solid #f0e1bf; font-family:Arial, Helvetica, sans-serif;">
                                        <div style="margin:0 auto 7px; width:43px; height:43px; border-radius:22px; background-color:#fff0bf; font-size:21px; line-height:43px; color:#8d1020;">&#128196;</div>
                                        <div style="font-size:11px; line-height:14px; font-weight:700; color:#30343a;">Review your<br>appointment details</div>
                                        <div style="padding-top:4px; font-size:10px; line-height:13px; color:#4e555c;">Check the date, time,<br>and service.</div>
                                    </td>
                                    <td width="34%" valign="top" align="center" style="width:34%; padding:5px 13px 15px; border-right:1px solid #f0e1bf; font-family:Arial, Helvetica, sans-serif;">
                                        <div style="margin:0 auto 7px; width:43px; height:43px; border-radius:22px; background-color:#fff0bf; font-size:21px; line-height:43px; color:#8d1020;">&#128194;</div>
                                        <div style="font-size:11px; line-height:14px; font-weight:700; color:#30343a;">Prepare any required<br>documents</div>
                                        <div style="padding-top:4px; font-size:10px; line-height:13px; color:#4e555c;">Bring the required forms<br>or supporting documents.</div>
                                    </td>
                                    <td width="33%" valign="top" align="center" style="width:33%; padding:5px 13px 15px; font-family:Arial, Helvetica, sans-serif;">
                                        <div style="margin:0 auto 7px; width:43px; height:43px; border-radius:22px; background-color:#fff0bf; font-size:22px; line-height:43px; color:#8d1020;">&#128337;</div>
                                        <div style="font-size:11px; line-height:14px; font-weight:700; color:#30343a;">Arrive on time</div>
                                        <div style="padding-top:4px; font-size:10px; line-height:13px; color:#4e555c;">Please arrive a few minutes<br>early for your appointment.</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:0 38px 14px;">
                            <a href="{{ $actionUrl }}" style="display:inline-block; padding:12px 27px; border-radius:5px; background-color:#8d1020; color:#ffffff; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:18px; font-weight:700; text-decoration:none;">&#128197;&nbsp;&nbsp;{{ $actionLabel }}&nbsp;&nbsp;&rsaquo;</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 38px 13px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; border:1px solid #f0dddd; border-radius:8px; background-color:#fff8f8;">
                                <tr>
                                    <td width="48" valign="middle" style="width:48px; padding:10px 0 10px 15px; font-family:Arial, Helvetica, sans-serif; font-size:24px; line-height:27px; color:#8d1020;">&#127911;</td>
                                    <td style="padding:10px 13px 10px 3px; font-family:Arial, Helvetica, sans-serif; color:#30343a;">
                                        <div style="font-size:12px; line-height:15px; font-weight:700; color:#8d1020;">Need to reschedule?</div>
                                        <div style="padding-top:2px; font-size:10px; line-height:14px;">Please contact the PUP Taguig Clinic as soon as possible.</div>
                                    </td>
                                    <td valign="middle" style="padding:10px 13px; border-left:1px solid #efe0e1; font-family:Arial, Helvetica, sans-serif; font-size:10px; line-height:16px; color:#30343a;">
                                        (02) 8837 5858 to 60<br>puptclinic@gmail.com
                                    </td>
                                </tr>
                            </table>
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
