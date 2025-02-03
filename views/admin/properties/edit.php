<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= $translations['edit'] ?></h1>
    </div>

    <?php require_once __DIR__ . '/form.php'; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 