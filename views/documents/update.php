<?php
use App\Utils\Format;
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mt-4">Редактиране на документ</h1>
        <a href="/documents/view/<?= $document['id'] ?>" class="btn btn-outline-secondary">
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
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="title" class="form-label">Заглавие *</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="title" 
                                   name="title" 
                                   value="<?= htmlspecialchars($document['title']) ?>" 
                                   required>
                            <div class="invalid-feedback">
                                Моля, въведете заглавие
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="3"><?= htmlspecialchars($document['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Текущ файл</label>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-<?= Format::fileIcon($document['file_type']) ?> fa-lg text-muted me-2"></i>
                                <span><?= $document['file_name'] ?></span>
                                <span class="text-muted ms-2">(<?= Format::fileSize($document['file_size']) ?>)</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="category" class="form-label">Категория *</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">-- Изберете --</option>
                                <option value="contract" <?= $document['category'] === 'contract' ? 'selected' : '' ?>>Договор</option>
                                <option value="deed" <?= $document['category'] === 'deed' ? 'selected' : '' ?>>Нотариален акт</option>
                                <option value="certificate" <?= $document['category'] === 'certificate' ? 'selected' : '' ?>>Сертификат</option>
                                <option value="permit" <?= $document['category'] === 'permit' ? 'selected' : '' ?>>Разрешително</option>
                                <option value="tax" <?= $document['category'] === 'tax' ? 'selected' : '' ?>>Данъчен документ</option>
                                <option value="insurance" <?= $document['category'] === 'insurance' ? 'selected' : '' ?>>Застраховка</option>
                                <option value="appraisal" <?= $document['category'] === 'appraisal' ? 'selected' : '' ?>>Оценка</option>
                                <option value="other" <?= $document['category'] === 'other' ? 'selected' : '' ?>>Друго</option>
                            </select>
                            <div class="invalid-feedback">
                                Моля, изберете категория
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Статус</label>
                            <select class="form-select" id="status" name="status">
                                <option value="draft" <?= $document['status'] === 'draft' ? 'selected' : '' ?>>Чернова</option>
                                <option value="active" <?= $document['status'] === 'active' ? 'selected' : '' ?>>Активен</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Шаблон</label>
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_template" 
                                       name="is_template" 
                                       value="1"
                                       <?= $document['is_template'] ? 'checked' : '' ?>>
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

                    <!-- Списък със съществуващи връзки -->
                    <div id="existing-relations" class="mt-3">
                        <?php if (!empty($relations)): ?>
                            <?php foreach ($relations as $relation): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-secondary me-2">
                                        <?= Format::entityType($relation['relation_type']) ?>
                                    </span>
                                    <span class="me-auto">
                                        <?= htmlspecialchars($relation['name']) ?>
                                    </span>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="removeRelation(<?= $relation['id'] ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Изисква подпис от</label>
                    <div id="signers">
                        <?php if (!empty($signatures)): ?>
                            <?php foreach ($signatures as $signature): ?>
                                <div class="card mb-3 signer-item">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Тип</label>
                                                    <select class="form-select" 
                                                            name="signers[<?= $signature['id'] ?>][type]" 
                                                            onchange="updateSignerFields(this)">
                                                        <option value="client" <?= $signature['signer_type'] === 'client' ? 'selected' : '' ?>>Клиент</option>
                                                        <option value="agent" <?= $signature['signer_type'] === 'agent' ? 'selected' : '' ?>>Агент</option>
                                                        <option value="manager" <?= $signature['signer_type'] === 'manager' ? 'selected' : '' ?>>Мениджър</option>
                                                        <option value="other" <?= $signature['signer_type'] === 'other' ? 'selected' : '' ?>>Друго</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <?php if ($signature['signer_type'] === 'other'): ?>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Име</label>
                                                                <input type="text" 
                                                                       class="form-control" 
                                                                       name="signers[<?= $signature['id'] ?>][name]"
                                                                       value="<?= htmlspecialchars($signature['signer_name']) ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Email</label>
                                                                <input type="email" 
                                                                       class="form-control" 
                                                                       name="signers[<?= $signature['id'] ?>][email]"
                                                                       value="<?= htmlspecialchars($signature['signer_email']) ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="mb-3">
                                                        <label class="form-label">Избор</label>
                                                        <select class="form-select" 
                                                                name="signers[<?= $signature['id'] ?>][id]">
                                                            <option value="">-- Изберете --</option>
                                                            <!-- Опциите ще се заредят динамично -->
                                                        </select>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="removeSigner(this)">
                                            <i class="fas fa-trash"></i> Премахни
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addSigner()">
                        <i class="fas fa-plus"></i> Добави подписващ
                    </button>
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

<template id="signer-template">
    <div class="card mb-3 signer-item">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Тип</label>
                        <select class="form-select" name="new_signers[{index}][type]" onchange="updateSignerFields(this)">
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
                        <select class="form-select" name="new_signers[{index}][id]">
                            <option value="">-- Изберете --</option>
                        </select>
                    </div>
                    <div class="mb-3 signer-manual" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Име</label>
                                <input type="text" class="form-control" name="new_signers[{index}][name]">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="new_signers[{index}][email]">
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

// Add relation
document.getElementById('relation_id').addEventListener('change', function() {
    if (this.value) {
        const type = document.getElementById('relation_type').value;
        const id = this.value;
        const name = this.options[this.selectedIndex].text;
        
        fetch(`/documents/add-relation/<?= $document['id'] ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `type=${type}&id=${id}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const html = `
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-secondary me-2">
                            ${type === 'property' ? 'Имот' : 
                              type === 'deal' ? 'Сделка' : 
                              type === 'client' ? 'Клиент' : 
                              type === 'agent' ? 'Агент' : type}
                        </span>
                        <span class="me-auto">
                            ${name}
                        </span>
                        <button type="button" 
                                class="btn btn-sm btn-outline-danger"
                                onclick="removeRelation(${data.relation_id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                document.getElementById('existing-relations').insertAdjacentHTML('beforeend', html);
                
                // Reset selects
                this.value = '';
                document.getElementById('relation_type').value = '';
                this.disabled = true;
            } else {
                alert(data.error || 'Възникна грешка при добавянето на връзката');
            }
        })
        .catch(error => console.error('Error:', error));
    }
});

function removeRelation(id) {
    if (!confirm('Сигурни ли сте, че искате да премахнете тази връзка?')) {
        return;
    }

    fetch(`/documents/remove-relation/${id}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Възникна грешка при премахването на връзката');
        }
    })
    .catch(error => console.error('Error:', error));
}

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
    const signerItem = button.closest('.signer-item');
    const signerId = signerItem.querySelector('input[name^="signers["]')?.name?.match(/\d+/)?.[0];
    
    if (signerId) {
        if (!confirm('Сигурни ли сте, че искате да премахнете този подписващ?')) {
            return;
        }

        fetch(`/documents/remove-signer/${signerId}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                signerItem.remove();
            } else {
                alert(data.error || 'Възникна грешка при премахването на подписващия');
            }
        })
        .catch(error => console.error('Error:', error));
    } else {
        signerItem.remove();
    }
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

// Load initial signers data
document.querySelectorAll('select[name^="signers["][name$="[type]"]').forEach(select => {
    updateSignerFields(select);
});
</script>

<?php require_once 'views/layout/footer.php'; ?> 