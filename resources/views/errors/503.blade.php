{{-- Halaman mandiri (tanpa layout/Vite/database) agar tetap tampil saat aplikasi bermasalah. --}}
@php $en = request()->segment(1) === 'en'; @endphp
<!DOCTYPE html>
<html lang="{{ $en ? 'en' : 'id' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $en ? 'Under maintenance' : 'Sedang dalam pemeliharaan' }} | DECAA.ID</title>
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;
             font-family:'Plus Jakarta Sans',system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#0b1640;color:#fff;text-align:center}
        .box{max-width:520px}
        .code{font-size:72px;font-weight:800;line-height:1;color:#fff}
        h1{font-size:28px;margin:16px 0 0;font-weight:800}
        p{margin:14px 0 0;line-height:1.7;color:#c9d3f5}
        .btns{margin-top:28px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
        a,button{text-transform:capitalize;font:inherit;font-weight:600;font-size:15px;padding:12px 22px;border-radius:8px;cursor:pointer;text-decoration:none}
        .primary{background:#fff;color:#1B3590;border:0}
        .ghost{background:transparent;color:#fff;border:1px solid rgba(255,255,255,.4)}
        img{height:36px;width:auto;margin-bottom:28px}
    </style>
</head>
<body>
    <main class="box">
        <img src="{{ logo_url('img/logo/decaaid-white.png') }}" alt="DECAA.ID" onerror="this.style.display='none'">
        <div class="code">503</div>
        <h1>{{ $en ? 'Under maintenance' : 'Sedang dalam pemeliharaan' }}</h1>
        <p>{{ $en ? 'We are improving the site for you. Please check back shortly.' : 'Kami sedang meningkatkan situs untuk Anda. Silakan kembali beberapa saat lagi.' }}</p>
        <div class="btns">
            <button type="button" class="primary" onclick="location.reload()">{{ $en ? 'Reload' : 'Muat ulang' }}</button>
            <a class="ghost" href="/{{ $en ? 'en' : 'id' }}">{{ $en ? 'Home' : 'Beranda' }}</a>
        </div>
    </main>
</body>
</html>