<div class="row">
    <h1 class="page-title grow">Request lab test</h1>
    <a class="btn" href="<?= url('/doctor/patients/' . (int) $patient['id'] . '/profile') ?>">&larr; Back to <?= e($patient['name']) ?></a>
</div>

<div class="card mt">
    <form method="post" action="<?= url('/doctor/patients/' . (int) $patient['id'] . '/lab/store') ?>">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="test_name">Test name</label>
            <input type="text" id="test_name" name="test_name" list="lab-tests"
                   placeholder="e.g. Complete blood count" required>
            <datalist id="lab-tests">
                <option value="Complete blood count">
                <option value="Malaria smear">
                <option value="Urinalysis">
                <option value="Blood glucose">
                <option value="Liver function test">
                <option value="Kidney function test">
                <option value="Lipid panel">
                <option value="Malaria antigen">
                <option value="Typhoid test">
                <option value="COVID-19 antigen">
                <option value="Pregnancy test">
                <option value="Sputum AFB">
            </datalist>
        </div>

        <div class="form-group">
            <label for="priority">Priority</label>
            <select id="priority" name="priority">
                <option value="normal">Normal</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>

        <div class="form-group">
            <label for="notes">Clinical notes <span class="optional">(optional)</span></label>
            <textarea id="notes" name="notes" rows="3" placeholder="What the result should confirm / exclude…"></textarea>
        </div>

        <div class="row">
            <button class="btn btn-primary" type="submit">Send request</button>
            <a class="btn" href="<?= url('/doctor/patients/' . (int) $patient['id'] . '/profile') ?>">Cancel</a>
        </div>
    </form>
</div>