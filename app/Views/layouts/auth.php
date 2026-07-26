<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
