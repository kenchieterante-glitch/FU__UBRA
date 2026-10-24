<?php
// Rows of the Borrowing table. Rendered inline by tools/borrowing and, on its own,
// by ToolsController::borrowingRefresh() so the page can refresh without a reload.
$recordList = $recordList ?? ($records ?? []);
?>
<?php if (!empty($recordList)): ?>
        <?php foreach ($recordList as $r): ?>
          <?php
            $computedStatus = $r['computed_status'] ?? $r['status'];
            $badgeClass = match ($computedStatus) {
                'Borrowed'          => 'badge blue',
                'Overdue'           => 'badge red',
                'Pending approval'  => 'badge amber',
                'Returned'          => 'badge green',
                default             => 'badge amber',
            };
            $rowClass = $computedStatus === 'Overdue' ? 'row-overdue' : '';
          ?>
          <tr class="borrow-row <?= $rowClass ?>" data-id="<?= (int) $r['id'] ?>" data-status="<?= esc($computedStatus) ?>" data-borrowed="<?= esc((string) ($r['borrowed_date'] ?? '')) ?>" data-due="<?= esc((string) ($r['expected_return'] ?? '')) ?>" data-name="<?= esc($r['asset_name'] ?? '') ?>" data-category="<?= esc($r['category'] ?? '') ?>">
            <td class="tool-name-cell"><?= esc($r['asset_name'] ?? 'Unknown tool') ?></td>
            <td><?= esc($r['asset_code'] ?? '—') ?></td>
            <td><?= esc($r['borrower'] ?? 'Not on record') ?></td>
            <td><?= !empty($r['borrowed_date']) ? esc(date('M j, Y', strtotime($r['borrowed_date']))) : '—' ?></td>
            <td><?= !empty($r['expected_return']) ? esc(date('M j, Y', strtotime($r['expected_return']))) : '—' ?></td>
            <td><?= esc((string) (float) ($r['quantity'] ?? 1)) ?></td>
            <td><span class="status-badge <?= $badgeClass ?>"><?= esc($computedStatus) ?></span></td>
            <td>
              <div class="action-buttons">
                <button type="button" class="icon-btn" onclick="openBorrowDetail(<?= (int) $r['id'] ?>)" title="View Details" aria-label="View borrow details for <?= esc($r['asset_name'] ?? 'tool') ?>"><i class="bi bi-eye-fill"></i></button>
                <?php if ($r['status'] === 'Borrowed' && ($r['category'] ?? null) !== 'Consumable'): ?>
                  <form id="returnForm<?= $r['id'] ?>" method="post" action="<?= base_url('tools/returnTool/' . $r['tool_id']) ?>" style="display:contents;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="quantity" id="returnQtyInput<?= $r['id'] ?>" value="<?= esc((string) (float) ($r['outstanding_qty'] ?? $r['quantity'] ?? 1)) ?>">
                    <button type="button" class="icon-btn" onclick="confirmReturnTool(<?= (int) $r['id'] ?>, '<?= esc($r['asset_name'] ?? 'this tool', 'js') ?>')" title="Mark Returned" aria-label="Mark <?= esc($r['asset_name'] ?? 'tool') ?> as returned"><i class="bi bi-arrow-counterclockwise"></i></button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8">No borrowing records yet.</td></tr>
      <?php endif; ?>
