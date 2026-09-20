<div class="row">
    <h1 class="page-title grow">Pharmacy inventory</h1>
    <a class="btn btn-ok" href="<?= url('/admin/medicines/create') ?>">+ New medicine</a>
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
                <th>Supplier</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
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
                <td><?= e($m['supplier'] ?: '—') ?></td>
                <td>
                    <?php if ((int) $m['stock_quantity'] === 0): ?>
                        <span class="badge badge-inactive">Out of stock</span>
                    <?php elseif ((int) $m['low_stock']): ?>
                        <span class="badge badge-rejected">Low stock</span>
                    <?php else: ?>
                        <span class="badge badge-completed">In stock</span>
                    <?php endif; ?>
                </td>
                <td class="text-right">
                    <a class="btn btn-sm" href="<?= url('/admin/medicines/' . (int) $m['id'] . '/edit') ?>">Edit</a>
                    <form method="post" action="<?= url('/admin/medicines/' . (int) $m['id'] . '/delete') ?>" class="inline"
                          onsubmit="return confirm('Delete <?= e($m['name']) ?>?')">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>