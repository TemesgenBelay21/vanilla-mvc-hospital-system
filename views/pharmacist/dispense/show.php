<div class="row">
    <h1 class="page-title grow">Dispense prescription #<?= (int) $prescription['id'] ?></h1>
    <a class="btn" href="<?= url('/pharmacist/dispense') ?>">&larr; Back to queue</a>
</div>

<div class="grid mt">
    <div class="card">
        <h3 class="card-title">Patient</h3>
        <strong><?= e($prescription['patient_name']) ?></strong>
        <p class="muted">
            <?= e($prescription['phone']) ?><br>
            <?= $prescription['date_of_birth'] ? 'DOB ' . e(format_date($prescription['date_of_birth'])) : '' ?>
        </p>
    </div>
    <div class="card">
        <h3 class="card-title">Prescription</h3>
        <p>
            <strong><?= e($prescription['medicine_name']) ?></strong><br>
            <?= e($prescription['dosage']) ?> — <?= e($prescription['frequency']) ?> for <?= e($prescription['duration']) ?><br>
            Quantity <?= (int) $prescription['quantity'] ?> &middot; <?= number_format((float) $prescription['unit_price'], 2) ?> ETB/unit<br>
            <span class="muted">By Dr. <?= e($prescription['doctor_name']) ?> on <?= e(format_date($prescription['prescribed_at'])) ?></span>
        </p>
        <?php if ($prescription['notes']): ?>
            <p class="muted"><strong>Notes:</strong> <?= e($prescription['notes']) ?></p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h3 class="card-title">Stock check</h3>
        <p>
            Available in pharmacy: <strong><?= (int) $prescription['stock_quantity'] ?></strong><br>
            Required: <strong><?= (int) $prescription['quantity'] ?></strong>
        </p>
        <?php if ((int) $prescription['stock_quantity'] < (int) $prescription['quantity']): ?>
            <p class="badge badge-rejected">Insufficient stock — restock before dispensing.</p>
        <?php else: ?>
            <p class="badge badge-completed">Sufficient stock.</p>
        <?php endif; ?>
    </div>
</div>

<?php if ((int) $prescription['stock_quantity'] >= (int) $prescription['quantity']): ?>
<div class="card mt">
    <h3 class="card-title">Confirm dispense</h3>
    <p class="muted">Dispensing deducts <?= (int) $prescription['quantity'] ?> units from stock and marks the prescription dispensed.</p>
    <form method="post" action="<?= url('/pharmacist/prescriptions/' . (int) $prescription['id'] . '/dispense') ?>"
          onsubmit="return confirm('Dispense <?= (int) $prescription['quantity'] ?> x <?= e($prescription['medicine_name']) ?> to <?= e($prescription['patient_name']) ?>?')">
        <?= csrf_field() ?>
        <button class="btn btn-primary" type="submit">Dispense prescription</button>
        <a class="btn" href="<?= url('/pharmacist/dispense') ?>">Back to queue</a>
    </form>
</div>
<?php endif; ?>