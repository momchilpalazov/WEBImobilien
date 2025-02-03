<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Огледи на имоти</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" id="todayFilter">
                            <i class="fas fa-calendar-day me-2"></i>
                            Днес
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="weekFilter">
                            <i class="fas fa-calendar-week me-2"></i>
                            Тази седмица
                        </button>
                        <button type="button" class="btn btn-outline-primary" id="monthFilter">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Този месец
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th>Час</th>
                                    <th>Имот</th>
                                    <th>Клиент</th>
                                    <th>Агент</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($viewings as $viewing): ?>
                                    <tr>
                                        <td><?= date('d.m.Y', strtotime($viewing['date'])) ?></td>
                                        <td><?= $viewing['time'] ?></td>
                                        <td>
                                            <a href="/admin/properties/<?= $viewing['property_id'] ?>" target="_blank">
                                                <?= $viewing['property_title'] ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?= $viewing['client_name'] ?><br>
                                            <small class="text-muted">
                                                <?= $viewing['client_phone'] ?>
                                            </small>
                                        </td>
                                        <td><?= $viewing['agent_name'] ?></td>
                                        <td>
                                            <?php
                                            $statusClass = [
                                                'scheduled' => 'bg-info',
                                                'completed' => 'bg-success',
                                                'cancelled' => 'bg-danger'
                                            ][$viewing['status']] ?? 'bg-secondary';
                                            $statusText = [
                                                'scheduled' => 'Насрочен',
                                                'completed' => 'Проведен',
                                                'cancelled' => 'Отказан'
                                            ][$viewing['status']] ?? 'Неизвестен';
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
                                        </td>
                                        <td>
                                            <?php if ($viewing['status'] === 'scheduled'): ?>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-sm btn-success complete-viewing" 
                                                            data-id="<?= $viewing['id'] ?>" title="Маркирай като проведен">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-warning reschedule-viewing" 
                                                            data-id="<?= $viewing['id'] ?>" title="Пренасрочи">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger cancel-viewing" 
                                                            data-id="<?= $viewing['id'] ?>" title="Откажи">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модален прозорец за обратна връзка -->
<div class="modal fade" id="feedbackModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Обратна връзка от огледа</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="feedbackForm" action="/admin/viewings/complete" method="POST">
                    <input type="hidden" name="viewing_id" id="feedback_viewing_id">

                    <div class="mb-3">
                        <label for="client_interest_level" class="form-label">Ниво на интерес</label>
                        <select class="form-select" id="client_interest_level" name="client_interest_level" required>
                            <option value="">Изберете ниво</option>
                            <option value="high">Висок интерес</option>
                            <option value="medium">Среден интерес</option>
                            <option value="low">Нисък интерес</option>
                            <option value="none">Без интерес</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="client_feedback" class="form-label">Обратна връзка от клиента</label>
                        <textarea class="form-control" id="client_feedback" name="client_feedback" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="agent_notes" class="form-label">Бележки на агента</label>
                        <textarea class="form-control" id="agent_notes" name="agent_notes" rows="3"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затвори</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>
                            Запази
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модален прозорец за отказване -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Отказване на оглед</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="cancelForm" action="/admin/viewings/cancel" method="POST">
                    <input type="hidden" name="viewing_id" id="cancel_viewing_id">

                    <div class="mb-3">
                        <label for="cancel_reason" class="form-label">Причина за отказване</label>
                        <textarea class="form-control" id="cancel_reason" name="reason" rows="3" required></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затвори</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>
                            Откажи огледа
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Модален прозорец за пренасрочване -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Пренасрочване на оглед</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="rescheduleForm" action="/admin/viewings/reschedule" method="POST">
                    <input type="hidden" name="viewing_id" id="reschedule_viewing_id">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reschedule_date" class="form-label">Нова дата</label>
                                <input type="date" class="form-control" id="reschedule_date" name="date" required
                                       min="<?= date('Y-m-d') ?>"
                                       max="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reschedule_time" class="form-label">Нов час</label>
                                <select class="form-select" id="reschedule_time" name="time" required>
                                    <option value="">Изберете час</option>
                                    <?php for ($hour = 9; $hour < 18; $hour++): ?>
                                        <option value="<?= sprintf('%02d:00', $hour) ?>"><?= sprintf('%02d:00', $hour) ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Затвори</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-calendar-alt me-2"></i>
                            Пренасрочи
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Филтри
    const filterButtons = document.querySelectorAll('#todayFilter, #weekFilter, #monthFilter');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // TODO: Имплементирайте филтрирането
        });
    });

    // Маркиране като проведен
    document.querySelectorAll('.complete-viewing').forEach(button => {
        button.addEventListener('click', function() {
            const viewingId = this.dataset.id;
            document.getElementById('feedback_viewing_id').value = viewingId;
            new bootstrap.Modal(document.getElementById('feedbackModal')).show();
        });
    });

    // Отказване
    document.querySelectorAll('.cancel-viewing').forEach(button => {
        button.addEventListener('click', function() {
            const viewingId = this.dataset.id;
            document.getElementById('cancel_viewing_id').value = viewingId;
            new bootstrap.Modal(document.getElementById('cancelModal')).show();
        });
    });

    // Пренасрочване
    document.querySelectorAll('.reschedule-viewing').forEach(button => {
        button.addEventListener('click', function() {
            const viewingId = this.dataset.id;
            document.getElementById('reschedule_viewing_id').value = viewingId;
            new bootstrap.Modal(document.getElementById('rescheduleModal')).show();
        });
    });

    // Обработка на формите
    const forms = {
        feedback: document.getElementById('feedbackForm'),
        cancel: document.getElementById('cancelForm'),
        reschedule: document.getElementById('rescheduleForm')
    };

    Object.entries(forms).forEach(([type, form]) => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const loadingToast = showLoading('Обработка...');

            fetch(this.action, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                loadingToast.hide();
                
                if (data.success) {
                    showNotification('success', data.message);
                    bootstrap.Modal.getInstance(this.closest('.modal')).hide();
                    location.reload(); // Презареждане на страницата
                } else {
                    showNotification('error', data.message);
                }
            })
            .catch(() => {
                loadingToast.hide();
                showNotification('error', 'Възникна грешка при обработката.');
            });
        });
    });
});</script>

<?php include_once '../layouts/footer.php'; ?> 