<?php
require_once 'includes/auth.php';
use App\Database;
checkAuth();
checkPermission('manage_users');

$db = Database::getInstance()->getConnection();

// Вземане на всички права и техните връзки с роли
$sql = "
    SELECT p.*, 
           GROUP_CONCAT(rp.role) as roles
    FROM permissions p
    LEFT JOIN role_permissions rp ON p.id = rp.permission_id
    GROUP BY p.id
    ORDER BY p.name
";

$permissions = $db->query($sql)->fetchAll();

// Обработка на формата за редактиране на права
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        // Изтриване на всички съществуващи връзки
        $db->exec("DELETE FROM role_permissions");
        
        // Добавяне на новите връзки
        foreach ($_POST['permissions'] as $role => $permissionIds) {
            foreach ($permissionIds as $permissionId) {
                $stmt = $db->prepare("INSERT INTO role_permissions (role, permission_id) VALUES (?, ?)");
                $stmt->execute([$role, $permissionId]);
            }
        }
        
        $db->commit();
        $success = 'Правата бяха обновени успешно';
        
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
                    <h1>Управление на права</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Право</th>
                                    <th>Описание</th>
                                    <th>Администратор</th>
                                    <th>Мениджър</th>
                                    <th>Агент</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($permissions as $permission): ?>
                                    <?php $roles = explode(',', $permission['roles']); ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($permission['name']); ?></td>
                                        <td><?php echo htmlspecialchars($permission['description']); ?></td>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       name="permissions[admin][]" 
                                                       value="<?php echo $permission['id']; ?>"
                                                       <?php echo in_array('admin', $roles) ? 'checked' : ''; ?>
                                                       class="form-check-input">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       name="permissions[manager][]" 
                                                       value="<?php echo $permission['id']; ?>"
                                                       <?php echo in_array('manager', $roles) ? 'checked' : ''; ?>
                                                       class="form-check-input">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="form-check">
                                                <input type="checkbox" 
                                                       name="permissions[agent][]" 
                                                       value="<?php echo $permission['id']; ?>"
                                                       <?php echo in_array('agent', $roles) ? 'checked' : ''; ?>
                                                       class="form-check-input">
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Запази промените</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div> 