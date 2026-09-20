<div class="row">
    <h1 class="page-title grow">New prescription</h1>
    <a class="btn" href="<?= url('/doctor/patients/' . (int) $patient['id'] . '/profile') ?>">&larr; Back to <?= e($patient['name']) ?></a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/doctor/patients/' . (int) $patient['id'] . '/prescriptions/store') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="medicine_id">Medicine <span class="optional">(from pharmacy inventory)</span></label>
            <select id="medicine_id" name="medicine_id" required>
                <option value="">— Select medicine —</option>
                <?php foreach ($medicines as $m): ?>
                    <option value="<?= (int) $m['id'] ?>" <?= (int) old('medicine_id') === (int) $m['id'] ? 'selected' : '' ?>>
                        <?= e($m['name']) ?> (stock <?= (int) $m['stock_quantity'] ?><?= (int) $m['low_stock'] ? ', low' : '' ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="dosage">Dosage</label>
                <input type="text" id="dosage" name="dosage" value="<?= e(old('dosage')) ?>" placeholder="500mg" required>
            </div>
            <div class="form-group">
                <label for="frequency">Frequency</label>
                <input type="text" id="frequency" name="frequency" value="<?= e(old('frequency')) ?>" placeholder="Twice daily" required>
            </div>
            <div class="form-group">
                <label for="duration">Duration</label>
                <input type="text" id="duration" name="duration" value="<?= e(old('duration')) ?>" placeholder="5 days" required>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" name="quantity" value="<?= e(old('quantity', '1')) ?>" min="1" required>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes <span class="optional">(optional)</span></label>
            <textarea id="notes" name="notes" rows="3" placeholder="Advice to the patient, administration clarifications…"><?= e(old('notes')) ?></textarea>
        </div>

        <div class="row">
            <button class="btn btn-primary" type="submit">Save prescription</button>
            <a class="btn" href="<?= url('/doctor/patients/' . (int) $patient['id'] . '/profile') ?>">Cancel</a>
        </div>
    </form>
</div>