<?php require_once 'views/layout/header.php'; ?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Качване на документ</h1>
        <a href="/documents" class="btn btn-outline-secondary">
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
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <div class="row">
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

                        <div class="mb-3">
                            <label for="file" class="form-label">Файл *</label>
                            <input type="file" class="form-control" id="file" name="file" required>
                            <div class="invalid-feedback">
                                Моля, изберете файл
                            </div>
                            <small class="text-muted">
                                Максимален размер: <?= ini_get('upload_max_filesize') ?>
                            </small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="category" class="form-label">Категория *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">-- Изберете --</option>
                                <option value="contract">Договор</option>
                                <option value="deed">Нотариален акт</option>
                                <option value="certificate">Сертификат</option>
                                <option value="permit">Разрешително</option>
                                <option value="tax">Данъчен документ</option>
                                <option value="insurance">Застраховка</option>
                                <option value="appraisal">Оценка</option>
                                <option value="other">Друго</option>
                            </select>
                            <div class="invalid-feedback">
                                Моля, изберете категория
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft">Чернова</option>
                                <option value="active" selected>Активен</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Шаблон</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_template" name="is_template" value="1">
                                <label class="form-check-label" for="is_template">
                                    Запази като шаблон
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Свързани с</label>
                    <div class="row">
                        <div class="col-md-4">
                            <select class="form-select" name="relation_type" id="relation_type">
                                <option value="">-- Изберете тип --</option>
                                <option value="property">Имот</option>
                                <option value="deal">Сделка</option>
                                <option value="client">Клиент</option>
                                <option value="agent">Агент</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <select class="form-select" name="relation_id" id="relation_id" disabled>
                                <option value="">-- Изберете --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Изисква подпис от</label>
                    <div id="signers">
                        <!-- Тук ще се добавят динамично подписващите -->
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSigner()">
                        <i class="fas fa-plus"></i> Добави подписващ
                    </button>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Качи документ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="signer-template">
    <div class="card mb-3 signer-item">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Тип</label>
                        <select class="form-select" name="signers[{index}][type]" onchange="updateSignerFields(this)">
                            <option value="client">Клиент</option>
                            <option value="agent">Агент</option>
                            <option value="manager">Мениджър</option>
                            <option value="other">Друго</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="mb-3 signer-select">
                        <label class="form-label">Избор</label>
                        <select class="form-select" name="signers[{index}][id]">
                            <option value="">-- Изберете --</option>
                        </select>
                    </div>
                    <div class="mb-3 signer-manual" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Име</label>
                                <input type="text" class="form-control" name="signers[{index}][name]">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="signers[{index}][email]">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSigner(this)">
                <i class="fas fa-trash"></i> Премахни
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

                form.classList.add('was-validated')
            }, false)
        })
})()

// Relation type handling
document.getElementById('relation_type').addEventListener('change', function() {
    const relationId = document.getElementById('relation_id');
    relationId.disabled = !this.value;
    relationId.innerHTML = '<option value="">-- Изберете --</option>';
    
    if (this.value) {
        // Fetch related items based on type
        fetch(`/documents/get-relations/${this.value}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    relationId.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }
});

// Signers management
let signerIndex = 0;

function addSigner() {
    const template = document.getElementById('signer-template');
    const signersContainer = document.getElementById('signers');
    
    const clone = template.content.cloneNode(true);
    const html = clone.firstElementChild.outerHTML.replace(/{index}/g, signerIndex++);
    
    signersContainer.insertAdjacentHTML('beforeend', html);
    updateSignerFields(signersContainer.lastElementChild.querySelector('select[name$="[type]"]'));
}

function removeSigner(button) {
    button.closest('.signer-item').remove();
}

function updateSignerFields(select) {
    const signerItem = select.closest('.signer-item');
    const selectDiv = signerItem.querySelector('.signer-select');
    const manualDiv = signerItem.querySelector('.signer-manual');
    const idSelect = signerItem.querySelector('select[name$="[id]"]');
    
    if (select.value === 'other') {
        selectDiv.style.display = 'none';
        manualDiv.style.display = 'block';
    } else {
        selectDiv.style.display = 'block';
        manualDiv.style.display = 'none';
        
        // Fetch signers based on type
        fetch(`/documents/get-signers/${select.value}`)
            .then(response => response.json())
            .then(data => {
                idSelect.innerHTML = '<option value="">-- Изберете --</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    idSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error:', error));
    }
}

// Add initial signer
addSigner();
</script>

<?php require_once 'views/layout/footer.php'; ?> 