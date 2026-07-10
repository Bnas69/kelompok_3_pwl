<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Error' }} — Kelompok 3</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',system-ui,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100dvh;background:#f8fafc;padding:24px}
        .error-card{background:#fff;border-radius:20px;padding:48px 40px;text-align:center;max-width:440px;width:100%;box-shadow:0 4px 24px rgba(0,0,0,.06)}
        .error-code{font-size:72px;font-weight:800;line-height:1;margin-bottom:8px}
        .error-title{font-size:20px;font-weight:600;margin-bottom:8px}
        .error-desc{font-size:14px;color:#64748b;line-height:1.6;margin-bottom:24px}
        .btn-home{display:inline-flex;align-items:center;gap:8px;padding:10px 24px;border-radius:10px;font-size:14px;font-weight:500;text-decoration:none;transition:all .15s}
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-code" style="color:{{ $color ?? '#0ea5e9' }}">{{ $code }}</div>
        <div class="error-title">{{ $title }}</div>
        <p class="error-desc">{{ $description }}</p>
        <a href="{{ $homeUrl ?? '/' }}" class="btn-home" style="color:#fff;background:{{ $color ?? '#0ea5e9' }}">
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M7 1L1 7m0 0l6 6M1 7h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
