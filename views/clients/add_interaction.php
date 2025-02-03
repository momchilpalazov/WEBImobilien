<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Ново взаимодействие</h1>
        <a href="/clients/details/<?= $client['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <div class="card my-4">
        <div class="card-header">
            <i class="fas fa-user me-1"></i>
            Клиент: <?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="interaction_type" class="form-label">Тип взаимодействие *</label>
                            <select class="form-select" id="interaction_type" name="interaction_type" required>
                                <option value="">-- Изберете --</option>
                                <option value="call">Обаждане</option>
                                <option value="email">Имейл</option>
                                <option value="meeting">Среща</option>
                                <option value="viewing">Оглед</option>
                                <option value="offer">Оферта</option>
                                <option value="other">Друго</option>
                            </select>
                            <div class="invalid-feedback">
                                Моля, изберете тип взаимодействие
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="scheduled_at" class="form-label">Дата и час</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at">
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Описание *</label>
                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    <div class="invalid-feedback">
                        Моля, въведете описание
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="property_id" class="form-label">Свързан имот</label>
                            <select class="form-select" id="property_id" name="property_id">
                                <option value="">-- Изберете --</option>
                                <?php foreach ($available_properties as $property): ?>
                                    <option value="<?= $property['id'] ?>">
                                        <?= htmlspecialchars($property['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select class="form-select" id="status" name="status">
                                <option value="planned">Планирано</option>
                                <option value="completed">Завършено</option>
                                <option value="cancelled">Отказано</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Бележки</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
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
</script>

<?php require_once 'views/layout/footer.php'; ?> 