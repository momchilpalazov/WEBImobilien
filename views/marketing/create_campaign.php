<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Нова маркетингова кампания</h1>
        <a href="/marketing/campaigns" class="btn btn-outline-secondary">
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
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Заглавие *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                            <div class="invalid-feedback">
                                Моля, въведете заглавие
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="status" class="form-label">Статус *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="draft">Чернова</option>
                                <option value="active">Активна</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="budget" class="form-label">Бюджет</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="budget" name="budget" 
                                       step="0.01" min="0">
                                <span class="input-group-text">лв.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Начална дата *</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                            <div class="invalid-feedback">
                                Моля, изберете начална дата
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="end_date" class="form-label">Крайна дата</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Имоти *</label>
                    <div class="row g-3">
                        <?php foreach ($properties as $property): ?>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="property_ids[]" 
                                           value="<?= $property['id'] ?>" 
                                           id="property_<?= $property['id'] ?>">
                                    <label class="form-check-label" for="property_<?= $property['id'] ?>">
                                        <?= htmlspecialchars($property['title']) ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="invalid-feedback">
                        Моля, изберете поне един имот
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Маркетингови канали</label>
                    <div id="channels">
                        <!-- Тук ще се добавят динамично каналите -->
                    </div>
                    <button type="button" class="btn btn-outline-primary" onclick="addChannel()">
                        <i class="fas fa-plus"></i> Добави канал
                    </button>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Създай кампания
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="channel-template">
    <div class="card mb-3 channel-item">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Тип канал *</label>
                        <select class="form-select" name="channels[{index}][channel_type]" required>
                            <option value="">-- Изберете --</option>
                            <option value="social_media">Социални медии</option>
                            <option value="email">Имейл маркетинг</option>
                            <option value="website">Уебсайт</option>
                            <option value="print">Печатни материали</option>
                            <option value="portal">Имотен портал</option>
                            <option value="other">Друго</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Име на канал *</label>
                        <input type="text" class="form-control" 
                               name="channels[{index}][channel_name]" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="mb-3">
                        <label class="form-label">Целева аудитория</label>
                        <textarea class="form-control" 
                                  name="channels[{index}][target_audience]" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Бюджет</label>
                        <div class="input-group">
                            <input type="number" class="form-control" 
                                   name="channels[{index}][budget_allocation]" 
                                   step="0.01" min="0">
                            <span class="input-group-text">лв.</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Начална дата</label>
                        <input type="date" class="form-control" 
                               name="channels[{index}][start_date]">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Крайна дата</label>
                        <input type="date" class="form-control" 
                               name="channels[{index}][end_date]">
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeChannel(this)">
                <i class="fas fa-trash"></i> Премахни канал
            </button>
        </div>
    </div>
</template>

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

                // Check if at least one property is selected
                const propertyCheckboxes = form.querySelectorAll('input[name="property_ids[]"]');
                const selectedProperties = Array.from(propertyCheckboxes).filter(cb => cb.checked);
                
                if (selectedProperties.length === 0) {
                    event.preventDefault();
                    form.querySelector('.invalid-feedback').style.display = 'block';
                }

                form.classList.add('was-validated')
            }, false)
        })
})()

// Channel management
let channelIndex = 0;

function addChannel() {
    const template = document.getElementById('channel-template');
    const channelsContainer = document.getElementById('channels');
    
    const clone = template.content.cloneNode(true);
    const html = clone.firstElementChild.outerHTML.replace(/{index}/g, channelIndex++);
    
    channelsContainer.insertAdjacentHTML('beforeend', html);
}

function removeChannel(button) {
    button.closest('.channel-item').remove();
}

// Set min date to today
document.getElementById('start_date').min = new Date().toISOString().split('T')[0];
document.getElementById('end_date').min = new Date().toISOString().split('T')[0];

// Add initial channel
addChannel();
</script>

<?php require_once 'views/layout/footer.php'; ?> 