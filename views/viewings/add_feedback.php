<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Добавяне на обратна връзка</h1>
        <a href="/viewings/view/<?= $viewing['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <div class="card my-4">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <strong>Имот:</strong> 
                    <a href="/properties/view/<?= $viewing['property_id'] ?>">
                        <?= htmlspecialchars($viewing['property_title']) ?>
                    </a>
                </div>
                <div class="col-md-6">
                    <strong>Клиент:</strong>
                    <a href="/clients/view/<?= $viewing['client_id'] ?>">
                        <?= htmlspecialchars($viewing['client_name']) ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Състояние на имота</label>
                        <div class="rating-group">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" 
                                           name="property_condition" id="condition_<?= $i ?>" 
                                           value="<?= $i ?>" required>
                                    <label class="form-check-label" for="condition_<?= $i ?>">
                                        <?= $i ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                            <div class="invalid-feedback">
                                Моля, оценете състоянието на имота
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Мнение за цената</label>
                        <div class="rating-group">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" 
                                           name="price_opinion" id="price_<?= $i ?>" 
                                           value="<?= $i ?>" required>
                                    <label class="form-check-label" for="price_<?= $i ?>">
                                        <?= $i ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                            <div class="invalid-feedback">
                                Моля, оценете цената
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Оценка на локацията</label>
                        <div class="rating-group">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" 
                                           name="location_rating" id="location_<?= $i ?>" 
                                           value="<?= $i ?>" required>
                                    <label class="form-check-label" for="location_<?= $i ?>">
                                        <?= $i ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                            <div class="invalid-feedback">
                                Моля, оценете локацията
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Цялостно впечатление</label>
                        <div class="rating-group">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" 
                                           name="overall_impression" id="overall_<?= $i ?>" 
                                           value="<?= $i ?>" required>
                                    <label class="form-check-label" for="overall_<?= $i ?>">
                                        <?= $i ?>
                                    </label>
                                </div>
                            <?php endfor; ?>
                            <div class="invalid-feedback">
                                Моля, дайте цялостна оценка
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label d-block">Интерес към имота</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" 
                               name="interested" id="interested_yes" 
                               value="1" required>
                        <label class="form-check-label" for="interested_yes">
                            Да, има интерес
                        </label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" 
                               name="interested" id="interested_no" 
                               value="0" required>
                        <label class="form-check-label" for="interested_no">
                            Не, няма интерес
                        </label>
                    </div>
                    <div class="invalid-feedback">
                        Моля, посочете дали има интерес
                    </div>
                </div>

                <div class="mb-4">
                    <label for="comments" class="form-label">Допълнителни коментари</label>
                    <textarea class="form-control" id="comments" name="comments" rows="3"></textarea>
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

<style>
.rating-group {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.rating-group .form-check {
    margin: 0;
}

.rating-group .form-check-input {
    margin-top: 0;
}
</style>

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