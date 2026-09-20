<h1 class="page-title">Nurse dashboard</h1>
<p class="page-sub text-muted"><?= e($nurse['name']) ?></p>

<div class="stat-grid">
    <?= render_partial('partials/stat-card', ['label' => 'Assigned wards', 'value' => count($wards), 'tone' => 'primary']) ?>
</div>

<?php if ($wards): ?>
    <div class="card mt">
        <h2 class="card-title">Your wards</h2>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Ward</th><th>Beds</th><th>Occupied</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($wards as $ward): ?>
                        <tr>
                            <td><?= e($ward['name']) ?></td>
                            <td><?= (int) $ward['bed_count'] ?></td>
                            <td><?= (int) $ward['occupied_count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <div class="card mt"><p class="empty-state">You are not assigned to any wards yet.</p></div>
<?php endif; ?>