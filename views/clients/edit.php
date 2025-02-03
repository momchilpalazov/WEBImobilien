<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Редактиране на клиент</h1>
        <div>
            <a href="/clients/details/<?= $client['id'] ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Назад
            </a>
        </div>
    </div>

    <div class="card my-4">
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <!-- Основна информация -->
                <h5 class="mb-3">Основна информация</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="first_name" class="form-label">Име *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($client['first_name']) ?>" required>
                            <div class="invalid-feedback">
                                Моля, въведете име
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="last_name" class="form-label">Фамилия *</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($client['last_name']) ?>" required>
                            <div class="invalid-feedback">
                                Моля, въведете фамилия
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= htmlspecialchars($client['email'] ?? '') ?>">
                            <div class="invalid-feedback">
                                Моля, въведете валиден email адрес
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="phone" class="form-label">Телефон</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select class="form-select" id="status" name="status">
                                <option value="potential" <?= $client['status'] === 'potential' ? 'selected' : '' ?>>
                                    Потенциален
                                </option>
                                <option value="active" <?= $client['status'] === 'active' ? 'selected' : '' ?>>
                                    Активен
                                </option>
                                <option value="inactive" <?= $client['status'] === 'inactive' ? 'selected' : '' ?>>
                                    Неактивен
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="source" class="form-label">Източник</label>
                            <input type="text" class="form-control" id="source" name="source" 
                                   value="<?= htmlspecialchars($client['source'] ?? '') ?>"
                                   placeholder="Например: Реклама, Препоръка и т.н.">
                        </div>
                    </div>
                </div>

                <!-- Предпочитания -->
                <h5 class="mb-3">Предпочитания за имот</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="property_type" class="form-label">Тип имот</label>
                            <select class="form-select" id="property_type" name="preferences[property_type]">
                                <option value="">-- Изберете --</option>
                                <option value="apartment" <?= ($preferences['property_type'] ?? '') === 'apartment' ? 'selected' : '' ?>>
                                    Апартамент
                                </option>
                                <option value="house" <?= ($preferences['property_type'] ?? '') === 'house' ? 'selected' : '' ?>>
                                    Къща
                                </option>
                                <option value="office" <?= ($preferences['property_type'] ?? '') === 'office' ? 'selected' : '' ?>>
                                    Офис
                                </option>
                                <option value="land" <?= ($preferences['property_type'] ?? '') === 'land' ? 'selected' : '' ?>>
                                    Парцел
                                </option>
                                <option value="commercial" <?= ($preferences['property_type'] ?? '') === 'commercial' ? 'selected' : '' ?>>
                                    Търговски обект
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="location" class="form-label">Предпочитана локация</label>
                            <input type="text" class="form-control" id="location" name="preferences[location]" 
                                   value="<?= htmlspecialchars($preferences['location'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Ценови диапазон (€)</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" name="preferences[min_price]" 
                                           placeholder="От" min="0" step="1000"
                                           value="<?= $preferences['min_price'] ?? '' ?>">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="preferences[max_price]" 
                                           placeholder="До" min="0" step="1000"
                                           value="<?= $preferences['max_price'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Площ (м²)</label>
                            <div class="row">
                                <div class="col">
                                    <input type="number" class="form-control" name="preferences[min_area]" 
                                           placeholder="От" min="0"
                                           value="<?= $preferences['min_area'] ?? '' ?>">
                                </div>
                                <div class="col">
                                    <input type="number" class="form-control" name="preferences[max_area]" 
                                           placeholder="До" min="0"
                                           value="<?= $preferences['max_area'] ?? '' ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="bedrooms" class="form-label">Брой спални</label>
                            <input type="number" class="form-control" id="bedrooms" 
                                   name="preferences[bedrooms]" min="0"
                                   value="<?= $preferences['bedrooms'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="bathrooms" class="form-label">Брой бани</label>
                            <input type="number" class="form-control" id="bathrooms" 
                                   name="preferences[bathrooms]" min="0"
                                   value="<?= $preferences['bathrooms'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="mb-3">
                            <label for="additional_features" class="form-label">Допълнителни изисквания</label>
                            <textarea class="form-control" id="additional_features" 
                                      name="preferences[additional_features]" rows="3"><?= htmlspecialchars($preferences['additional_features'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Запази промените
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