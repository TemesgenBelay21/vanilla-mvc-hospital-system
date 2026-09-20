<h1 class="page-title">Generate invoice</h1>
<p class="page-sub">Pick a patient to bill all their outstanding services (consultations, labs, medicines and ward charges).</p>

<div class="card">
    <form method="post" action="<?= url('/accountant/invoices/generate') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="patient_id">Patient</label>
            <select id="patient_id" name="patient_id" required>
                <option value="">— Choose a patient —</option>
                <?php foreach ($patients as $p): ?>
                    <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?> (<?= e($p['email']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="notes">Notes (optional)</label>
            <textarea id="notes" name="notes" rows="2" placeholder="Billing notes…"></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Generate invoice</button>
        <a class="btn" href="<?= url('/accountant/invoices') ?>">Cancel</a>
    </form>
</div>