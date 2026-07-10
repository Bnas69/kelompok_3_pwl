<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | HR Analytics</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite('resources/css/app.css')
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{min-height:100vh;display:flex;font-family:'Inter',system-ui,sans-serif;font-size:14px;color:#1e293b;background:#0f172a}
        .split{display:flex;width:100%;min-height:100vh}
        .split-left{flex:1.2;position:relative;display:flex;align-items:center;justify-content:center;overflow:hidden}
        .split-left::before{content:'';position:absolute;inset:0;background:url('/images/login-bg.jpg') center/cover no-repeat;filter:brightness(.45) saturate(1.1);transform:scale(1.05);transition:transform 8s ease}
        .split-left:hover::before{transform:scale(1)}
        .split-left .overlay{position:absolute;inset:0;background:linear-gradient(135deg,rgba(15,23,42,.7) 0%,rgba(30,64,175,.3) 100%)}
        .split-left .content{position:relative;z-index:2;padding:60px;max-width:560px;color:#fff}
        .split-left .content .badge{display:inline-block;padding:6px 16px;border-radius:100px;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;background:rgba(255,255,255,.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.08);margin-bottom:24px}
        .split-left .content h1{font-size:36px;font-weight:800;line-height:1.2;margin-bottom:16px;text-shadow:0 2px 20px rgba(0,0,0,.3)}
        .split-left .content p{font-size:15px;line-height:1.7;opacity:.8;margin-bottom:32px}
        .split-left .content .stat-row{display:flex;gap:32px;flex-wrap:wrap}
        .split-left .content .stat-item{display:flex;flex-direction:column;gap:4px}
        .split-left .content .stat-value{font-size:28px;font-weight:800;letter-spacing:-.5px}
        .split-left .content .stat-label{font-size:12px;opacity:.6;text-transform:uppercase;letter-spacing:.5px}
        .split-right{flex:1;display:flex;align-items:center;justify-content:center;padding:40px;background:#fff;position:relative}
        .split-right::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;background:linear-gradient(90deg,#2563eb,#7c3aed,#2563eb)}
        .card{width:100%;max-width:400px;padding:20px 8px}
        .logo-row{display:flex;align-items:center;gap:14px;margin-bottom:32px}
        .logo-box{width:48px;height:48px;background:linear-gradient(135deg,#2563eb,#7c3aed);border-radius:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .logo-box i{line-height:1;font-size:22px;color:#fff;display:block}
        .logo-text .brand{font-size:18px;font-weight:800;color:#0f172a;line-height:1.2}
        .logo-text .sub{font-size:12px;color:#94a3b8;font-weight:500}
        .heading{font-size:24px;font-weight:800;color:#0f172a;margin-bottom:4px}
        .subheading{font-size:14px;color:#64748b;margin-bottom:28px;line-height:1.5}
        .form{text-align:left}
        .field{margin-bottom:18px}
        .label{display:block;font-size:13px;font-weight:600;color:#1e293b;margin-bottom:6px}
        .input-wrap{position:relative}
        .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;pointer-events:none;display:flex;line-height:0}
        .input{display:block;width:100%;height:48px;padding:0 14px 0 44px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:14px;color:#1e293b;background:#f8fafc;outline:none;transition:all .2s;-webkit-appearance:none}
        .input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.12);background:#fff}
        .input::placeholder{color:#cbd5e1}
        .toggle-pwd{position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#94a3b8;cursor:pointer;padding:4px;line-height:0;transition:color .15s}
        .toggle-pwd:hover{color:#475569}
        .btn-login{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;height:48px;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;border:none;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;transition:all .2s;font-family:inherit;position:relative;overflow:hidden}
        .btn-login:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(37,99,235,.3)}
        .btn-login:active{transform:translateY(0)}
        .btn-login::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,transparent,rgba(255,255,255,.15));pointer-events:none}
        .error-box{background:#fef2f2;color:#991b1b;border:1px solid #fecaca;border-radius:10px;padding:12px 14px;font-size:13px;text-align:left;margin-bottom:20px;display:flex;align-items:flex-start;gap:8px}
        .error-box i{flex-shrink:0;margin-top:1px}
        .footer{margin-top:28px;font-size:12px;color:#94a3b8;text-align:center}
        .divider{display:flex;align-items:center;gap:12px;margin:20px 0;color:#cbd5e1;font-size:12px}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:#e2e8f0}
        @media(max-width:860px){
            .split-left{display:none}
            .split-right{padding:24px}
            body{background:#fff}
            .split-right::before{height:3px}
        }
    </style>
</head>
<body>
<div class="split">
    <div class="split-left">
        <div class="overlay"></div>
        <div class="content">
            <div class="badge">HR Analytics Dashboard</div>
            <h1>Human Resource<br>Analytics</h1>
            <p>Platform analisis data kepegawaian untuk mengidentifikasi attrition risk, memantau performa departemen, dan mengambil keputusan HR berbasis data.</p>
            <div class="stat-row">
                <div class="stat-item">
                    <span class="stat-value">50+</span>
                    <span class="stat-label">Data Points</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">4</span>
                    <span class="stat-label">Risk Levels</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value">Real-time</span>
                    <span class="stat-label">Monitoring</span>
                </div>
            </div>
        </div>
    </div>
    <div class="split-right">
        <div class="card">
            <div class="logo-row">
                <div class="logo-box">
                    <i class="bi bi-people-fill"></i>
                </div>
                <div class="logo-text">
                    <div class="brand">HR Analytics</div>
                    <div class="sub">Kelompok 3</div>
                </div>
            </div>

            <h1 class="heading">Selamat Datang</h1>
            <p class="subheading">Silakan masuk menggunakan akun HR Anda</p>

            @if ($errors->has('login'))
                <div class="error-box">
                    <i class="bi bi-exclamation-circle-fill" style="font-size:14px;"></i>
                    <span>{{ $errors->first('login') }}</span>
                </div>
            @endif

            <form method="post" action="{{ route('login.submit') }}" class="form">
                @csrf
                <div class="field">
                    <label class="label" for="username">Username</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="bi bi-person" style="font-size:16px;display:block"></i></span>
                        <input id="username" name="username" type="text" class="input"
                            value="{{ old('username') }}"
                            placeholder="Masukkan username"
                            autocomplete="username" required autofocus>
                    </div>
                    @error('username') <small style="color:#dc2626;font-size:12px;margin-top:4px;display:block;">{{ $message }}</small> @enderror
                </div>

                <div class="field">
                    <label class="label" for="password">Password</label>
                    <div class="input-wrap">
                        <span class="input-icon"><i class="bi bi-lock" style="font-size:16px;display:block"></i></span>
                        <input id="password" name="password" type="password" class="input"
                            placeholder="Masukkan password"
                            autocomplete="current-password" required
                            style="padding-right:44px;">
                        <button type="button" class="toggle-pwd" id="togglePwd" aria-label="Tampilkan password">
                            <i class="bi bi-eye" id="eyeIcon" style="font-size:16px;display:block"></i>
                        </button>
                    </div>
                    @error('password') <small style="color:#dc2626;font-size:12px;margin-top:4px;display:block;">{{ $message }}</small> @enderror
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right" style="font-size:16px;display:block"></i>
                    Masuk Dashboard
                </button>
            </form>

            <div class="footer">&copy; {{ date('Y') }} HR Analytics &middot; Kelompok 3 &middot; Universitas Dian Nusantara</div>
        </div>
    </div>
</div>

<script>
(function(){
    var btn=document.getElementById('togglePwd'),inp=document.getElementById('password'),eye=document.getElementById('eyeIcon');
    if(!btn||!inp)return;
    btn.addEventListener('click',function(){
        var hidden=inp.type==='password';
        inp.type=hidden?'text':'password';
        eye.className=hidden?'bi bi-eye-slash':'bi bi-eye';
    });
})();
</script>
</body>
</html>
