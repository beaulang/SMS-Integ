<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUP Portal — Student Registration</title>
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
            padding: 32px 16px;
            overflow-x: hidden;
        }

        .bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
        .bg-blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.25; animation: float 8s ease-in-out infinite; }
        .bg-blob-1 { width: 500px; height: 500px; background: var(--blue-light); top: -100px; left: -100px; }
        .bg-blob-2 { width: 400px; height: 400px; background: var(--orange); bottom: -80px; right: -60px; animation-delay: -4s; }
        .bg-grid {
            position: fixed; inset: 0;
            background-image: linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px; z-index: 0;
        }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-20px); } }

        .card {
            position: relative; z-index: 10;
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 24px;
            padding: 48px 44px;
            width: 480px; max-width: 96vw;
            animation: cardIn 0.7s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0; transform: translateY(24px);
        }
        @keyframes cardIn { to { opacity:1; transform:translateY(0); } }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: rgba(255,255,255,0.4); font-size: 0.8rem;
            text-decoration: none; margin-bottom: 28px; transition: color 0.2s;
        }
        .back-link:hover { color: var(--orange-light); }

        .card-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.7rem; font-weight: 800; color: white; margin-bottom: 6px;
        }
        .card-title span { color: var(--orange); }
        .card-sub { color: rgba(255,255,255,0.4); font-size: 0.875rem; margin-bottom: 32px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-size: 0.73rem; font-weight: 600;
            color: rgba(255,255,255,0.5); text-transform: uppercase;
            letter-spacing: 1px; margin-bottom: 7px;
        }
        .form-group input {
            width: 100%; padding: 12px 14px;
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem; color: white; outline: none;
            transition: all 0.2s;
        }
        .form-group input::placeholder { color: rgba(255,255,255,0.22); }
        .form-group input:focus {
            border-color: var(--orange);
            background: rgba(249,115,22,0.08);
            box-shadow: 0 0 0 3px rgba(249,115,22,0.15);
        }

        /* Password strength bar */
        .pw-bar-wrap { margin-top: 8px; height: 4px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; }
        .pw-bar { height: 100%; width: 0%; border-radius: 4px; transition: all 0.4s; }

        .btn-submit {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, var(--orange) 0%, #c2580e 100%);
            color: white; border: none; border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem; font-weight: 600;
            letter-spacing: 0.5px; cursor: pointer;
            transition: all 0.3s; margin-top: 8px;
            box-shadow: 0 4px 20px rgba(249,115,22,0.35);
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(249,115,22,0.5); }
        .btn-submit:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .alert {
            padding: 12px 16px; border-radius: 10px;
            margin-bottom: 20px; font-size: 0.84rem;
            animation: alertIn 0.3s ease;
        }
        @keyframes alertIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
        .alert-error   { background: rgba(220,38,38,0.15); border: 1px solid rgba(220,38,38,0.3); color: #fca5a5; }
        .alert-success { background: rgba(22,163,74,0.15); border: 1px solid rgba(22,163,74,0.3); color: #86efac; }

        .card-footer { margin-top: 22px; text-align: center; font-size: 0.84rem; color: rgba(255,255,255,0.35); }
        .card-footer a { color: var(--blue-light); text-decoration: none; font-weight: 500; }
        .card-footer a:hover { text-decoration: underline; }

        .hint { font-size: 0.72rem; color: rgba(255,255,255,0.3); margin-top: 4px; }
        .pw-label { display: flex; justify-content: space-between; align-items: center; }
        .pw-strength-txt { font-size: 0.7rem; color: rgba(255,255,255,0.4); }
    </style>
</head>
<body>
    <div class="bg">
        <div class="bg-blob bg-blob-1"></div>
        <div class="bg-blob bg-blob-2"></div>
    </div>
    <div class="bg-grid"></div>

    <div class="card">
        <a href="index.php" class="back-link">← Back</a>

        <div class="card-title"><span>Student</span> Registration</div>
        <div class="card-sub">Fill in all fields to create your LUP Portal account</div>

        <?php if(isset($_SESSION['msg'])): ?>
            <div class="alert alert-<?= $_SESSION['msg_type'] ?>">
                <?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); unset($_SESSION['msg_type']); ?>
            </div>
        <?php endif; ?>

        <form action="process_register.php" method="POST" id="regForm">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="fullname" placeholder="e.g. Juan Dela Cruz" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Max 16 chars" maxlength="16" required>
                </div>
                <div class="form-group">
                    <label>Phone (11 digits)</label>
                    <input type="text" name="phonenumber" placeholder="91234567890" minlength="11" maxlength="11" pattern="\d{11}" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="you@email.com" required>
            </div>

            <div class="form-group">
                <div class="pw-label">
                    <label>Password</label>
                    <span class="pw-strength-txt" id="pwStrengthTxt"></span>
                </div>
                <input type="password" name="password" id="pwInput" placeholder="8–16 chars, upper/lower/num/symbol" maxlength="16" required>
                <div class="pw-bar-wrap"><div class="pw-bar" id="pwBar"></div></div>
                <div class="hint">Min 8 chars with uppercase, lowercase, number, and special character</div>
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" id="confirmPw" placeholder="Re-enter your password" maxlength="16" required>
                <div class="hint" id="matchHint"></div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">Create Account →</button>
        </form>

        <div class="card-footer">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

    <script>
        // Password strength meter
        const pwInput = document.getElementById('pwInput');
        const pwBar   = document.getElementById('pwBar');
        const pwTxt   = document.getElementById('pwStrengthTxt');
        const confirmPw = document.getElementById('confirmPw');
        const matchHint = document.getElementById('matchHint');

        pwInput.addEventListener('input', function() {
            const v = this.value;
            let score = 0;
            if (v.length >= 8) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/[a-z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            if (/[\W_]/.test(v)) score++;

            const colors = ['#ef4444','#f97316','#eab308','#22c55e','#10b981'];
            const labels = ['Very Weak','Weak','Fair','Strong','Very Strong'];
            const pct    = (score / 5) * 100;
            pwBar.style.width = pct + '%';
            pwBar.style.background = colors[score - 1] || 'transparent';
            pwTxt.textContent = score > 0 ? labels[score - 1] : '';
            pwTxt.style.color = colors[score - 1] || '';
        });

        // Password match check
        confirmPw.addEventListener('input', function() {
            if (!this.value) { matchHint.textContent = ''; return; }
            if (this.value === pwInput.value) {
                matchHint.textContent = '✔ Passwords match';
                matchHint.style.color = '#86efac';
            } else {
                matchHint.textContent = '✘ Passwords do not match';
                matchHint.style.color = '#fca5a5';
            }
        });

        // Loading state
        document.getElementById('regForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.textContent = 'Creating account…';
            btn.disabled = true;
        });
    </script>
</body>
</html>