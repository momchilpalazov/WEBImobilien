<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Пренасрочване на оглед</h1>
        <a href="/viewings/view/<?= $viewing['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mt-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card my-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-4">
                    <strong>Имот:</strong> 
                    <a href="/properties/view/<?= $viewing['property_id'] ?>">
                        <?= htmlspecialchars($viewing['property_title']) ?>
                    </a>
                </div>
                <div class="col-md-4">
                    <strong>Клиент:</strong>
                    <a href="/clients/view/<?= $viewing['client_id'] ?>">
                        <?= htmlspecialchars($viewing['client_name']) ?>
                    </a>
                </div>
                <div class="col-md-4">
                    <strong>Агент:</strong>
                    <?= htmlspecialchars($viewing['agent_name']) ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Текуща дата и час</label>
                            <input type="text" class="form-control" 
                                   value="<?= date('d.m.Y H:i', strtotime($viewing['scheduled_at'])) ?>" 
                                   disabled>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="scheduled_at" class="form-label">Нова дата и час *</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" 
                                   name="scheduled_at" required>
                            <div class="invalid-feedback">
                                Моля, изберете нова дата и час
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-clock"></i> Пренасрочи
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'

    var forms = document.querySelectorAll('.needs-validation')

    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
})()

// Set min datetime to now
document.getElementById('scheduled_at').min = new Date().toISOString().slice(0, 16);
</script>

<?php require_once 'views/layout/footer.php'; ?> 