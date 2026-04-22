<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUP Portal — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue:        #1A3F7A;
            --blue-light:  #2563EB;
            --blue-dark:   #0F2550;
            --orange:      #F97316;
            --orange-light:#FB923C;
            --white:       #FFFFFF;
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
        }

        .bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
        .bg-blob {
            position: absolute; border-radius: 50%;
            filter: blur(80px); opacity: 0.3;
            animation: float 8s ease-in-out infinite;
        }
        .bg-blob-1 { width: 500px; height: 500px; background: var(--blue-light); top: -100px; right: -80px; }
        .bg-blob-2 { width: 350px; height: 350px; background: var(--orange); bottom: -60px; left: -60px; animation-delay: -4s; }
        .bg-grid {
            position: fixed; inset: 0;
            background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: 0;
        }
        @keyframes float {
            0%,100% { transform: translateY(0); }
            50%      { transform: translateY(-25px); }
        }

        .card {
            position: relative; z-index: 10;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 24px;
            padding: 48px 44px;
            width: 420px; max-width: 94vw;
            animation: cardIn 0.7s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0; transform: translateY(24px);
        }
        @keyframes cardIn { to { opacity:1; transform:translateY(0); } }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: rgba(255,255,255,0.4); font-size: 0.8rem;
            text-decoration: none; margin-bottom: 28px;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--orange-light); }

        .card-header { margin-bottom: 32px; }
        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.7rem; font-weight: 800;
            color: white; margin-bottom: 6px;
        }
        .card-title span { color: var(--orange); }
        .card-sub { color: rgba(255,255,255,0.45); font-size: 0.875rem; }

        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block; font-size: 0.75rem;
            font-weight: 600; color: rgba(255,255,255,0.55);
            text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .form-group input {
            width: 100%; padding: 13px 16px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem; color: white;
            transition: all 0.2s;
            outline: none;
        }
        .form-group input::placeholder { color: rgba(255,255,255,0.25); }
        .form-group input:focus {
            border-color: var(--blue-light);
            background: rgba(37,99,235,0.12);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.2);
        }

        .btn-submit {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, var(--blue-light), var(--blue));
            color: white; border: none; border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem; font-weight: 600;
            letter-spacing: 0.5px; cursor: pointer;
            transition: all 0.3s; margin-top: 8px;
            box-shadow: 0 4px 20px rgba(37,99,235,0.35);
            position: relative; overflow: hidden;
        }
        .btn-submit::after {
            content: '';
            position: absolute; top: 50%; left: 50%;
            width: 0; height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%; transform: translate(-50%,-50%);
            transition: width 0.5s, height 0.5s;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 32px rgba(37,99,235,0.5); }
        .btn-submit:active::after { width: 300px; height: 300px; }

        .alert {
            padding: 12px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 0.84rem;
            display: flex; align-items: center; gap: 8px;
            animation: alertIn 0.3s ease;
        }
        @keyframes alertIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
        .alert-error { background: rgba(220,38,38,0.15); border: 1px solid rgba(220,38,38,0.3); color: #fca5a5; }

        .card-footer {
            margin-top: 24px; text-align: center;
            font-size: 0.84rem; color: rgba(255,255,255,0.35);
        }
        .card-footer a { color: var(--orange-light); text-decoration: none; font-weight: 500; }
        .card-footer a:hover { text-decoration: underline; }

        .input-icon-wrap { position: relative; }
        .input-icon {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.3); font-size: 1rem;
            cursor: pointer; user-select: none;
            transition: color 0.2s;
        }
        .input-icon:hover { color: var(--orange-light); }
    </style>
</head>
<body>
    <div class="bg">
        <div class="bg-blob bg-blob-1"></div>
        <div class="bg-blob bg-blob-2"></div>
    </div>
    <div class="bg-grid"></div>

    <div class="card">
        <a href="index.php" class="back-link">← Back to Home</a>

        <div class="card-header">
            <div class="card-title"><span>LUP</span> Portal Login</div>
            <div class="card-sub">Sign in to access your dashboard</div>
        </div>

        <?php if(isset($_SESSION['login_error'])): ?>
            <div class="alert alert-error">
                ⚠️ <?php echo htmlspecialchars($_SESSION['login_error']); unset($_SESSION['login_error']); ?>
            </div>
        <?php endif; ?>

        <form action="process_login.php" method="POST" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Enter your username" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-icon-wrap">
                    <input type="password" name="password" id="password" placeholder="Enter your password" required autocomplete="current-password">
                    <span class="input-icon" id="togglePw" title="Show/hide password">👁</span>
                </div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">Sign In →</button>
        </form>

        <div class="card-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePw').addEventListener('click', function() {
            const pw = document.getElementById('password');
            const isHidden = pw.type === 'password';
            pw.type = isHidden ? 'text' : 'password';
            this.textContent = isHidden ? '🙈' : '👁';
        });

        // Button loading state on submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.textContent = 'Signing in…';
            btn.disabled = true;
            btn.style.opacity = '0.7';
        });

        // Input focus animation
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', () => input.parentElement.classList.add('focused'));
            input.addEventListener('blur',  () => input.parentElement.classList.remove('focused'));
        });
    </script>
</body>
</html>