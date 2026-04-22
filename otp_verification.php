<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LUP Portal — OTP Verification</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --blue:        #1A3F7A;
            --blue-light:  #2563EB;
            --blue-dark:   #0F2550;
            --orange:      #F97316;
            --orange-light:#FB923C;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            background: var(--blue-dark);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .bg { position: fixed; inset: 0; z-index: 0; }
        .bg-blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.25; animation: float 8s ease-in-out infinite; }
        .bg-blob-1 { width: 450px; height: 450px; background: var(--blue-light); top: -80px; right: -80px; }
        .bg-blob-2 { width: 350px; height: 350px; background: var(--orange); bottom: -60px; left: -60px; animation-delay: -4s; }
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
            padding: 52px 44px;
            width: 420px; max-width: 94vw;
            text-align: center;
            animation: cardIn 0.7s cubic-bezier(0.16,1,0.3,1) forwards;
            opacity: 0; transform: translateY(24px);
        }
        @keyframes cardIn { to { opacity:1; transform:translateY(0); } }

        .otp-icon {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, var(--blue-light), var(--blue-dark));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
            margin: 0 auto 24px;
            border: 2px solid rgba(249,115,22,0.3);
            box-shadow: 0 0 0 8px rgba(37,99,235,0.1);
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%,100% { box-shadow: 0 0 0 8px rgba(37,99,235,0.1); }
            50%      { box-shadow: 0 0 0 16px rgba(37,99,235,0.05); }
        }

        .card-title { font-family: 'Syne', sans-serif; font-size: 1.6rem; font-weight: 800; color: white; margin-bottom: 8px; }
        .card-title span { color: var(--orange); }
        .card-sub { color: rgba(255,255,255,0.4); font-size: 0.875rem; margin-bottom: 36px; line-height: 1.6; }
        .card-sub strong { color: rgba(255,255,255,0.7); }

        /* 6-box OTP input */
        .otp-boxes {
            display: flex; gap: 10px; justify-content: center;
            margin-bottom: 28px;
        }
        .otp-box {
            width: 52px; height: 64px;
            background: rgba(255,255,255,0.08);
            border: 2px solid rgba(255,255,255,0.15);
            border-radius: 12px;
            font-family: 'Syne', sans-serif;
            font-size: 1.6rem; font-weight: 800;
            color: white; text-align: center;
            outline: none;
            transition: all 0.2s;
            caret-color: var(--orange);
        }
        .otp-box:focus {
            border-color: var(--orange);
            background: rgba(249,115,22,0.1);
            box-shadow: 0 0 0 3px rgba(249,115,22,0.2);
            transform: scale(1.05);
        }
        .otp-box.filled { border-color: var(--blue-light); }

        /* Timer */
        .timer-wrap { margin-bottom: 24px; }
        .timer-bar-bg { height: 4px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; margin-bottom: 8px; }
        .timer-bar { height: 100%; background: linear-gradient(90deg, var(--blue-light), var(--orange)); border-radius: 4px; transition: width 1s linear; }
        .timer-txt { font-size: 0.8rem; color: rgba(255,255,255,0.4); }
        .timer-txt span { color: var(--orange-light); font-weight: 600; }

        .btn-verify {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, var(--blue-light), var(--blue));
            color: white; border: none; border-radius: 12px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem; font-weight: 600;
            cursor: pointer; transition: all 0.3s;
            box-shadow: 0 4px 20px rgba(37,99,235,0.35);
        }
        .btn-verify:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(37,99,235,0.5); }
        .btn-verify:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }

        .alert { padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 0.84rem; }
        .alert-error { background: rgba(220,38,38,0.15); border: 1px solid rgba(220,38,38,0.3); color: #fca5a5; }

        /* Hidden real input that collects the value */
        #otpHidden { display: none; }
    </style>
</head>
<body>
    <div class="bg">
        <div class="bg-blob bg-blob-1"></div>
        <div class="bg-blob bg-blob-2"></div>
    </div>
    <div class="bg-grid"></div>

    <div class="card">
        <div class="otp-icon">🔐</div>
        <div class="card-title">Verify <span>OTP</span></div>
        <div class="card-sub">A 6-digit code has been sent to your registered email.<br><strong>Enter it below to continue.</strong></div>

        <?php if(isset($_SESSION['otp_error'])): ?>
            <div class="alert alert-error">
                ⚠️ <?php echo htmlspecialchars($_SESSION['otp_error']); unset($_SESSION['otp_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Timer -->
        <div class="timer-wrap">
            <div class="timer-bar-bg"><div class="timer-bar" id="timerBar"></div></div>
            <div class="timer-txt">Code expires in <span id="timerTxt">5:00</span></div>
        </div>

        <form action="otp_verification_process.php" method="POST" id="otpForm">
            <div class="otp-boxes" id="otpBoxes">
                <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="\d">
                <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="\d">
                <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="\d">
                <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="\d">
                <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="\d">
                <input class="otp-box" type="text" maxlength="1" inputmode="numeric" pattern="\d">
            </div>
            <input type="hidden" name="otp" id="otpHidden">
            <button type="submit" class="btn-verify" id="verifyBtn" disabled>Verify Code →</button>
        </form>
    </div>

    <script>
        // OTP box auto-advance
        const boxes = document.querySelectorAll('.otp-box');
        const hidden = document.getElementById('otpHidden');
        const verifyBtn = document.getElementById('verifyBtn');

        function updateHidden() {
            let val = '';
            boxes.forEach(b => val += b.value);
            hidden.value = val;
            verifyBtn.disabled = val.length < 6;
            boxes.forEach(b => b.classList.toggle('filled', b.value !== ''));
        }

        boxes.forEach((box, i) => {
            box.addEventListener('input', function(e) {
                // Only allow digits
                this.value = this.value.replace(/\D/g, '');
                if (this.value && i < boxes.length - 1) boxes[i + 1].focus();
                updateHidden();
            });

            box.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && i > 0) {
                    boxes[i - 1].value = '';
                    boxes[i - 1].focus();
                    updateHidden();
                }
                // Allow paste
                if (e.key === 'v' && (e.ctrlKey || e.metaKey)) return;
            });

            box.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
                [...pasted].forEach((ch, idx) => {
                    if (idx < boxes.length) { boxes[idx].value = ch; }
                });
                updateHidden();
                const nextEmpty = [...boxes].findIndex(b => !b.value);
                if (nextEmpty !== -1) boxes[nextEmpty].focus(); else boxes[5].focus();
            });
        });

        boxes[0].focus();

        // 5-minute countdown timer
        let total = 300;
        const timerTxt = document.getElementById('timerTxt');
        const timerBar = document.getElementById('timerBar');

        const countdown = setInterval(() => {
            total--;
            const m = Math.floor(total / 60);
            const s = total % 60;
            timerTxt.textContent = m + ':' + String(s).padStart(2, '0');
            timerBar.style.width = ((total / 300) * 100) + '%';
            if (total <= 60) { timerTxt.parentElement.style.color = '#fca5a5'; timerBar.style.background = '#ef4444'; }
            if (total <= 0) {
                clearInterval(countdown);
                timerTxt.textContent = 'Expired';
                verifyBtn.disabled = true;
                verifyBtn.textContent = 'Code Expired — Please Login Again';
            }
        }, 1000);

        // Submit loading state
        document.getElementById('otpForm').addEventListener('submit', function() {
            verifyBtn.textContent = 'Verifying…';
            verifyBtn.disabled = true;
        });
    </script>
</body>
</html>