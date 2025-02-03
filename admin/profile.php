<?php
require_once 'includes/auth.php';
use App\Database;

checkAuth();

$db = Database::getInstance()->getConnection();
$error = null;
$success = null;

// Вземане на информация за текущия потребител
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Обработка на формата
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        if (isset($_POST['update_profile'])) {
            // Обновяване на профилна информация
            $stmt = $db->prepare("
                UPDATE users 
                SET first_name = ?, 
                    last_name = ?, 
                    email = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $_POST['first_name'],
                $_POST['last_name'],
                $_POST['email'],
                $_SESSION['user_id']
            ]);
            
            $success = 'Профилът беше обновен успешно';
            
        } elseif (isset($_POST['change_password'])) {
            // Промяна на парола
            if (!password_verify($_POST['current_password'], $user['password'])) {
                throw new Exception('Текущата парола е грешна');
            }
            
            if ($_POST['new_password'] !== $_POST['confirm_password']) {
                throw new Exception('Паролите не съвпадат');
            }
            
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([
                password_hash($_POST['new_password'], PASSWORD_DEFAULT),
                $_SESSION['user_id']
            ]);
            
            $success = 'Паролата беше променена успешно';
        }
        
        $db->commit();
        
        // Обновяване на информацията за потребителя
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Профилни настройки</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Профилна информация -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Профилна информация</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label class="form-label">Потребителско име</label>
                                    <input type="text" class="form-control" value="<?php echo $user['username']; ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Име</label>
                                    <input type="text" name="first_name" class="form-control" 
                                           value="<?php echo $user['first_name']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Фамилия</label>
                                    <input type="text" name="last_name" class="form-control" 
                                           value="<?php echo $user['last_name']; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo $user['email']; ?>" required>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    Запази промените
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Промяна на парола -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Промяна на парола</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="mb-3">
                                    <label class="form-label">Текуща парола</label>
                                    <input type="password" name="current_password" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Нова парола</label>
                                    <input type="password" name="new_password" class="form-control" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Потвърди новата парола</label>
                                    <input type="password" name="confirm_password" class="form-control" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    Промени паролата
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Валидация на формите
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        });
    });
});
</script> 