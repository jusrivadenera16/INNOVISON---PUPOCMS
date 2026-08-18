<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Restricted</title>
    <style>
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; background:#f8fafc; color:#172033; font-family:Arial,sans-serif; }
        main { width:min(460px,100%); padding:32px; border:1px solid #ead7d9; border-radius:10px; background:#fff; box-shadow:0 18px 40px rgba(15,23,42,.1); text-align:center; }
        .mark { display:inline-grid; width:48px; height:48px; place-items:center; border-radius:50%; background:#70131b; color:#fff; font-weight:800; }
        h1 { margin:18px 0 8px; color:#70131b; font-size:22px; }
        p { margin:0; color:#64748b; line-height:1.55; }
        a { display:inline-block; margin-top:22px; padding:11px 16px; border-radius:8px; background:#70131b; color:#fff; font-weight:700; text-decoration:none; }
    </style>
</head>
<body>
    @php
        $backUrl = optional(auth()->user())->isStudentAssistant()
            ? url('/assistant/dashboard')
            : url('/admin/dashboard');
    @endphp
    <main>
        <div class="mark">!</div>
        <h1>Access Restricted</h1>
        <p>You do not have permission to open this area.</p>
        <a href="{{ $backUrl }}">Return to Dashboard</a>
    </main>
</body>
</html>
