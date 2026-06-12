<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Supermarket — Sistema De Gestion De Ventas. Inicio de sesión seguro.">

    <title>Iniciar Sesión | Supermarket</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        try {
            if (localStorage.getItem('darkMode') === '1') {
                document.documentElement.classList.add('dark-mode');
                document.body.classList.add('dark-mode');
            }
        } catch(e) {}
    </script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: 'Inter', 'Poppins', sans-serif;
            background: #f1f5f9;
            overflow: hidden;
            position: relative;
        }

        #loader {
            position: fixed; inset: 0; z-index: 99999;
            background: linear-gradient(135deg, #0f172a, #1e1b4b, #4f46e5);
            background-size: 400% 400%;
            animation: gradientShift 8s ease infinite;
            display: flex; justify-content: center; align-items: center;
            transition: opacity .5s ease, visibility .5s ease;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50%      { background-position: 100% 50%; }
        }
        .loader-inner { text-align: center; color: #fff; }
        .loader-logo {
            width: 90px; height: 90px; margin: 0 auto 20px;
            border-radius: 24px;
            background: rgba(255,255,255,.12);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.2);
            display: flex; justify-content: center; align-items: center;
            font-size: 38px; color: #fff;
            animation: pulse 1.8s ease-in-out infinite;
            box-shadow: 0 20px 50px rgba(0,0,0,.3);
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 20px 50px rgba(0,0,0,.3); }
            50%      { transform: scale(1.08); box-shadow: 0 28px 70px rgba(79,70,229,.5); }
        }

        .page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            z-index: 2;
        }

        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: .55;
            pointer-events: none;
            z-index: 1;
        }
        .bg-orb-1 { width: 520px; height: 520px; top: -180px; left: -120px; background: #6366f1; animation: float1 14s ease-in-out infinite; }
        .bg-orb-2 { width: 480px; height: 480px; bottom: -160px; right: -100px; background: #0ea5e9; animation: float2 16s ease-in-out infinite; }
        .bg-orb-3 { width: 380px; height: 380px; top: 40%; right: 30%; background: #10b981; opacity: .35; animation: float3 18s ease-in-out infinite; }
        @keyframes float1 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(40px,30px)} }
        @keyframes float2 { 0%,100%{transform:translate(0,0)} 50%{transform:translate(-30px,-40px)} }
        @keyframes float3 { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(20px,-20px) scale(1.08)} }

        .login-shell {
            width: 100%;
            max-width: 520px;
            min-height: auto;
            background: rgba(255,255,255,.88);
            backdrop-filter: blur(26px);
            -webkit-backdrop-filter: blur(26px);
            border: 1px solid rgba(255,255,255,.85);
            border-radius: 32px;
            box-shadow:
                0 50px 120px rgba(15,23,42,.18),
                0 16px 40px rgba(79,70,229,.12);
            overflow: hidden;
            position: relative;
            animation: shellIn .9s cubic-bezier(.18,.9,.32,1);
        }
        .login-shell::before {
            content: '';
            position: absolute;
            inset: 0 0 auto 0;
            height: 6px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed, #ec4899, #f59e0b, #10b981, #4f46e5);
            background-size: 300% 100%;
            animation: rainbowBar 8s linear infinite;
        }
        /* Borde animado premium con conic gradient */
        .login-shell::after {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 34px;
            padding: 2px;
            background: conic-gradient(
                from 0deg,
                rgba(79,70,229,.35),
                rgba(236,72,153,.2),
                rgba(16,185,129,.25),
                rgba(79,70,229,.35)
            );
            -webkit-mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            mask:
                linear-gradient(#fff 0 0) content-box,
                linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            pointer-events: none;
            z-index: 0;
            opacity: .6;
            animation: rotateConic 12s linear infinite;
        }
        @keyframes rotateConic {
            0%   { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .login-shell > * { position: relative; z-index: 1; }
        @keyframes rainbowBar {
            0% { background-position: 0% 0; }
            100% { background-position: 300% 0; }
        }
        @keyframes shellIn {
            from { opacity: 0; transform: translateY(30px) scale(.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand-strip {
            padding: 30px 44px 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 14px;
            position: relative;
        }
        .brand-strip-logo {
            width: 76px; height: 76px;
            border-radius: 24px;
            background: linear-gradient(145deg, #4f46e5 0%, #6d28d9 55%, #7c3aed 100%);
            display: flex; justify-content: center; align-items: center;
            font-size: 32px; color: #fff;
            box-shadow:
                0 18px 40px rgba(79,70,229,.35),
                inset 0 1px 0 rgba(255,255,255,.25);
            position: relative;
            transition: all .4s cubic-bezier(0.34, 1.56, 0.64, 1);
            cursor: pointer;
        }
        .brand-strip-logo:hover {
            transform: translateY(-4px) scale(1.06) rotate(3deg);
            box-shadow: 0 24px 50px rgba(79,70,229,.45), inset 0 1px 0 rgba(255,255,255,.35);
        }
        .brand-strip-logo::after {
            content: '';
            position: absolute;
            inset: -6px;
            border-radius: 28px;
            background: linear-gradient(145deg, rgba(79,70,229,.3), rgba(124,58,237,.15));
            z-index: -1;
            filter: blur(12px);
            animation: pulseGlow 3s ease-in-out infinite alternate;
        }
        @keyframes pulseGlow {
            0% { opacity: .5; transform: scale(.96); }
            100% { opacity: 1; transform: scale(1.04); }
        }
        .brand-strip-title {
            font-size: 1.65rem;
            font-weight: 900;
            letter-spacing: -.02em;
            background: linear-gradient(135deg, #0f172a 0%, #4338ca 40%, #7c3aed 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1.1;
        }
        .brand-strip-subtitle {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 16px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(79,70,229,.1), rgba(124,58,237,.08));
            border: 1px solid rgba(79,70,229,.16);
            color: #4f46e5;
            font-size: .74rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(79,70,229,.08);
        }
        @keyframes liveDot {
            0% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(16,185,129,0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 7px rgba(16,185,129,0); }
            100% { transform: scale(0.9); box-shadow: 0 0 0 0 rgba(16,185,129,0); }
        }
        .brand-strip-subtitle::before {
            content: ''; width: 7px; height: 7px; border-radius: 50%;
            background: #10b981;
            display: inline-block;
            animation: liveDot 2s infinite ease-in-out;
        }

        .form-side {
            padding: 14px 44px 34px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }
        .form-top {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }
        .dark-toggle {
            width: 40px; height: 40px;
            border-radius: 12px;
            border: none;
            background: #f1f5f9;
            color: #475569;
            cursor: pointer;
            transition: all .2s;
            display: flex; justify-content: center; align-items: center;
        }
        .dark-toggle:hover { background: #e2e8f0; color: #0f172a; transform: translateY(-1px); }

        .form-header { margin-bottom: 28px; }
        .eyebrow {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: linear-gradient(90deg, rgba(99,102,241,.1), rgba(124,58,237,.1));
            color: #4f46e5;
            font-size: .7rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: 14px;
        }
        .eyebrow::before {
            content: ''; width: 6px; height: 6px; border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 0 0 3px rgba(16,185,129,.18);
        }
        .form-header h2 {
            font-size: 1.95rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            letter-spacing: -.02em;
        }
        .form-header .lead {
            color: #64748b;
            font-size: .9rem;
            margin: 0;
        }

        .datetime-strip {
            display: flex; align-items: center; justify-content: space-between;
            padding: 10px 14px;
            background: linear-gradient(90deg, rgba(14,165,233,.06), rgba(79,70,229,.06));
            border: 1px solid rgba(99,102,241,.12);
            border-radius: 12px;
            margin-bottom: 22px;
            font-size: .8rem;
            color: #475569;
            font-weight: 500;
        }
        .datetime-strip i { color: #6366f1; }
        .datetime-strip strong { color: #0f172a; }

        .alert-stack { margin-bottom: 18px; }

        .form-label {
            font-size: .82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .input-wrap { position: relative; margin-bottom: 18px; }
        .input-wrap .icon-left {
            position: absolute;
            top: 50%; left: 16px;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: .95rem;
            transition: color .2s;
            z-index: 2;
            pointer-events: none;
        }
        .input-wrap .toggle-pass {
            position: absolute;
            top: 50%; right: 14px;
            transform: translateY(-50%);
            width: 34px; height: 34px;
            border-radius: 10px;
            border: none;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            z-index: 2;
            display: flex; justify-content: center; align-items: center;
            transition: all .15s;
        }
        .input-wrap .toggle-pass:hover { background: #f1f5f9; color: #475569; }
        .input-wrap input {
            width: 100%;
            height: 52px;
            padding: 0 50px 0 46px;
            border-radius: 14px;
            border: 1.5px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            font-size: .92rem;
            font-weight: 500;
            transition: all .2s ease;
            font-family: inherit;
        }
        .input-wrap input::placeholder { color: #94a3b8; font-weight: 400; }
        .input-wrap input:focus {
            outline: none;
            border-color: #6366f1;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(99,102,241,.18), 0 10px 25px -5px rgba(99,102,241,.15);
        }
        .input-wrap input.is-invalid {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239,68,68,.15);
        }

        /* Shake animación cuando hay error */
        @keyframes shakeKey {
            0%,100% { transform: translateX(0); }
            15%,45%,75% { transform: translateX(-8px); }
            30%,60%,90% { transform: translateX(8px); }
        }
        .shake {
            animation: shakeKey .55s cubic-bezier(.36,.07,.19,.97) both;
        }

        /* Popover credenciales demo */
        .demo-fab {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 50;
            width: 54px; height: 54px;
            border-radius: 50%;
            border: none;
            background: linear-gradient(145deg, #0ea5e9, #4f46e5);
            color: #fff;
            font-size: 1.3rem;
            cursor: pointer;
            box-shadow: 0 18px 36px rgba(79,70,229,.38);
            transition: all .25s cubic-bezier(.2,.9,.3,1);
            display: flex; justify-content: center; align-items: center;
        }
        .demo-fab:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 24px 48px rgba(79,70,229,.48);
        }
        .demo-fab:active { transform: translateY(-1px) scale(.98); }

        .demo-pop {
            position: fixed;
            right: 24px;
            bottom: 92px;
            z-index: 50;
            width: min(330px, calc(100vw - 48px));
            background: rgba(255,255,255,.96);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(79,70,229,.18);
            border-radius: 20px;
            padding: 18px;
            box-shadow:
                0 30px 60px rgba(15,23,42,.25),
                0 14px 30px rgba(79,70,229,.18);
            transform-origin: bottom right;
            transform: scale(.9) translateY(12px);
            opacity: 0;
            visibility: hidden;
            transition: all .25s cubic-bezier(.2,.9,.3,1);
        }
        .demo-pop.show {
            transform: scale(1) translateY(0);
            opacity: 1;
            visibility: visible;
        }
        .demo-pop::after {
            content: '';
            position: absolute;
            bottom: -8px;
            right: 30px;
            width: 16px; height: 16px;
            background: rgba(255,255,255,.96);
            border-right: 1px solid rgba(79,70,229,.18);
            border-bottom: 1px solid rgba(79,70,229,.18);
            transform: rotate(45deg);
        }
        .demo-pop-header {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 12px;
        }
        .demo-pop-icon {
            width: 36px; height: 36px;
            border-radius: 12px;
            background: linear-gradient(145deg, #10b981, #0ea5e9);
            color: #fff;
            display: flex; justify-content: center; align-items: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .demo-pop-header strong {
            font-size: .92rem;
            color: #0f172a;
            font-weight: 800;
        }
        .demo-pop-header span {
            font-size: .7rem;
            color: #64748b;
            display: block;
            font-weight: 500;
            margin-top: 2px;
        }
        .demo-user {
            padding: 11px 12px;
            background: linear-gradient(135deg, rgba(79,70,229,.06), rgba(14,165,233,.06));
            border: 1px solid rgba(79,70,229,.12);
            border-radius: 12px;
            margin-bottom: 8px;
            cursor: pointer;
            transition: all .2s;
        }
        .demo-user:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(79,70,229,.1);
            border-color: rgba(79,70,229,.25);
        }
        .demo-user-label {
            font-size: .75rem;
            font-weight: 800;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: 4px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .demo-user-creds {
            font-family: 'Courier New', monospace;
            font-size: .78rem;
            color: #334155;
            font-weight: 600;
            line-height: 1.45;
        }
        .demo-user-creds span { color: #64748b; font-weight: 500; display: inline-block; width: 52px; }

        /* Reveal staggered */
        .reveal-item {
            opacity: 0;
            transform: translateY(14px);
            animation: revealIn .7s cubic-bezier(.2,.9,.3,1) forwards;
        }
        @keyframes revealIn {
            to { opacity: 1; transform: translateY(0); }
        }
        .reveal-item.r1 { animation-delay: .15s; }
        .reveal-item.r2 { animation-delay: .25s; }
        .reveal-item.r3 { animation-delay: .35s; }
        .reveal-item.r4 { animation-delay: .45s; }
        .reveal-item.r5 { animation-delay: .55s; }
        .reveal-item.r6 { animation-delay: .65s; }
        .reveal-item.r7 { animation-delay: .75s; }
        .reveal-item.r8 { animation-delay: .85s; }

        .strength-meter {
            height: 4px;
            border-radius: 999px;
            background: #e2e8f0;
            margin: -10px 0 18px;
            overflow: hidden;
            display: none;
        }
        .strength-meter.active { display: block; }
        .strength-fill {
            height: 100%;
            width: 0;
            border-radius: 999px;
            transition: all .3s ease;
            background: #ef4444;
        }
        .strength-text {
            font-size: .7rem;
            font-weight: 600;
            margin-top: 5px;
            color: #64748b;
        }
        .strength-text.s1 { color: #ef4444; }
        .strength-text.s2 { color: #f97316; }
        .strength-text.s3 { color: #f59e0b; }
        .strength-text.s4 { color: #10b981; }

        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 6px 0 22px;
            font-size: .82rem;
        }
        .form-check-input {
            width: 17px; height: 17px;
            cursor: pointer;
            border: 1.5px solid #cbd5e1;
        }
        .form-check-input:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79,70,229,.18);
        }
        .form-check-label {
            color: #64748b;
            font-weight: 500;
            cursor: pointer;
            padding-left: 4px;
        }
        .forgot-link {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
            transition: color .2s;
        }
        .forgot-link:hover { color: #7c3aed; text-decoration: underline; }

        .btn-login {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #fff;
            font-size: .95rem;
            font-weight: 700;
            letter-spacing: .2px;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: all .25s ease;
            box-shadow: 0 10px 30px rgba(79,70,229,.3);
            font-family: inherit;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px rgba(79,70,229,.42);
            filter: brightness(1.05);
        }
        .btn-login:active { transform: translateY(0); }
        .btn-login:disabled {
            opacity: .75;
            cursor: not-allowed;
            transform: none !important;
        }
        .btn-login .spinner-border {
            width: 18px; height: 18px;
            border-width: 2px;
            margin-right: 8px;
            display: none;
        }
        .btn-login.loading .spinner-border { display: inline-block; }
        .btn-login.loading .btn-label { opacity: .85; }
        .btn-login::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
            transition: left .6s ease;
        }
        .btn-login:hover::after { left: 100%; }

        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 24px 0;
            color: #94a3b8;
            font-size: .75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .1em;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        .tip-card {
            padding: 14px 16px;
            background: linear-gradient(135deg, rgba(16,185,129,.06), rgba(14,165,233,.06));
            border: 1px solid rgba(16,185,129,.15);
            border-radius: 14px;
            display: flex; gap: 12px; align-items: flex-start;
        }
        .tip-card i {
            width: 30px; height: 30px;
            border-radius: 10px;
            background: linear-gradient(135deg, #10b981, #0ea5e9);
            color: #fff;
            display: flex; justify-content: center; align-items: center;
            font-size: .8rem;
            flex-shrink: 0;
            margin-top: 1px;
        }
        .tip-card .tip-text {
            font-size: .78rem;
            color: #475569;
            line-height: 1.5;
        }
        .tip-card .tip-text strong { color: #0f172a; }

        .form-bottom {
            margin-top: auto;
            padding-top: 24px;
            text-align: center;
            font-size: .78rem;
            color: #94a3b8;
            font-weight: 500;
        }
        .form-bottom .version {
            display: inline-block;
            padding: 3px 10px;
            margin-bottom: 6px;
            border-radius: 999px;
            background: rgba(79,70,229,.08);
            color: #4f46e5;
            font-weight: 700;
            font-size: .7rem;
        }

        @media (max-width: 560px) {
            body { overflow: auto; }
            .page-wrapper { padding: 12px; }
            .login-shell { border-radius: 24px; }
            .brand-strip { padding: 24px 22px 12px; }
            .brand-strip-logo { width: 64px; height: 64px; font-size: 26px; border-radius: 20px; }
            .brand-strip-title { font-size: 1.35rem; }
            .form-side { padding: 10px 22px 28px; }
            .form-header h2 { font-size: 1.55rem; }
            .options-row { flex-direction: column; align-items: flex-start; gap: 10px; }
            .bg-orb { filter: blur(55px); }
            .bg-orb-1 { width: 260px; height: 260px; }
            .bg-orb-2 { width: 240px; height: 240px; }
            .bg-orb-3 { width: 200px; height: 200px; }
        }

        /* Dark mode */
        body.dark-mode { background: #030712; }
        body.dark-mode .login-shell {
            background: rgba(15,23,42,.88);
            border-color: rgba(255,255,255,.06);
            box-shadow: 0 40px 100px rgba(0,0,0,.7);
        }
        body.dark-mode .brand-strip-title {
            background: linear-gradient(135deg, #f1f5f9, #a5b4fc, #c4b5fd);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        body.dark-mode .brand-strip-subtitle {
            background: linear-gradient(90deg, rgba(99,102,241,.2), rgba(124,58,237,.18));
            border-color: rgba(99,102,241,.28);
            color: #a5b4fc;
        }
        body.dark-mode .form-header h2 { color: #f1f5f9; }
        body.dark-mode .form-header .lead { color: #94a3b8; }
        body.dark-mode .datetime-strip {
            background: linear-gradient(90deg, rgba(14,165,233,.08), rgba(79,70,229,.08));
            border-color: rgba(99,102,241,.18);
            color: #94a3b8;
        }
        body.dark-mode .datetime-strip strong { color: #e2e8f0; }
        body.dark-mode .form-label { color: #cbd5e1; }
        body.dark-mode .input-wrap input {
            background: rgba(15,23,42,.7);
            border-color: rgba(148,163,184,.2);
            color: #e2e8f0;
        }
        body.dark-mode .input-wrap input::placeholder { color: #64748b; }
        body.dark-mode .input-wrap input:focus {
            background: rgba(15,23,42,.9);
            border-color: #6366f1;
        }
        body.dark-mode .input-wrap .icon-left { color: #64748b; }
        body.dark-mode .input-wrap .toggle-pass { color: #64748b; }
        body.dark-mode .input-wrap .toggle-pass:hover { background: rgba(255,255,255,.05); color: #cbd5e1; }
        body.dark-mode .form-check-label { color: #94a3b8; }
        body.dark-mode .divider { color: #64748b; }
        body.dark-mode .divider::before,
        body.dark-mode .divider::after { background: linear-gradient(90deg, transparent, rgba(148,163,184,.2), transparent); }
        body.dark-mode .tip-card {
            background: linear-gradient(135deg, rgba(16,185,129,.08), rgba(14,165,233,.08));
            border-color: rgba(16,185,129,.2);
        }
        body.dark-mode .tip-card .tip-text { color: #94a3b8; }
        body.dark-mode .tip-card .tip-text strong { color: #e2e8f0; }
        body.dark-mode .dark-toggle {
            background: rgba(255,255,255,.05);
            color: #cbd5e1;
        }
        body.dark-mode .dark-toggle:hover { background: rgba(255,255,255,.1); }
        body.dark-mode .eyebrow {
            background: linear-gradient(90deg, rgba(99,102,241,.18), rgba(124,58,237,.18));
            color: #a5b4fc;
        }
        body.dark-mode .form-bottom { color: #64748b; }
        body.dark-mode .form-bottom .version {
            background: rgba(99,102,241,.15);
            color: #a5b4fc;
        }
        body.dark-mode .strength-meter { background: rgba(255,255,255,.08); }

        body.dark-mode .demo-pop {
            background: rgba(15,23,42,.94);
            border-color: rgba(79,70,229,.25);
        }
        body.dark-mode .demo-pop::after {
            background: rgba(15,23,42,.94);
            border-color: rgba(79,70,229,.25);
        }
        body.dark-mode .demo-pop-header strong { color: #e2e8f0; }
        body.dark-mode .demo-pop-header span { color: #94a3b8; }
        body.dark-mode .demo-user {
            background: linear-gradient(135deg, rgba(79,70,229,.14), rgba(14,165,233,.1));
            border-color: rgba(79,70,229,.22);
        }
        body.dark-mode .demo-user-creds { color: #cbd5e1; }
        body.dark-mode .demo-user-creds span { color: #94a3b8; }
        body.dark-mode .icon-right-state.state-valid { box-shadow: 0 6px 18px rgba(16,185,129,.45); }
        body.dark-mode .icon-right-state.state-invalid { box-shadow: 0 6px 18px rgba(239,68,68,.45); }
    </style>
</head>
<body>

<div id="loader">
    <div class="loader-inner">
        <div class="loader-logo">
            <i class="fas fa-store"></i>
        </div>
        <h5 style="font-weight:700; letter-spacing:.5px;">Supermarket</h5>
        <div class="spinner-border text-light mt-3" style="width:22px;height:22px;border-width:2px;"></div>
    </div>
</div>

<div class="bg-orb bg-orb-1"></div>
<div class="bg-orb bg-orb-2"></div>
<div class="bg-orb bg-orb-3"></div>

<div class="page-wrapper">
    <div class="login-shell">

        <div class="brand-strip">
            <div class="brand-strip-logo">
                <i class="fas fa-store"></i>
            </div>
            <div class="brand-strip-title">Supermarket</div>
            <div class="brand-strip-subtitle">Sistema De Gestion De Ventas</div>
        </div>

        <div class="form-side">
            <div class="form-top reveal-item r1">
                <button type="button" class="dark-toggle" id="darkToggleLogin" title="Alternar modo oscuro" aria-label="Modo oscuro">
                    <i class="fas fa-moon"></i>
                </button>
            </div>

            <div class="form-header reveal-item r2">
                <span class="eyebrow"><i class="fas fa-shield-halved me-1"></i> Acceso al sistema</span>
                <h2>¡Bienvenido de vuelta!</h2>
                <p class="lead">Ingresa tus credenciales para continuar con la gestión del negocio.</p>
            </div>

            <div class="datetime-strip reveal-item r3">
                <span><i class="fas fa-calendar-day me-2"></i><span id="fechaTxt"></span></span>
                <span><i class="fas fa-clock me-2"></i><strong id="horaTxt"></strong></span>
            </div>

            <div class="alert-stack reveal-item r4" id="alertStack">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;border:none;background:linear-gradient(135deg,#fee2e2,#fecaca);color:#7f1d1d;font-weight:500;font-size:.85rem;">
                        <i class="fas fa-circle-exclamation me-2"></i>
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar" style="font-size:.7rem;"></button>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                @csrf

                <div class="reveal-item r5">
                    <label for="email" class="form-label">
                        <span>Correo Electrónico</span>
                        <span style="font-weight:500;color:#94a3b8;font-size:.72rem;">Requerido</span>
                    </label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope icon-left"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="correo@ejemplo.com"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            maxlength="255">
                    </div>
                </div>

                <div class="reveal-item r6">
                    <label for="password" class="form-label">
                        <span>Contraseña</span>
                        <a href="#" class="forgot-link" style="font-size:.78rem;" onclick="event.preventDefault(); alert('Por favor, contacta al administrador del sistema para restablecer tu contraseña.');">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </label>
                    <div class="input-wrap has-toggle">
                        <i class="fas fa-lock icon-left"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            required
                            autocomplete="current-password"
                            minlength="6"
                            maxlength="255">
                        <button type="button" class="toggle-pass" id="togglePass" title="Mostrar contraseña" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                    <div class="strength-meter" id="strengthMeter">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <div class="strength-text" id="strengthText">&nbsp;</div>
                </div>

                <div class="options-row reveal-item r7">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">
                            Recordarme en este equipo
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-login reveal-item r8" id="submitBtn">
                    <span class="spinner-border spinner-border-sm text-light" role="status" aria-hidden="true"></span>
                    <span class="btn-label">
                        <i class="fas fa-right-to-bracket me-2"></i>Iniciar Sesión
                    </span>
                </button>
            </form>

            <div class="form-bottom reveal-item r8" style="animation-delay:.95s;opacity:0;transform:translateY(14px);animation:revealIn .7s cubic-bezier(.2,.9,.3,1) .95s forwards;">
                <span class="version">Versión 1.0 · Proyecto de Grado</span><br>
                <strong style="color:#475569;">Supermarket</strong> · Gestión de Ventas<br>
                © {{ date('Y') }} · Todos los derechos reservados
            </div>
        </div>
    </div>
</div>

{{-- Popover credenciales demo --}}
<div class="demo-pop" id="demoPop" role="dialog" aria-label="Credenciales de demostración">
    <div class="demo-pop-header">
        <div class="demo-pop-icon"><i class="fas fa-user-shield"></i></div>
        <div>
            <strong>Credenciales demo</strong>
            <span>Haz clic para auto-rellenar</span>
        </div>
    </div>
    <div class="demo-user" data-email="dueno@supermarket.com" data-password="password123">
        <div class="demo-user-label">
            <span>Administrador</span>
            <i class="fas fa-crown" style="color:#f59e0b;"></i>
        </div>
        <div class="demo-user-creds">
            <div><span>Email:</span>dueno@supermarket.com</div>
            <div><span>Pass:</span>password123</div>
        </div>
    </div>
    <div class="demo-user" data-email="empleado@supermarket.com" data-password="password123">
        <div class="demo-user-label">
            <span>Usuario Invitado</span>
            <i class="fas fa-user" style="color:#0ea5e9;"></i>
        </div>
        <div class="demo-user-creds">
            <div><span>Email:</span>empleado@supermarket.com</div>
            <div><span>Pass:</span>password123</div>
        </div>
    </div>
    <div style="font-size:.68rem;color:#94a3b8;text-align:center;margin-top:6px;font-weight:500;">
        Para evaluación del proyecto
    </div>
</div>

<button type="button" class="demo-fab" id="demoFab" aria-label="Credenciales de demostración" title="Ver credenciales demo">
    <i class="fas fa-key"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        function actualizarFechaHora() {
            const ahora = new Date();
            const fechaStr = ahora.toLocaleDateString('es-ES', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
            const horaStr  = ahora.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const fechaEl = document.getElementById('fechaTxt');
            const horaEl  = document.getElementById('horaTxt');
            if (fechaEl) fechaEl.textContent = fechaStr.charAt(0).toUpperCase() + fechaStr.slice(1);
            if (horaEl)  horaEl.textContent  = horaStr;
        }
        actualizarFechaHora();
        setInterval(actualizarFechaHora, 1000);

        function isEmailValid(v) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
        }
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        const toggleBtn = document.getElementById('togglePass');
        const toggleIcon = document.getElementById('toggleIcon');
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const isPass = passwordInput.type === 'password';
                passwordInput.type = isPass ? 'text' : 'password';
                toggleIcon.className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
                toggleBtn.setAttribute('title', isPass ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        }

        const meter = document.getElementById('strengthMeter');
        const fill  = document.getElementById('strengthFill');
        const text  = document.getElementById('strengthText');
        if (passwordInput && meter && fill && text) {
            passwordInput.addEventListener('input', function () {
                const val = passwordInput.value;
                if (!val) {
                    meter.classList.remove('active');
                    fill.style.width = '0';
                    text.textContent = '\u00A0';
                    text.className = 'strength-text';
                    return;
                }
                meter.classList.add('active');
                let score = 0;
                if (val.length >= 6) score++;
                if (val.length >= 10) score++;
                if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
                if (/\d/.test(val)) score++;
                if (/[^A-Za-z0-9]/.test(val)) score++;
                const level = Math.min(4, score);
                const pct = [0, 25, 50, 75, 100][level];
                const colors = ['#ef4444', '#f97316', '#f59e0b', '#84cc16', '#10b981'];
                const labels = ['Muy débil', 'Débil', 'Regular', 'Buena', 'Excelente'];
                fill.style.width = pct + '%';
                fill.style.background = colors[level];
                text.textContent = labels[level];
                text.className = 'strength-text s' + (level || 1);
            });
        }

        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const loginShell = document.querySelector('.login-shell');
        if (form && submitBtn) {
            form.addEventListener('submit', function (e) {
                const emailOk = emailInput && isEmailValid(emailInput.value);
                const passOk  = passwordInput && passwordInput.value.length >= 6;
                if (!emailOk || !passOk) {
                    e.preventDefault();
                    if (loginShell) {
                        loginShell.classList.remove('shake');
                        // reflow
                        void loginShell.offsetWidth;
                        loginShell.classList.add('shake');
                        setTimeout(() => loginShell.classList.remove('shake'), 600);
                    }
                    if (!emailOk && emailInput) { emailInput.focus(); }
                    else if (!passOk && passwordInput) { passwordInput.focus(); }
                    return;
                }
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                const label = submitBtn.querySelector('.btn-label');
                if (label) label.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i>Autenticando...';
            });
        }

        // Shake automático si hay errores de Laravel
        window.addEventListener('DOMContentLoaded', function () {
            const errAlert = document.querySelector('#alertStack .alert-danger');
            if (errAlert && loginShell) {
                setTimeout(() => {
                    loginShell.classList.remove('shake');
                    void loginShell.offsetWidth;
                    loginShell.classList.add('shake');
                    setTimeout(() => loginShell.classList.remove('shake'), 600);
                }, 200);
            }
        });

        function applyDark(enabled) {
            document.body.classList.toggle('dark-mode', !!enabled);
            const btn = document.getElementById('darkToggleLogin');
            if (btn) {
                const icon = btn.querySelector('i');
                if (icon) icon.className = enabled ? 'fas fa-sun' : 'fas fa-moon';
                btn.setAttribute('title', enabled ? 'Desactivar modo oscuro' : 'Activar modo oscuro');
            }
        }
        const darkBtn = document.getElementById('darkToggleLogin');
        if (darkBtn) {
            darkBtn.addEventListener('click', function () {
                const enabled = !document.body.classList.contains('dark-mode');
                try { localStorage.setItem('darkMode', enabled ? '1' : '0'); } catch(e) {}
                applyDark(enabled);
            });
        }
        try {
            applyDark(localStorage.getItem('darkMode') === '1');
        } catch(e) {}

        /* ══════════════════════════════════════════════
           POPOVER CREDENCIALES DEMO
        ══════════════════════════════════════════════ */
        const demoFab = document.getElementById('demoFab');
        const demoPop = document.getElementById('demoPop');
        if (demoFab && demoPop) {
            demoFab.addEventListener('click', function (e) {
                e.stopPropagation();
                demoPop.classList.toggle('show');
            });
            document.addEventListener('click', function (e) {
                if (!demoPop.classList.contains('show')) return;
                if (demoPop.contains(e.target) || demoFab.contains(e.target)) return;
                demoPop.classList.remove('show');
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') demoPop.classList.remove('show');
            });

            demoPop.querySelectorAll('.demo-user').forEach(function (card) {
                card.addEventListener('click', function () {
                    const email = card.getAttribute('data-email');
                    const pass  = card.getAttribute('data-password');
                    if (emailInput && email) {
                        emailInput.value = email;
                        const ev = new Event('input', { bubbles: true });
                        emailInput.dispatchEvent(ev);
                        emailInput.dispatchEvent(new Event('blur'));
                    }
                    if (passwordInput && pass) {
                        passwordInput.value = pass;
                        const ev = new Event('input', { bubbles: true });
                        passwordInput.dispatchEvent(ev);
                        passwordInput.dispatchEvent(new Event('blur'));
                    }
                    demoPop.classList.remove('show');
                    // Feedback visual: pulso en el botón submit
                    if (submitBtn) {
                        submitBtn.animate(
                            [
                                { transform: 'scale(1)', boxShadow: '0 10px 30px rgba(79,70,229,.3)' },
                                { transform: 'scale(1.03)', boxShadow: '0 18px 44px rgba(16,185,129,.45)', offset: .5 },
                                { transform: 'scale(1)', boxShadow: '0 10px 30px rgba(79,70,229,.3)' }
                            ],
                            { duration: 550, easing: 'cubic-bezier(.2,.9,.3,1)' }
                        );
                    }
                    setTimeout(() => {
                        if (passwordInput) passwordInput.focus();
                    }, 120);
                });
            });
        }

        window.addEventListener('load', function () {
            setTimeout(() => {
                const loader = document.getElementById('loader');
                if (loader) {
                    loader.style.opacity = '0';
                    loader.style.visibility = 'hidden';
                    setTimeout(() => loader.remove(), 500);
                }
            }, 700);
        });
    })();
</script>
</body>
</html>
