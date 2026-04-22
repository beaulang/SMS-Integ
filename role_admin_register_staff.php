<?php
// Included inside dashboard.php
?>

<style>
    .section-title { font-family: 'Playfair Display', serif; font-size: 1.4rem; margin-bottom: 6px; color: #0f1923; }
    .section-sub   { color: #6b7280; font-size: 0.87rem; margin-bottom: 28px; }

    .form-card {
        background: white;
        border-radius: 12px;
        padding: 32px 36px;
        border: 1px solid #e5e7eb;
        max-width: 560px;
    }

    .form-card h3 {
        font-family: 'Playfair Display', serif;
        font-size: 1.1rem;
        margin-bottom: 22px;
        color: #0f1923;
        padding-bottom: 14px;
        border-bottom: 1px solid #f3f4f6;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-group label {
        display: block;
        font-size: 0.82rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 7px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .form-group input,
    .form-group select {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.9rem;
        font-family: 'Inter', sans-serif;
        color: #0f1923;
        transition: border-color 0.2s, box-shadow 0.2s;
        background: white;
    }

    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: #D4AF37;
        box-shadow: 0 0 0 3px rgba(212,175,55,0.12);
    }

    .form-hint { font-size: 0.77rem; color: #9ca3af; margin-top: 5px; }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

    .submit-btn {
        width: 100%;
        padding: 14px;
        background: #D4AF37;
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 700;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        letter-spacing: 0.5px;
        transition: background 0.2s, transform 0.1s;
        margin-top: 8px;
    }

    .submit-btn:hover { background: #a8891e; transform: translateY(-1px); }

    .role-info {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: 14px 18px;
        font-size: 0.82rem;
        color: #92400e;
        margin-bottom: 24px;
        line-height: 1.6;
    }
</style>

<div class="section-title">Register Staff or Admin</div>
<div class="section-sub">Create accounts for faculty members or additional administrators. These accounts are immediately active — no approval required.</div>

<div class="role-info">
    🔒 <strong>Admin privilege:</strong> Only administrators can create faculty and admin accounts. Students self-register at the registration page and require your approval before they can log in.
</div>

<div class="form-card">
    <h3>➕ New Account Details</h3>
    <form action="process_admin_register.php" method="POST">

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" placeholder="e.g. Ben Ben" required>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Max 16 characters" maxlength="16" required>
                <div class="form-hint">Alphanumeric, max 16 chars</div>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="" disabled selected>Select role…</option>
                    <option value="faculty">Faculty</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" placeholder="e.g. benedetta@gmail.com" required>
            <div class="form-hint">OTP will be sent to this address during login</div>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phonenumber" placeholder="11 digits (e.g. 09171234567)"
                   minlength="11" maxlength="11" pattern="\d{11}" title="Exactly 11 digits" required>
        </div>

        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="8–16 chars, upper/lower/number/symbol" maxlength="16" required>
            <div class="form-hint">Must be 8–16 chars with uppercase, lowercase, number, and special character</div>
        </div>

        <button type="submit" class="submit-btn">Create Account</button>
    </form>
</div>