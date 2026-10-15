<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New appointment submitted</title>
</head>
<body style="margin:0;background:#f4f6f8;color:#172033;font-family:Arial,sans-serif;">
    <div style="max-width:560px;margin:28px auto;padding:0 16px;">
        <div style="overflow:hidden;border:1px solid #ead5d8;border-radius:10px;background:#fff;">
            <div style="padding:20px 24px;background:#7f0010;color:#fff;">
                <h1 style="margin:0;font-size:20px;line-height:1.3;">New appointment submitted</h1>
                <p style="margin:7px 0 0;font-size:14px;line-height:1.5;">A patient has submitted a clinic appointment request.</p>
            </div>
            <div style="padding:22px 24px;">
                <p style="margin:0 0 8px;"><strong>Patient:</strong> {{ $patientName }}</p>
                <p style="margin:0 0 8px;"><strong>Service:</strong> {{ $service }}</p>
                <p style="margin:0 0 8px;"><strong>Schedule:</strong> {{ $schedule }}</p>
                <p style="margin:0 0 18px;"><strong>Status:</strong> {{ $status }}</p>
                <a href="{{ $appointmentsUrl }}" style="display:inline-block;padding:11px 16px;border-radius:6px;background:#7f0010;color:#fff;text-decoration:none;font-weight:700;">Open Appointments</a>
            </div>
            <div style="padding:12px 24px;border-top:1px solid #edf0f3;color:#64748b;font-size:12px;">Appointment reference: {{ $appointmentNumber }}</div>
        </div>
    </div>
</body>
</html>
