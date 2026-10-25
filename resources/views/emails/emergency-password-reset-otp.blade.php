<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Emergency password reset code</title>
</head>
<body style="margin:0;padding:24px;background:#f4f1f1;font-family:Arial,Helvetica,sans-serif;color:#252a33">
    <main style="max-width:560px;margin:auto;padding:28px;background:#fff;border:1px solid #e8dfe0;border-top:4px solid #8d1020;border-radius:8px">
        <p style="margin:0 0 8px;color:#82111e;font-size:13px;font-weight:700;letter-spacing:.5px">PUP TAGUIG CLINIC</p>
        <h1 style="margin:0 0 12px;color:#82111e;font-size:22px">Emergency password reset</h1>
        <p style="margin:0 0 22px;line-height:1.5">Use this one-time code to verify the emergency recovery email and reset its password.</p>
        <p style="margin:0 auto 18px;padding:14px 20px;width:max-content;border:1px solid #f0c9ce;border-radius:7px;background:#fff7f7;color:#8d1020;font-size:30px;font-weight:700;letter-spacing:8px">{{ $code }}</p>
        <p style="margin:0 0 16px;line-height:1.5">This code expires in <strong>{{ $expiresMinutes }} minutes</strong> and can only be used once.</p>
        <p style="margin:0;padding-top:14px;border-top:1px solid #eee9e9;color:#555b63;font-size:13px;line-height:1.5">If you did not request this reset, ignore this email. No password will be changed.</p>
    </main>
</body>
</html>
