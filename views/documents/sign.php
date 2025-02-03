<?php
use App\Utils\Format;
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mt-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php elseif ($signature['signature_status'] === 'signed'): ?>
        <div class="alert alert-success mt-4">
            <i class="fas fa-check-circle me-2"></i>
            Документът е успешно подписан на <?= Format::date($signature['signature_date'], 'd.m.Y H:i') ?>
        </div>
    <?php elseif ($signature['signature_status'] === 'rejected'): ?>
        <div class="alert alert-danger mt-4">
            <i class="fas fa-times-circle me-2"></i>
            Подписването е отказано на <?= Format::date($signature['signature_date'], 'd.m.Y H:i') ?>
        </div>
    <?php elseif ($signature['signature_status'] === 'expired'): ?>
        <div class="alert alert-warning mt-4">
            <i class="fas fa-exclamation-circle me-2"></i>
            Срокът за подписване е изтекъл на <?= Format::date($signature['expiration_date'], 'd.m.Y H:i') ?>
        </div>
    <?php endif; ?>

    <div class="card my-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-file-signature me-2"></i>
                Подписване на документ
            </h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-8">
                    <!-- Информация за документа -->
                    <h6 class="mb-3">Информация за документа</h6>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-file-<?= Format::fileIcon($document['file_type']) ?> fa-2x text-muted me-3"></i>
                        <div>
                            <h5 class="mb-0"><?= htmlspecialchars($document['title']) ?></h5>
                            <small class="text-muted">
                                <?php
                                $categories = [
                                    'contract' => 'Договор',
                                    'deed' => 'Нотариален акт',
                                    'certificate' => 'Сертификат',
                                    'permit' => 'Разрешително',
                                    'tax' => 'Данъчен документ',
                                    'insurance' => 'Застраховка',
                                    'appraisal' => 'Оценка',
                                    'other' => 'Друго'
                                ];
                                echo $categories[$document['category']] ?? 'Неизвестно';
                                ?>
                            </small>
                        </div>
                    </div>

                    <?php if (!empty($document['description'])): ?>
                        <div class="mb-3">
                            <strong>Описание:</strong><br>
                            <?= nl2br(htmlspecialchars($document['description'])) ?>
                        </div>
                    <?php endif; ?>

                    <div class="text-muted small">
                        <div>Размер: <?= Format::fileSize($document['file_size']) ?></div>
                        <div>Създаден на: <?= Format::date($document['created_at']) ?></div>
                        <div>Създаден от: <?= htmlspecialchars($document['created_by_name']) ?></div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Информация за подписващия -->
                    <h6 class="mb-3">Информация за подписващия</h6>
                    <div class="mb-3">
                        <strong>Име:</strong><br>
                        <?= htmlspecialchars($signature['signer_name']) ?>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong><br>
                        <?= htmlspecialchars($signature['signer_email']) ?>
                    </div>
                    <?php if ($signature['expiration_date']): ?>
                        <div class="mb-3">
                            <strong>Валидно до:</strong><br>
                            <?= Format::date($signature['expiration_date'], 'd.m.Y H:i') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($signature['signature_status'] === 'pending'): ?>
                <div class="row">
                    <div class="col-md-8">
                        <!-- Преглед на документа -->
                        <div class="mb-4">
                            <h6 class="mb-3">Преглед на документа</h6>
                            <div class="ratio ratio-16x9 mb-3">
                                <iframe src="/documents/preview/<?= $document['id'] ?>?signature_id=<?= $signature['id'] ?>" 
                                        class="border rounded"
                                        allowfullscreen></iframe>
                            </div>
                        </div>

                        <!-- Форма за подписване -->
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="agree" name="agree" required>
                                    <label class="form-check-label" for="agree">
                                        Прочетох и се съгласявам със съдържанието на документа
                                    </label>
                                    <div class="invalid-feedback">
                                        Трябва да се съгласите с документа, за да продължите
                                    </div>
                                </div>
                            </div>

                            <?php if ($signature['requires_pin']): ?>
                                <div class="mb-3">
                                    <label for="pin" class="form-label">PIN код за потвърждение *</label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="pin" 
                                                   name="pin" 
                                                   pattern="\d{6}" 
                                                   maxlength="6"
                                                   required>
                                            <div class="invalid-feedback">
                                                Моля, въведете валиден 6-цифрен PIN код
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" 
                                                    class="btn btn-outline-primary" 
                                                    onclick="sendPin()">
                                                <i class="fas fa-paper-plane"></i> Изпрати нов PIN
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        PIN кодът е изпратен на вашия email адрес
                                    </small>
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="comment" class="form-label">Коментар (незадължително)</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3"></textarea>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" name="action" value="sign" class="btn btn-primary">
                                    <i class="fas fa-signature"></i> Подпиши
                                </button>
                                <button type="submit" name="action" value="reject" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Откажи
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-4">
                        <!-- Други подписващи -->
                        <h6 class="mb-3">Други подписващи</h6>
                        <?php if (empty($other_signatures)): ?>
                            <p class="text-muted mb-0">Няма други подписващи</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($other_signatures as $other): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= htmlspecialchars($other['signer_name']) ?></strong>
                                                <small class="d-block text-muted">
                                                    <?= $other['signer_email'] ?>
                                                </small>
                                            </div>
                                            <?php
                                            $statusClass = [
                                                'pending' => 'warning',
                                                'signed' => 'success',
                                                'rejected' => 'danger',
                                                'expired' => 'secondary'
                                            ][$other['signature_status']] ?? 'secondary';
                                            
                                            $statusText = [
                                                'pending' => 'Очаква подпис',
                                                'signed' => 'Подписан',
                                                'rejected' => 'Отказан',
                                                'expired' => 'Изтекъл'
                                            ][$other['signature_status']] ?? 'Неизвестно';
                                            ?>
                                            <span class="badge bg-<?= $statusClass ?>">
                                                <?= $statusText ?>
                                            </span>
                                        </div>
                                        <?php if ($other['signature_status'] === 'signed'): ?>
                                            <small class="text-muted">
                                                Подписан на: <?= Format::date($other['signature_date'], 'd.m.Y H:i') ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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

function sendPin() {
    fetch(`/documents/send-pin/<?= $signature['id'] ?>`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Нов PIN код е изпратен на вашия email адрес');
        } else {
            alert(data.error || 'Възникна грешка при изпращането на PIN код');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Възникна грешка при изпращането на PIN код');
    });
}
</script>

<?php require_once 'views/layout/footer.php'; ?> 