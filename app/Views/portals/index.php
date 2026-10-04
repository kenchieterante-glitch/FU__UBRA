<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UBRA — Choose Your Department Portal</title>
  <link rel="icon" href="<?= base_url('images/' . rawurlencode('UBRA LOGO (cropped).png')) ?>">
  <link rel="stylesheet" href="<?= base_url('fonts/bebas-neue/bebas-neue.css') ?>">
  <link rel="stylesheet" href="<?= base_url('icons/bootstrap-icons/bootstrap-icons.css') ?>">
  <link rel="stylesheet" href="<?= base_url('Assets/css/base.css') ?>">
  <link rel="stylesheet" href="<?= base_url('Assets/css/portals.css') ?>?v=<?= @filemtime(FCPATH . 'Assets/css/portals.css') ?>">
</head>
<body class="portal-lobby">
  <header class="portal-lobby-top">
    <img src="<?= base_url('images/' . rawurlencode('UBRA LOGO (cropped).png')) ?>" alt="UBRA logo">
    <div class="portal-lobby-title"><span class="portal-lobby-sub">Foundation University</span><span class="portal-lobby-brand">UBRA</span></div>
  </header>

  <main class="portal-hub">
    <div class="portal-grid">
      <?php foreach ($portals as $key => $p): ?>
        <div class="portal-card">
          <span class="portal-icon"><i class="bi <?= esc($p['icon'] ?? 'bi-building') ?>"></i></span>
          <h2><?= esc($p['short']) ?></h2>
          <p class="portal-desc"><?= esc($p['description']) ?></p>
          <div class="portal-covers">Covers: <strong><?= esc($p['covers']) ?></strong></div>
          <a class="portal-btn" href="<?= base_url('portals/' . $key) ?>">Sign in</a>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <footer class="portal-lobby-foot">© <?= date('Y') ?> UBRA — Foundation University Buildings &amp; Grounds</footer>
</body>
</html>
