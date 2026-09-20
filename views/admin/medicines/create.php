<div class="row">
    <h1 class="page-title grow">New medicine</h1>
    <a class="btn" href="<?= url('/admin/medicines') ?>">&larr; Back to inventory</a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/admin/medicines/store') ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="form-group">
                <label for="name">Medicine name</label>
                <input type="text" id="name" name="name" value="<?= e(old('name')) ?>" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" value="<?= e(old('category')) ?>" placeholder="Antibiotic">
            </div>
            <div class="form-group">
                <label for="stock_quantity">Initial stock</label>
                <input type="number" id="stock_quantity" name="stock_quantity" min="0" value="<?= e(old('stock_quantity', '0')) ?>" required>
            </div>
            <div class="form-group">
                <label for="unit_price">Unit price (ETB)</label>
                <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" value="<?= e(old('unit_price', '0')) ?>" required>
            </div>
            <div class="form-group">
                <label for="expiry_date">Expiry date</label>
                <input type="date" id="expiry_date" name="expiry_date" value="<?= e(old('expiry_date')) ?>">
            </div>
            <div class="form-group">
                <label for="supplier">Supplier</label>
                <input type="text" id="supplier" name="supplier" value="<?= e(old('supplier')) ?>">
            </div>
            <div class="form-group">
                <label for="low_stock_threshold">Low stock threshold</label>
                <input type="number" id="low_stock_threshold" name="low_stock_threshold" min="0" value="<?= e(old('low_stock_threshold', '10')) ?>" required>
            </div>
        </div>
        <button class="btn btn-ok btn-primary" type="submit">Add medicine</button>
    </form>
</div>