<h1 class="page-title">Pharmacist dashboard</h1>
<p class="page-sub text-muted">Medicine inventory and prescription dispensing.</p>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Medicines', 'value' => (int) $medicines, 'tone' => 'primary']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Low stock', 'value' => (int) $lowStock, 'tone' => 'warn']) ?>
    <?= render_partial('partials/stat-card', ['label' => 'Pending dispense', 'value' => (int) $pendingCount, 'tone' => 'ok']) ?>
</div>

<div class="row mt">
    <a class="btn btn-primary" href="<?= url('/pharmacist/dispense') ?>">Dispense queue</a>
    <a class="btn" href="<?= url('/pharmacist/medicines') ?>">Inventory</a>
</div>

<?php if ($pendingCount > 0): ?>
<div class="card mt">
    <h2 class="card-title">Awaiting dispensing</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Patient</th><th>Medicine</th><th>Qty</th><th>Prescribed</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($queue as $q): ?>
                <tr>
                    <td><?= e($q['patient_name']) ?></td>
                    <td><?= e($q['medicine_name']) ?></td>
                    <td><?= (int) $q['quantity'] ?></td>
                    <td class="text-muted"><?= e(format_date($q['prescribed_at'])) ?></td>
                    <td class="text-right">
                        <a class="btn btn-sm" href="<?= url('/pharmacist/dispense/' . (int) $q['id']) ?>">Dispense</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($lowList): ?>
<div class="card mt">
    <h2 class="card-title">Stock alerts (at or below threshold)</h2>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Medicine</th><th>Stock</th><th>Low threshold</th></tr>
            </thead>
            <tbody>
                <?php foreach ($lowList as $m): ?>
                    <?php if ((int) $m['stock_quantity'] <= (int) $m['low_stock_threshold']): ?>
                    <tr>
                        <td><?= e($m['name']) ?></td>
                        <td><span class="badge badge-rejected"><?= (int) $m['stock_quantity'] ?> left</span></td>
                        <td class="text-muted"><?= (int) $m['low_stock_threshold'] ?></td>
                    </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>