<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Application Error' }}</title>
    <style>
        :root {
            --bg: #0b1220;
            --surface: #111a2e;
            --surface-2: #16213a;
            --text: #e2e8f0;
            --muted: #9aa8c1;
            --accent: #4f46e5;
            --accent-2: #22d3ee;
            --danger: #f43f5e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Manrope", "Segoe UI", Roboto, Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 15% 10%, rgba(79, 70, 229, 0.23), transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(34, 211, 238, 0.18), transparent 42%),
                linear-gradient(145deg, #070b15 0%, var(--bg) 60%, #0d172b 100%);
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .shell {
            width: min(860px, 100%);
            background: linear-gradient(170deg, rgba(22, 33, 58, 0.95), rgba(12, 20, 35, 0.96));
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(2, 8, 23, 0.65);
        }

        .topbar {
            height: 6px;
            background: linear-gradient(90deg, var(--accent), var(--accent-2), var(--danger));
        }

        .content {
            padding: 36px 32px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, 0.28);
            background: rgba(15, 23, 42, 0.4);
            border-radius: 999px;
            padding: 7px 12px;
            font-weight: 700;
        }

        .code {
            font-size: clamp(58px, 11vw, 110px);
            line-height: 1;
            margin: 18px 0 8px;
            font-weight: 900;
            letter-spacing: 0.02em;
            color: #f8fafc;
            text-shadow: 0 8px 30px rgba(79, 70, 229, 0.35);
        }

        .heading {
            margin: 0;
            font-size: clamp(24px, 4vw, 36px);
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .message {
            margin: 14px 0 0;
            max-width: 62ch;
            color: var(--muted);
            font-size: 15px;
            line-height: 1.7;
        }

        .debug-block {
            margin-top: 18px;
            padding: 14px;
            background: rgba(15, 23, 42, 0.75);
            border: 1px solid rgba(148, 163, 184, 0.2);
            border-radius: 12px;
            color: #e2e8f0;
            font-size: 12px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 26px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-weight: 700;
            text-decoration: none;
            font-size: 14px;
            padding: 11px 18px;
            transition: transform .18s ease, opacity .18s ease, box-shadow .18s ease;
        }

        .btn-primary {
            color: #ffffff;
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            box-shadow: 0 10px 22px rgba(79, 70, 229, 0.35);
        }

        .btn-ghost {
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, 0.28);
            background: rgba(15, 23, 42, 0.35);
        }

        .btn:hover {
            transform: translateY(-1px);
            opacity: .96;
        }

        .footer {
            border-top: 1px solid rgba(148, 163, 184, 0.18);
            padding: 14px 22px;
            color: #93a5bf;
            font-size: 12px;
            background: rgba(2, 8, 23, 0.35);
        }

        @media (max-width: 640px) {
            .content {
                padding: 30px 22px;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <main class="shell" role="main" aria-live="polite">
        <div class="topbar"></div>
        <section class="content">
            <span class="badge">{{ $badge ?? 'System Notice' }}</span>
            <div class="code">{{ $code ?? '500' }}</div>
            <h1 class="heading">{{ $heading ?? 'Something went wrong.' }}</h1>
            <p class="message">
                {{ $message ?? 'An unexpected error occurred while processing your request. Please try again in a moment.' }}
            </p>

            @if (!empty($debug))
                <pre class="debug-block">{{ $debug['type'] ?? '' }}
{{ $debug['message'] ?? '' }}

{{ $debug['file'] ?? '' }}:{{ $debug['line'] ?? '' }}

{{ $debug['trace'] ?? '' }}</pre>
            @endif

            <div class="actions">
                <a class="btn btn-primary" href="{{ $primaryUrl ?? url('/') }}">
                    {{ $primaryText ?? 'Go To Homepage' }}
                </a>
                <a class="btn btn-ghost" href="{{ $secondaryUrl ?? url()->previous() }}">
                    {{ $secondaryText ?? 'Go Back' }}
                </a>
            </div>
        </section>
        <div class="footer">
            {{ config('app.name', 'Application') }} | If the issue persists, contact support.
        </div>
    </main>
</body>
</html>
