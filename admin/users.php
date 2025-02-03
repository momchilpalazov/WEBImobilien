<?php
require_once 'includes/auth.php';
use App\Database;
checkAuth();
checkPermission('manage_users');

$db = Database::getInstance()->getConnection();

// Вземане на всички потребители
$sql = "
    SELECT u.*, 
           COUNT(p.id) as properties_count,
           COUNT(i.id) as inquiries_count
    FROM users u
    LEFT JOIN properties p ON u.id = p.created_by
    LEFT JOIN inquiries i ON u.id = i.assigned_to
    GROUP BY u.id
    ORDER BY u.created_at DESC
";

$users = $db->query($sql)->fetchAll();
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Управление на потребители</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="user-edit.php" class="btn btn-primary">
                        <i class="mdi mdi-plus"></i> Нов потребител
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Име</th>
                                    <th>Email</th>
                                    <th>Роля</th>
                                    <th>Имоти</th>
                                    <th>Запитвания</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td><?php echo $user['properties_count']; ?></td>
                                    <td><?php echo $user['inquiries_count']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo $user['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="user-edit.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-sm btn-info">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-user" 
                                                    data-id="<?php echo $user['id']; ?>">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация на DataTables
    const table = new DataTable('.table', {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/bg.json'
        }
    });
    
    // Изтриване на потребител
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            if (confirm('Сигурни ли сте, че искате да изтриете този потребител?')) {
                const userId = this.dataset.id;
                
                fetch('ajax/delete-user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: userId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Презареждане на страницата
                        window.location.reload();
                    } else {
                        alert('Грешка при изтриване на потребителя');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Възникна грешка');
                });
            }
        });
    });
});
</script> 