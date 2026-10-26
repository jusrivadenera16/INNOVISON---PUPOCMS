<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Clinic temporarily unavailable</title>
    <style>
        :root { color-scheme: light; font-family: Arial, sans-serif; color: #202938; background: #f4f6f8; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; }
        main { width: min(100%, 680px); background: #fff; border: 1px solid #e6d9dc; border-top: 6px solid #86152a; padding: 32px; }
        .eyebrow { margin: 0 0 10px; color: #86152a; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        h1 { margin: 0; font-size: 26px; line-height: 1.25; }
        .message { margin: 14px 0 22px; line-height: 1.55; color: #4b5563; }
        .fields { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 20px 0; }
        .field { min-width: 0; border: 1px solid #e5e7eb; padding: 12px; }
        .field.wide { grid-column: 1 / -1; }
        .field span { display: block; color: #697586; font-size: 12px; font-weight: 700; margin-bottom: 6px; }
        .field output { display: block; white-space: pre-wrap; overflow-wrap: anywhere; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; margin-top: 22px; }
        button, .link { min-height: 44px; display: inline-flex; align-items: center; justify-content: center; padding: 0 18px; font-weight: 700; text-decoration: none; }
        button { border: 0; background: #86152a; color: #fff; cursor: pointer; }
        .link { color: #86152a; }
        @media (max-width: 520px) { main { padding: 24px 18px; } .fields { grid-template-columns: 1fr; } .field.wide { grid-column: auto; } }
    </style>
</head>
<body>
<main>
    <p class="eyebrow">Appointment booking</p>
    <h1>We couldn't save your appointment</h1>
    <p class="message">The clinic database connection was interrupted. Your appointment was not confirmed. Restore the connection, then retry using the details below.</p>

    @if ($bookingInput)
        <div class="fields">
            <div class="field"><span>Preferred date</span><output>{{ $bookingInput['date'] ?? '' }}</output></div>
            <div class="field"><span>Preferred time</span><output>{{ $bookingInput['time'] ?? '' }}</output></div>
            <div class="field"><span>Service</span><output>{{ $bookingInput['service'] ?? '' }}</output></div>
            <div class="field wide"><span>Reason / symptoms</span><output>{{ $bookingInput['remarks'] ?? '' }}</output></div>
        </div>
        <form method="POST" action="{{ url('/student/appointments/store') }}">
            @csrf
            @foreach (['date', 'time', 'service', 'remarks'] as $field)
                @if (array_key_exists($field, $bookingInput))
                    <input type="hidden" name="{{ $field }}" value="{{ $bookingInput[$field] }}">
                @endif
            @endforeach
            <div class="actions">
                <button type="submit">Retry Appointment</button>
                <a class="link" href="{{ url('/student/booking') }}">Reload booking page</a>
            </div>
        </form>
    @else
        <div class="actions">
            <a class="link" href="{{ url('/student/booking') }}">Retry loading the booking page</a>
        </div>
    @endif
</main>
</body>
</html>
