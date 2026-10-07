<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<style>
  /* Black, readable text on this page (the default grey was too faint) */
  .page-subtitle, .text-muted, .stat-card h3, .status-table .sj-table td, .gc-title { color: #111 !important; }
</style>
<div class="page-header">
  <div>
    <h1>Vehicle Management</h1>
    <p class="page-subtitle">The whole fleet at a glance — vehicles, GPS and trip tickets.</p>
  </div>
</div>

<?php
helper('facilities');
$cards = $cards ?? [];
?>
<div class="stat-carousel" id="vehStatCarousel">
  <button type="button" class="carousel-nav-btn carousel-nav-left" id="vehCarouselPrev" onclick="vehCarouselStep(-1)" aria-label="Scroll status cards left" style="display:none">
    <i class="bi bi-chevron-left"></i>
  </button>
  <button type="button" class="carousel-nav-btn carousel-nav-right" id="vehCarouselNext" onclick="vehCarouselStep(1)" aria-label="Scroll status cards right">
    <i class="bi bi-chevron-right"></i>
  </button>
  <div class="stat-carousel-track" id="vehStatCarouselTrack">
  <?php foreach ($cards as $c): ?>
    <button type="button" class="stat-card status-pick" data-key="<?= esc($c['key']) ?>" onclick="pickDash('<?= esc($c['key'], 'js') ?>')">
      <span class="stat-icon tone-<?= esc($c['tone']) ?>"><i class="bi <?= esc($c['icon']) ?>"></i></span>
      <h3><?= esc($c['label']) ?></h3>
      <div class="value"><?= count($c['rows']) ?></div>
    </button>
  <?php endforeach; ?>
</div>
</div>

<div class="status-tables" style="margin-top:16px;">
  <?php foreach ($cards as $c): ?>
    <div class="status-table guard-card" id="st-<?= esc($c['key']) ?>" style="display:none;">
      <div class="gc-title"><i class="bi <?= esc($c['icon']) ?>"></i> <?= esc($c['title']) ?></div>
      <div class="table-wrap">
        <table class="sj-table">
          <thead><tr><?php foreach ($c['columns'] as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
          <tbody>
            <?php if (empty($c['rows'])): ?>
              <tr><td colspan="<?= max(1, count($c['columns'])) ?>" class="empty-row">Nothing here right now.</td></tr>
            <?php else: foreach ($c['rows'] as $row): ?>
              <tr><?php foreach ($row as $cell): ?><?= fac_cell($cell) ?><?php endforeach; ?></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<div class="gc-title" style="margin:22px 0 12px;"><i class="bi bi-grid"></i> Go to</div>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:12px;">
  <?php foreach ([
      ['Vehicle List', 'vehicles', 'bi-truck', 'Every vehicle, its driver, fuel and inspection.'],
      ['GPS Tracker', 'gps', 'bi-geo-alt-fill', 'Live positions and recorded routes.'],
      ['Trip Ticket', 'travel', 'bi-ticket-perforated', 'Requests, approvals and trip history.'],
  ] as $l): ?>
    <a href="<?= base_url($l[1]) ?>" class="guard-card" style="display:flex;gap:12px;align-items:flex-start;padding:16px;text-decoration:none;color:inherit;">
      <i class="bi <?= $l[2] ?>" style="font-size:20px;color:var(--m);"></i>
      <div>
        <div style="font-weight:700;"><?= esc($l[0]) ?></div>
        <div class="text-muted" style="font-size:13px;"><?= esc($l[3]) ?></div>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<script>
// Status boxes in one swipeable row, like Personnel Management: arrows appear only when there is more to scroll to.
(function () {
  const track = document.getElementById('vehStatCarouselTrack');
  const prev = document.getElementById('vehCarouselPrev'), next = document.getElementById('vehCarouselNext');
  if (!track) return;
  const cards = track.querySelectorAll('.stat-card');
  let firstVisible = true, lastVisible = false;
  const apply = () => { prev.style.display = firstVisible ? 'none' : 'flex'; next.style.display = lastVisible ? 'none' : 'flex'; };
  const obs = new IntersectionObserver(es => { es.forEach(e => { if (e.target === cards[0]) firstVisible = e.isIntersecting; if (e.target === cards[cards.length - 1]) lastVisible = e.isIntersecting; }); apply(); }, { root: track, threshold: 0.95 });
  obs.observe(cards[0]); obs.observe(cards[cards.length - 1]); apply();
  window.vehCarouselStep = d => track.scrollBy({ left: d * track.clientWidth, behavior: 'smooth' });
})();
function pickDash(key) {
  const panel = document.getElementById('st-' + key);
  const wasOpen = panel.style.display !== 'none';
  document.querySelectorAll('.status-table').forEach(t => t.style.display = 'none');
  document.querySelectorAll('.status-pick').forEach(b => b.classList.toggle('active', !wasOpen && b.dataset.key === key));
  if (!wasOpen) panel.style.display = '';
}
</script>
<script src="<?= base_url('Assets/js/table-tools.js') ?>?v=<?= @filemtime(FCPATH . 'Assets/js/table-tools.js') ?>"></script>
<script>document.querySelectorAll('.status-table').forEach(attachTableTools);</script>
<?= $this->endSection() ?>
