<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Нов оглед</h1>
        <a href="/viewings" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mt-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card my-4">
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="property_id" class="form-label">Имот *</label>
                            <select class="form-select" id="property_id" name="property_id" required>
                                <option value="">-- Изберете --</option>
                                <?php foreach ($properties as $property): ?>
                                    <option value="<?= $property['id'] ?>">
                                        <?= htmlspecialchars($property['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Моля, изберете имот
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="client_id" class="form-label">Клиент *</label>
                            <select class="form-select" id="client_id" name="client_id" required>
                                <option value="">-- Изберете --</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?= $client['id'] ?>">
                                        <?= htmlspecialchars($client['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Моля, изберете клиент
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="agent_id" class="form-label">Агент *</label>
                            <select class="form-select" id="agent_id" name="agent_id" required>
                                <option value="">-- Изберете --</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?= $agent['id'] ?>">
                                        <?= htmlspecialchars($agent['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                Моля, изберете агент
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="scheduled_at" class="form-label">Дата и час *</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" 
                                   name="scheduled_at" required>
                            <div class="invalid-feedback">
                                Моля, изберете дата и час
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Запази
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