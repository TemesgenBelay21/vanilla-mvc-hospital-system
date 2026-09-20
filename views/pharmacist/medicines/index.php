<div class="row">
    <h1 class="page-title grow">Pharmacy inventory</h1>
</div>

<div class="table-wrap mt">
    <table class="table">
        <thead>
            <tr>
                <th>Medicine</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Unit price</th>
                <th>Expiry</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($medicines as $m): ?>
            <tr>
                <td><strong><?= e($m['name']) ?></strong></td>
                <td><?= e($m['category'] ?: '—') ?></td>
                <td><?= (int) $m['stock_quantity'] ?></td>
                <td><?= number_format((float) $m['unit_price'], 2) ?> ETB</td>
                <td class="text-muted"><?= e($m['expiry_date'] ?? '—') ?></td>
                <td>
                    <?php if ((int) $m['stock_quantity'] === 0): ?>
                        <span class="badge badge-inactive">Out of stock</span>
                    <?php elseif ((int) $m['low_stock']): ?>
                        <span class="badge badge-rejected">Low stock</span>
                    <?php else: ?>
                        <span class="badge badge-completed">In stock</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>