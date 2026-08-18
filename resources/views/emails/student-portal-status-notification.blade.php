@php
    $isWarning = ($statusCard['tone'] ?? 'success') === 'warning';
    $accent = $isWarning ? '#d18a00' : '#07833d';
    $accentSoft = $isWarning ? '#fff9df' : '#f3fbf5';
    $accentPale = $isWarning ? '#fff2b8' : '#e3f6e9';
    $border = $isWarning ? '#f0cf68' : '#c4e4ce';
    $heroIcon = ($statusCard['icon'] ?? 'check') === 'x' ? '&#10005;' : '&#10003;';
    $statusIcon = $statusCard['status_icon'] ?? '&#10023;';
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
            <td align="center" style="padding:16px 10px;">
                <table role="presentation" width="660" cellspacing="0" cellpadding="0" border="0" style="width:100%; max-width:660px; background-color:#ffffff; border:1px solid #e5e1e1; border-top:3px solid #8d1020; border-radius:7px; overflow:hidden;">
                    <tr>
                        <td style="padding:21px 30px 8px; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:20px; color:#30343a;">Hello {{ $studentName }},</td>
                    </tr>
                    <tr>
                        <td style="padding:6px 30px 19px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td width="107" valign="middle" style="width:107px; padding-right:18px;">
                                        <div style="width:72px; height:72px; border:4px solid {{ $accentPale }}; border-radius:50%; background-color:{{ $accent }}; box-shadow:0 0 0 2px {{ $accent }}; font-family:Arial, Helvetica, sans-serif; font-size:44px; line-height:72px; font-weight:700; text-align:center; color:#ffffff;">{!! $heroIcon !!}</div>
                                    </td>
                                    <td valign="middle" style="font-family:Arial, Helvetica, sans-serif;">
                                        <div style="font-size:24px; line-height:30px; font-weight:700; color:#85121f;">{{ $title }}</div>
                                        <div style="padding-top:7px; font-size:14px; line-height:21px; color:#3d464f;">{{ $messageText }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 10px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; border:1px solid {{ $border }}; border-radius:10px; background-color:{{ $accentSoft }};">
                                <tr>
                                    <td width="44%" valign="middle" style="width:44%; padding:25px 20px; border-right:1px solid {{ $border }};">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td width="66" valign="middle" style="width:66px; padding-right:13px;">
                                                    <div style="width:52px; height:52px; border-radius:26px; background-color:{{ $accentPale }}; font-family:Arial, Helvetica, sans-serif; font-size:28px; line-height:52px; text-align:center; color:{{ $accent }};">{!! $statusIcon !!}</div>
                                                </td>
                                                <td valign="middle" style="font-family:Arial, Helvetica, sans-serif;">
                                                    <div style="font-size:13px; line-height:17px; font-weight:700; color:{{ $accent }};">{{ $statusCard['status_label'] ?? 'Status' }}</div>
                                                    <div style="padding-top:2px; font-size:18px; line-height:23px; font-weight:700; color:{{ $accent }};">{{ $statusCard['status_value'] ?? 'UPDATED' }}</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td valign="middle" style="padding:14px 20px; font-family:Arial, Helvetica, sans-serif;">
                                        @foreach (($statusCard['details'] ?? []) as $detail)
                                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:2px 0;">
                                                <tr>
                                                    <td width="33" valign="top" style="width:33px; padding-top:2px;">
                                                        <div style="width:28px; height:28px; border-radius:14px; background-color:{{ $accentPale }}; font-family:Arial, Helvetica, sans-serif; font-size:14px; line-height:28px; text-align:center; color:{{ $accent }};">{!! $detail['icon'] ?? '&#8226;' !!}</div>
                                                    </td>
                                                    <td valign="top">
                                                        <div style="font-size:11px; line-height:14px; color:#5b6269;">{{ $detail['label'] ?? '' }}</div>
                                                        <div style="font-size:13px; line-height:17px; font-weight:700; color:#30343a;">{{ $detail['value'] ?? '' }}</div>
                                                    </td>
                                                </tr>
                                            </table>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:0 30px 10px;">
                            <a href="{{ $actionUrl }}" style="display:inline-block; min-width:270px; padding:12px 22px; border-radius:5px; background-color:#8d1020; color:#ffffff; font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:19px; font-weight:700; text-decoration:none;">&#128196;&nbsp;&nbsp;{{ $actionLabel }}&nbsp;&nbsp;&rsaquo;</a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 30px 15px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%; border:1px solid #efc9ce; border-radius:6px; background-color:#fff3f4;">
                                <tr>
                                    <td width="38" valign="top" style="width:38px; padding:10px 0 9px 13px; color:#8d1020;">@include('emails.partials.privacy-shield-icon')</td>
                                    <td style="padding:9px 13px 9px 4px; font-family:Arial, Helvetica, sans-serif; font-size:11px; line-height:15px; color:#30343a;">For your privacy, request details are available only after you sign in to the Student Portal.</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:7px 30px 14px; border-top:1px solid #eee9e9; font-family:Arial, Helvetica, sans-serif; font-size:10px; line-height:16px; color:#4e555c;">
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
