<?php
// Shared row template — included by tools/index.php on first load AND
// rendered standalone by ToolsController::refreshData() for the page's
// polling script. One place for the badge/stock logic instead of a second,
// hand-kept-in-sync copy in JS. Expects $toolList and $isConsumablePage,
// same as the parent view.
?>
<?php if (!empty($toolList)): ?>
  <?php foreach ($toolList as $t): ?>
    <tr>
      <td class="tool-name-cell"><?= esc($t['asset_name']) ?></td>
      <td><?= esc($t['asset_code']) ?></td>
      <td><?php helper('facilities'); ?><?= esc(tool_cat_label($t['category'])) ?></td>
      <td><?= esc($t['location']) ?></td>
      <td><?= esc($t['custodian_name'] ?? 'Unassigned') ?></td>
      <td><span class="status-badge status-<?= strtolower($t['condition_status']) ?>"><?= esc($t['condition_status']) ?></span></td>
      <td><span class="status-badge status-<?= strtolower($t['availability']) ?>"><?= esc($t['availability']) ?></span></td>
      <td><?= (($t['availability'] ?? '') === 'Borrowed') ? esc($t['borrower_name'] ?? 'Not on record') : '—' ?></td>
      <?php if ($isConsumablePage): ?>
        <?php
          $stockQty = (float) ($t['current_stock'] ?? 0);
          $reorderLevel = (float) ($t['reorder_threshold'] ?? 0);
          $unitLabel = $t['unit'] ?? 'pcs';
          $stockBadge = $stockQty <= 0 ? ['inv-out', 'Out of Stock'] : ($stockQty <= $reorderLevel ? ['inv-low', 'Low Stock'] : ['inv-ok', 'Full Stock']);
        ?>
        <td>
          <strong><?= esc((string) $stockQty) ?> <?= esc($unitLabel) ?></strong>
          <span class="inv-badge <?= $stockBadge[0] ?>"><?= $stockBadge[1] ?></span>
        </td>
      <?php endif; ?>
      <td>
        <div class="action-buttons">
          <button type="button" class="icon-btn" onclick="openToolDetail(<?= (int) $t['id'] ?>)" title="View Details" aria-label="View details for <?= esc($t['asset_name']) ?>"><i class="bi bi-eye-fill"></i></button>
          <button type="button" class="icon-btn" onclick="document.getElementById('editModal<?= $t['id'] ?>')?.style.setProperty('display','flex')" title="Edit" aria-label="Edit <?= esc($t['asset_name']) ?>"><i class="bi bi-pencil-fill"></i></button>
          <?php if ($isConsumablePage): ?>
            <button type="button" class="icon-btn" title="Refill" aria-label="Refill <?= esc($t['asset_name']) ?>" onclick="refillToolStock(<?= (int) $t['id'] ?>, '<?= esc($t['asset_name'], 'js') ?>', '<?= esc($t['unit'] ?? 'pcs', 'js') ?>')"><i class="bi bi-upload"></i></button>
          <?php else: ?>
            <form method="post" action="<?= base_url('tools/delete/'.$t['id']) ?>" onsubmit="return confirm('Archive this tool?')" style="display:contents;">
              <?= csrf_field() ?>
              <button type="submit" class="icon-btn delete" title="Archive" aria-label="Archive <?= esc($t['asset_name']) ?>"><i class="bi bi-archive-fill"></i></button>
            </form>
          <?php endif; ?>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr><td colspan="<?= $isConsumablePage ? 10 : 9 ?>">No assets recorded yet.</td></tr>
<?php endif; ?>
