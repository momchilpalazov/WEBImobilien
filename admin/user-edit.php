<?php
require_once 'includes/auth.php';
use App\Database;

checkAuth();
checkPermission('manage_users');

$db = Database::getInstance()->getConnection();
$user = null;
$error = null;

// Ако редактираме съществуващ потребител
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: users.php');
        exit;
    }
}

// Обработка на формата
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        $data = [
            'username' => $_POST['username'],
            'email' => $_POST['email'],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'role' => $_POST['role'],
            'status' => $_POST['status']
        ];
        
        // Ако се добавя нов потребител или се променя паролата
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        if (isset($_GET['id'])) {
            // Обновяване на съществуващ потребител
            $sql = "UPDATE users SET 
                    username = :username,
                    email = :email,
                    first_name = :first_name,
                    last_name = :last_name,
                    role = :role,
                    status = :status";
            
            if (isset($data['password'])) {
                $sql .= ", password = :password";
            }
            
            $sql .= " WHERE id = :id";
            $data['id'] = $_GET['id'];
            
        } else {
            // Добавяне на нов потребител
            if (empty($_POST['password'])) {
                throw new Exception('Паролата е задължителна за нов потребител');
            }
            
            $sql = "INSERT INTO users (username, email, password, first_name, last_name, role, status)
                    VALUES (:username, :email, :password, :first_name, :last_name, :role, :status)";
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($data);
        
        $db->commit();
        header('Location: users.php');
        exit;
        
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
                    <h1><?php echo isset($_GET['id']) ? 'Редактиране на потребител' : 'Нов потребител'; ?></h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Потребителско име</label>
                                    <input type="text" name="username" class="form-control" 
                                           value="<?php echo $user['username'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" 
                                           value="<?php echo $user['email'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Парола</label>
                                    <input type="password" name="password" class="form-control" 
                                           <?php echo !isset($_GET['id']) ? 'required' : ''; ?>>
                                    <?php if (isset($_GET['id'])): ?>
                                        <small class="text-muted">Оставете празно, ако не искате да променяте паролата</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Име</label>
                                    <input type="text" name="first_name" class="form-control" 
                                           value="<?php echo $user['first_name'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Фамилия</label>
                                    <input type="text" name="last_name" class="form-control" 
                                           value="<?php echo $user['last_name'] ?? ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Роля</label>
                                    <select name="role" class="form-select" required>
                                        <option value="admin" <?php echo ($user['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                                            Администратор
                                        </option>
                                        <option value="manager" <?php echo ($user['role'] ?? '') === 'manager' ? 'selected' : ''; ?>>
                                            Мениджър
                                        </option>
                                        <option value="agent" <?php echo ($user['role'] ?? '') === 'agent' ? 'selected' : ''; ?>>
                                            Агент
                                        </option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Статус</label>
                                    <select name="status" class="form-select" required>
                                        <option value="active" <?php echo ($user['status'] ?? '') === 'active' ? 'selected' : ''; ?>>
                                            Активен
                                        </option>
                                        <option value="inactive" <?php echo ($user['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>
                                            Неактивен
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Запази</button>
                            <a href="users.php" class="btn btn-secondary">Отказ</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Валидация на формата
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.needs-validation');
    
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });
});
</script> 