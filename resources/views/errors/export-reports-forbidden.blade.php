<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
            margin: 0;
        }

        body {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            background:
                radial-gradient(circle at 50% 34%, rgba(112, 19, 27, 0.07), transparent 34%),
                #f6f7f9;
            color: #20232d;
            font-family: Arial, Helvetica, sans-serif;
        }

        .forbidden-card {
            width: min(460px, 100%);
            padding: 46px 38px 40px;
            border: 1px solid rgba(31, 35, 48, 0.08);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.13);
            text-align: center;
        }

        .forbidden-lock {
            width: 100px;
            height: 100px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            color: #242733;
            filter: drop-shadow(0 5px 3px rgba(15, 23, 42, 0.18));
        }

        .forbidden-lock svg {
            width: 100%;
            height: 100%;
            stroke-width: 1.8;
        }

        .forbidden-title {
            margin: 0 0 14px;
            color: #20232d;
            font-size: 30px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: 0;
        }

        .forbidden-copy {
            max-width: 340px;
            margin: 0 auto 28px;
            color: #343844;
            font-size: 20px;
            line-height: 1.45;
            font-weight: 500;
        }

        .forbidden-back {
            min-width: 210px;
            min-height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 13px 30px;
            border: 1px solid #760b18;
            border-radius: 7px;
            background: #850d1c;
            color: #ffffff;
            box-shadow: 0 8px 18px rgba(112, 19, 27, 0.24);
            font-size: 19px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s ease, background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
        }

        .forbidden-back:hover,
        .forbidden-back:focus-visible {
            transform: translateY(-2px);
            border-color: #facc15;
            background: #facc15;
            color: #70131b;
            outline: none;
        }

        @media (max-width: 520px) {
            body {
                padding: 16px;
            }

            .forbidden-card {
                padding: 38px 22px 32px;
            }

            .forbidden-lock {
                width: 82px;
                height: 82px;
            }

            .forbidden-title {
                font-size: 25px;
            }

            .forbidden-copy {
                font-size: 17px;
            }

            .forbidden-back {
                width: 100%;
                min-width: 0;
                min-height: 50px;
                font-size: 17px;
            }
        }
    </style>
</head>
<body>
    <main class="forbidden-card" role="main" aria-labelledby="forbiddenTitle">
        <div class="forbidden-lock" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                <path d="M7.5 10V7.5a4.5 4.5 0 0 1 9 0V10" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" />
                <rect x="5" y="9" width="14" height="12" rx="1.8" fill="currentColor" />
                <circle cx="12" cy="14.25" r="1.5" fill="#ffffff" />
                <path d="M12 15.5V18" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round" />
            </svg>
        </div>
        <h1 class="forbidden-title" id="forbiddenTitle">403 - FORBIDDEN</h1>
        <p class="forbidden-copy">You are not authorized to export the health records.</p>
        <a class="forbidden-back" href="{{ $backUrl }}">Go Back</a>
    </main>
</body>
</html>
