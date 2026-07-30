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
    <title>FU_UBRA | <?= esc($title ?? 'Login') ?></title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #f7f1f1 0%, #f0e2e2 100%);
            color: #333;
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
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
            padding: 30px;
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
