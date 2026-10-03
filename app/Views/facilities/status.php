<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1>Facilities Status</h1>
    <p class="page-subtitle">Click a box to see the records behind it.</p>
  </div>
</div>

<div class="stat-carousel" id="statusCarousel">
  <button type="button" class="carousel-nav-btn carousel-nav-left" id="statusCarouselPrev" onclick="statusCarouselStep(-1)" aria-label="Scroll status boxes left" style="display:none">
    <i class="bi bi-chevron-left"></i>
  </button>
  <button type="button" class="carousel-nav-btn carousel-nav-right" id="statusCarouselNext" onclick="statusCarouselStep(1)" aria-label="Scroll status boxes right">
    <i class="bi bi-chevron-right"></i>
  </button>
  <div class="stat-carousel-track" id="statusCarouselTrack">
    <?php foreach ($cards as $i => $c): ?>
      <button type="button" class="stat-card status-pick<?= $i === 0 ? ' active' : '' ?>" data-key="<?= esc($c['key']) ?>" onclick="pickStatusCard('<?= esc($c['key'], 'js') ?>')">
        <span class="stat-icon tone-<?= esc($c['tone']) ?>"><i class="bi <?= esc($c['icon']) ?>"></i></span>
        <h3><?= esc($c['label']) ?></h3>
        <div class="value"><?= esc((string) $c['value']) ?></div>
      </button>
    <?php endforeach; ?>
  </div>
</div>

<div class="status-tables">
  <?php foreach ($cards as $i => $c): ?>
    <div class="status-table guard-card" id="st-<?= esc($c['key']) ?>" style="<?= $i === 0 ? '' : 'display:none;' ?>">
      <div class="gc-title"><i class="bi <?= esc($c['icon']) ?>"></i> <?= esc($c['title']) ?></div>
      <div class="table-wrap">
        <table class="sj-table">
          <thead><tr><?php foreach ($c['columns'] as $col): ?><th><?= esc($col) ?></th><?php endforeach; ?></tr></thead>
          <tbody>
            <?php if (empty($c['rows'])): ?>
              <tr><td colspan="<?= count($c['columns']) ?>" class="empty-row">Nothing here right now.</td></tr>
            <?php else: foreach ($c['rows'] as $row): ?>
              <?php helper('facilities'); ?>
              <tr><?php foreach ($row as $cell): ?><?= fac_cell($cell) ?><?php endforeach; ?></tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
function pickStatusCard(key) {
  document.querySelectorAll('.status-pick').forEach(b => b.classList.toggle('active', b.dataset.key === key));
  document.querySelectorAll('.status-table').forEach(t => t.style.display = t.id === 'st-' + key ? '' : 'none');
}
function setupStatusCarousel() {
  const track = document.getElementById('statusCarouselTrack');
  const prevBtn = document.getElementById('statusCarouselPrev');
  const nextBtn = document.getElementById('statusCarouselNext');
  if (!track || !prevBtn || !nextBtn) return;
  const cards = Array.from(track.querySelectorAll('.stat-card'));
  if (!cards.length) return;
  const firstCard = cards[0];
  const lastCard = cards[cards.length - 1];
  let firstVisible = true;
  let lastVisible = false;
  function applyArrowState() {
    prevBtn.style.display = firstVisible ? 'none' : 'flex';
    nextBtn.style.display = lastVisible ? 'none' : 'flex';
  }
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.target === firstCard) firstVisible = entry.isIntersecting;
      if (entry.target === lastCard) lastVisible = entry.isIntersecting;
    });
    applyArrowState();
  }, { root: track, threshold: 0.95 });
  observer.observe(firstCard);
  observer.observe(lastCard);
  applyArrowState();
  window.statusCarouselStep = function (direction) {
    track.scrollBy({ left: direction * track.clientWidth, behavior: 'smooth' });
  };
}
document.addEventListener('DOMContentLoaded', setupStatusCarousel);
</script>
<?= $this->endSection() ?>
