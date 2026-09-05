<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Prevent the login page from being cached by the browser.
         This ensures that when a logged-in user clicks the browser's
         back button, the page is re-requested from the server (which
         checks the session and redirects to the dashboard) instead of
         showing a stale cached login page. -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>UBRA | <?= esc($title ?? 'Login') ?></title>
    <link rel="stylesheet" href="<?= base_url('fonts/bebas-neue/bebas-neue.css') ?>">
    <link rel="stylesheet" href="<?= base_url('icons/bootstrap-icons/bootstrap-icons.css') ?>">
    <style>
        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            margin: 0;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-weight: 400;
            color: #333;
            background-color: #fff;
        }

        .auth-shell {
            height: 100vh;
            display: flex;
            position: relative;
        }

        .auth-form-panel {
            flex: 1 1 50%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            z-index: 2;
            background: linear-gradient(160deg, #8a1010 0%, #5c0000 100%);
            overflow-y: auto;
            box-sizing: border-box;
        }

        .auth-image-panel {
            flex: 1 1 50%;
            position: relative;
            overflow: hidden;
            background-color: #181818;
        }

        /* Upscaled from the original 480x360 source (Lanczos + unsharp
           mask) — sharper than a raw browser stretch, but still an
           upscale, not genuine 4K detail. Swap in a real high-res original
           here if one becomes available. */
        .auth-image-panel img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
            padding: 40px 36px;
            box-sizing: border-box;
        }

        @media (max-width: 900px) {
            .auth-shell {
                flex-direction: column;
            }

            .auth-image-panel {
                display: none;
            }

            .auth-form-panel {
                flex: 1 1 100%;
            }
        }

        .auth-card h1 {
            font-family: "Bebas Neue Pro", "Bebas Neue", "Arial Narrow", sans-serif;
            font-weight: 700;
            letter-spacing: 4px;
            color: #800000;
        }

        .auth-card p {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-weight: 500;
            color: #666;
        }

        .auth-card label {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-weight: 400;
        }
    </style>
</head>
<body>
    <div class="auth-shell">
        <div class="auth-form-panel">
            <div class="auth-card">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
        <div class="auth-image-panel">
            <img src="<?= base_url('images/' . rawurlencode('BnG background 4k.jpg')) ?>" alt="">
        </div>
    </div>
</body>
</html>
