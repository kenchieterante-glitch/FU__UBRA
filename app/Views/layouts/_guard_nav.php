<?php $isGuardSection = $currentUri === 'security-dept/keys'; /* the Guard Monitoring label page itself keeps the dropdown closed */ ?>
<div class="nav-parent-group <?= $isGuardSection ? 'open' : '' ?>">
  <a href="<?= base_url('security-dept/guard') ?>" class="nav-parent-link <?= $isGuardSection ? 'open' : '' ?> <?= $currentUri === 'security-dept/guard' ? 'active' : '' ?>" data-guard-toggle data-tooltip="Guard Monitoring">
    <i class="bi bi-shield-check"></i>
    <span class="nav-label">Guard Monitoring</span>
    <i class="bi bi-chevron-down nav-parent-caret"></i>
  </a>
  <div class="nav-submenu" id="guard-submenu">
    <a href="<?= base_url('security-dept/keys') ?>" class="<?= navActive('security-dept/keys') ?>"><i class="bi bi-list-ul"></i> <span class="nav-label">List of Keys</span></a>
  </div>
</div>
