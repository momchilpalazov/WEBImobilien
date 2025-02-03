<?php $this->layout('admin/layout') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Настройки на агенцията</h3>
                </div>
                <div class="card-body">
                    <form id="agencySettingsForm" action="/admin/settings/agency/update" method="POST">
                        <div class="row">
                            <!-- Основна информация -->
                            <div class="col-md-6">
                                <h4 class="mb-3">Основна информация</h4>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Име на агенцията</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($config['name']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="bulstat" class="form-label">ЕИК/БУЛСТАТ</label>
                                    <input type="text" class="form-control" id="bulstat" name="bulstat" value="<?= htmlspecialchars($config['bulstat']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Адрес</label>
                                    <input type="text" class="form-control" id="address" name="address" value="<?= htmlspecialchars($config['address']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="city" class="form-label">Град</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?= htmlspecialchars($config['city']) ?>" required>
                                </div>
                            </div>
                            
                            <!-- Контактна информация -->
                            <div class="col-md-6">
                                <h4 class="mb-3">Контактна информация</h4>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Телефон</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($config['phone']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Имейл</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($config['email']) ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="representative" class="form-label">Представител</label>
                                    <input type="text" class="form-control" id="representative" name="representative" value="<?= htmlspecialchars($config['representative']) ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <!-- Банкова информация -->
                            <div class="col-md-6">
                                <h4 class="mb-3">Банкова информация</h4>
                                
                                <div class="mb-3">
                                    <label for="bank_name" class="form-label">Име на банката</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" value="<?= htmlspecialchars($config['bank_name']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="bank_account" class="form-label">IBAN</label>
                                    <input type="text" class="form-control" id="bank_account" name="bank_account" value="<?= htmlspecialchars($config['bank_account']) ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="bank_bic" class="form-label">BIC</label>
                                    <input type="text" class="form-control" id="bank_bic" name="bank_bic" value="<?= htmlspecialchars($config['bank_bic']) ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Запазване
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission
    document.getElementById('agencySettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        fetch(this.action, {
            method: 'POST',
            body: new FormData(this),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Настройките са запазени успешно.');
            } else {
                alert(data.message || 'Възникна грешка при запазване на настройките.');
            }
        })
        .catch(() => {
            alert('Възникна грешка при запазване на настройките.');
        });
    });
});
</script>
<?php $this->end() ?> 