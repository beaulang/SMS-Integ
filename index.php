<?php
$portal_name = "LUP PORTAL";
$tagline = "Learn · Unite · Progress";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUP Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue:        #1A3F7A;
            --blue-light:  #2563EB;
            --blue-dark:   #0F2550;
            --orange:      #F97316;
            --orange-light:#FB923C;
            --white:       #FFFFFF;
            --off-white:   #F8FAFF;
            --glass:       rgba(255,255,255,0.07);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            background: var(--blue-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated background blobs */
        .bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .bg-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.35;
            animation: float 8s ease-in-out infinite;
        }

        .bg-blob-1 {
            width: 500px; height: 500px;
            background: var(--blue-light);
            top: -120px; left: -100px;
            animation-delay: 0s;
        }

        .bg-blob-2 {
            width: 400px; height: 400px;
            background: var(--orange);
            bottom: -80px; right: -60px;
            animation-delay: -3s;
        }

        .bg-blob-3 {
            width: 300px; height: 300px;
            background: var(--blue);
            top: 50%; right: 10%;
            animation-delay: -5s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50%       { transform: translateY(-30px) scale(1.05); }
        }

        /* Grid overlay */
        .bg-grid {
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
        }

        .card {
            position: relative;
            z-index: 10;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 24px;
            padding: 56px 48px;
            width: 440px;
            max-width: 94vw;
            text-align: center;
            box-shadow: 0 32px 80px rgba(0,0,0,0.4), inset 0 1px 0 rgba(255,255,255,0.1);
            animation: cardIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes cardIn {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Logo mark */
        .logo-mark {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--blue-light) 0%, var(--orange) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Syne', sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            color: white;
            margin: 0 auto 28px;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
            position: relative;
        }

        .logo-mark::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 19px;
            background: linear-gradient(135deg, var(--blue-light), var(--orange));
            z-index: -1;
            opacity: 0.4;
            filter: blur(8px);
        }

        .portal-name {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--white);
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .portal-name span { color: var(--orange); }

        .tagline {
            font-size: 0.82rem;
            font-weight: 500;
            color: rgba(255,255,255,0.45);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 44px;
        }

        .divider {
            width: 48px;
            height: 2px;
            background: linear-gradient(90deg, var(--blue-light), var(--orange));
            border-radius: 2px;
            margin: 0 auto 44px;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 16px 24px;
            border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-decoration: none;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            margin-bottom: 14px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0);
            transition: background 0.2s;
        }

        .btn:hover::before { background: rgba(255,255,255,0.08); }

        .btn-primary {
            background: linear-gradient(135deg, var(--blue-light) 0%, var(--blue) 100%);
            color: white;
            border: 1px solid rgba(255,255,255,0.15);
            box-shadow: 0 4px 20px rgba(37,99,235,0.35);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 32px rgba(37,99,235,0.5);
        }

        .btn-secondary {
            background: transparent;
            color: var(--orange-light);
            border: 1px solid rgba(249,115,22,0.4);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            background: rgba(249,115,22,0.08);
            border-color: var(--orange);
            box-shadow: 0 8px 24px rgba(249,115,22,0.2);
        }

        footer {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.72rem;
            color: rgba(255,255,255,0.25);
            letter-spacing: 1px;
            z-index: 10;
        }

        /* Floating particles */
        .particle {
            position: fixed;
            width: 4px;
            height: 4px;
            background: var(--orange);
            border-radius: 50%;
            opacity: 0;
            animation: rise 6s ease-in infinite;
        }

        @keyframes rise {
            0%   { opacity: 0; transform: translateY(100vh) scale(0); }
            10%  { opacity: 0.6; }
            90%  { opacity: 0.2; }
            100% { opacity: 0; transform: translateY(-20vh) scale(1); }
        }
    </style>
</head>
<body>
    <div class="bg">
        <div class="bg-blob bg-blob-1"></div>
        <div class="bg-blob bg-blob-2"></div>
        <div class="bg-blob bg-blob-3"></div>
    </div>
    <div class="bg-grid"></div>

    <!-- Particles -->
    <div id="particles"></div>

    <div class="card">
        <div class="logo-mark">L</div>
        <div class="portal-name"><?= htmlspecialchars($portal_name) ?></div>
        <div class="tagline"><?= htmlspecialchars($tagline) ?></div>
        <div class="divider"></div>

        <a href="login.php" class="btn btn-primary">🔑 Portal Login</a>
        <a href="register.php" class="btn btn-secondary">📝 Student Registration</a>
    </div>

    <footer>&copy; <?= date("Y") ?> LUP Portal — All Rights Reserved</footer>

    <script>
        // Generate floating particles
        const container = document.getElementById('particles');
        for (let i = 0; i < 18; i++) {
            const p = document.createElement('div');
            p.className = 'particle';
            p.style.left = Math.random() * 100 + 'vw';
            p.style.animationDelay = (Math.random() * 6) + 's';
            p.style.animationDuration = (4 + Math.random() * 6) + 's';
            p.style.width = p.style.height = (2 + Math.random() * 4) + 'px';
            p.style.background = Math.random() > 0.5 ? '#F97316' : '#2563EB';
            container.appendChild(p);
        }

        // Parallax on mouse move
        document.addEventListener('mousemove', e => {
            const x = (e.clientX / window.innerWidth - 0.5) * 20;
            const y = (e.clientY / window.innerHeight - 0.5) * 20;
            document.querySelectorAll('.bg-blob').forEach((b, i) => {
                const factor = (i + 1) * 0.4;
                b.style.transform = `translate(${x * factor}px, ${y * factor}px)`;
            });
        });
    </script>
</body>
</html>