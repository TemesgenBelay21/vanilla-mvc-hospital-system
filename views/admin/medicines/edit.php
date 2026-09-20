<div class="row">
    <h1 class="page-title grow">Edit medicine</h1>
    <a class="btn" href="<?= url('/admin/medicines') ?>">&larr; Back to inventory</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/admin/medicines/' . (int) $medicine['id'] . '/update') ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Medicine name</label>
                <input type="text" id="name" name="name" value="<?= e($medicine['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" value="<?= e($medicine['category']) ?>">
            </div>
            <div class="form-group">
                <label for="unit_price">Unit price (ETB)</label>
                <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" value="<?= e((string) $medicine['unit_price']) ?>" required>
            </div>
            <div class="form-group">
                <label for="expiry_date">Expiry date</label>
                <input type="date" id="expiry_date" name="expiry_date" value="<?= e($medicine['expiry_date']) ?>">
            </div>
            <div class="form-group">
                <label for="supplier">Supplier</label>
                <input type="text" id="supplier" name="supplier" value="<?= e($medicine['supplier']) ?>">
            </div>
            <div class="form-group">
                <label for="low_stock_threshold">Low stock threshold</label>
                <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" value="<?= (int) $medicine['low_stock_threshold'] ?>" required>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Save changes</button>
    </form>

    <hr class="mt">

    <h3 class="card-title">Restock</h3>
    <p class="muted">Current stock: <strong><?= (int) $medicine['stock_quantity'] ?></strong></p>
    <form method="post" action="<?= url('/admin/medicines/' . (int) $medicine['id'] . '/restock') ?>" class="form-inline">
        <?= csrf_field() ?>
        <input type="number" name="amount" min="1" placeholder="Amount to add" required>
        <button class="btn btn-ok" type="submit">Add stock</button>
    </form>

    <hr class="mt">

    <form method="post" action="<?= url('/admin/medicines/' . (int) $medicine['id'] . '/delete') ?>"
          onsubmit="return confirm('Delete <?= e($medicine['name']) ?>?')">
        <?= csrf_field() ?>
        <button class="btn btn-danger" type="submit">Delete medicine</button>
    </form>
</div>