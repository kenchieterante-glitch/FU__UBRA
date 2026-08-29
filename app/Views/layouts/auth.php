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
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-weight: 400;
            color: #333;
            background-color: #2a1414;
            background-image:
                linear-gradient(135deg, rgba(40, 10, 10, .55) 0%, rgba(20, 5, 5, .6) 100%),
                url('<?= base_url('images/' . rawurlencode('BnG background.jpg')) ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
            padding: 30px;
        }

        .auth-card h1 {
            font-family: "Bebas Neue Pro", "Bebas Neue", "Arial Narrow", sans-serif;
            font-weight: 700;
            letter-spacing: 1px;
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
        <div class="auth-card">
            <?= $this->renderSection('content') ?>
        </div>
    </div>
</body>
</html>
