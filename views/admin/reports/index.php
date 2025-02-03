<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Генериране на отчети</h5>
                </div>
                <div class="card-body">
                    <form id="reportForm" action="/admin/reports/generate" method="POST">
                        <!-- Тип отчет -->
                        <div class="mb-3">
                            <label for="type" class="form-label">Тип отчет</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Изберете тип отчет</option>
                                <option value="property_performance">Представяне на имотите</option>
                                <option value="agent_activity">Активност на агентите</option>
                                <option value="market_analysis">Пазарен анализ</option>
                                <option value="financial_summary">Финансово обобщение</option>
                            </select>
                        </div>

                        <!-- Период -->
                        <div class="mb-3">
                            <label for="period" class="form-label">Период</label>
                            <select class="form-select" id="period" name="period">
                                <option value="week">Последната седмица</option>
                                <option value="month" selected>Последният месец</option>
                                <option value="quarter">Последното тримесечие</option>
                                <option value="year">Последната година</option>
                                <option value="custom">Персонализиран период</option>
                            </select>
                        </div>

                        <!-- Персонализиран период -->
                        <div class="mb-3 d-none" id="customPeriod">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Начална дата</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date">
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">Крайна дата</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date">
                                </div>
                            </div>
                        </div>

                        <!-- Агент -->
                        <div class="mb-3">
                            <label for="agent_id" class="form-label">Агент</label>
                            <select class="form-select" id="agent_id" name="agent_id">
                                <option value="">Всички агенти</option>
                                <?php foreach ($agents as $agent): ?>
                                    <option value="<?= $agent['id'] ?>"><?= htmlspecialchars($agent['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Тип имот -->
                        <div class="mb-3">
                            <label for="property_type" class="form-label">Тип имот</label>
                            <select class="form-select" id="property_type" name="property_type">
                                <option value="">Всички типове</option>
                                <?php foreach ($propertyTypes as $type => $label): ?>
                                    <option value="<?= $type ?>"><?= htmlspecialchars($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Локация -->
                        <div class="mb-3">
                            <label for="location" class="form-label">Локация</label>
                            <select class="form-select" id="location" name="location">
                                <option value="">Всички локации</option>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location ?>"><?= htmlspecialchars($location) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Формат -->
                        <div class="mb-3">
                            <label for="format" class="form-label">Формат</label>
                            <select class="form-select" id="format" name="format">
                                <option value="pdf">PDF</option>
                                <option value="xlsx">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-alt me-2"></i>
                            Генерирай отчет
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('reportForm');
    const periodSelect = document.getElementById('period');
    const customPeriod = document.getElementById('customPeriod');
    
    // Показване/скриване на полетата за персонализиран период
    periodSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            customPeriod.classList.remove('d-none');
        } else {
            customPeriod.classList.add('d-none');
        }
    });
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const loadingToast = showLoading('Генериране на отчет...');

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
                window.location.href = data.url;
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(() => {
            loadingToast.hide();
            showNotification('error', 'Възникна грешка при генерирането на отчета.');
        });
    });
});
</script>

<?php include_once '../layouts/footer.php'; ?> 