<?php
require_once 'includes/auth.php';
use App\Database;

checkAuth();
checkPermission('view_reports');

$db = Database::getInstance()->getConnection();

// Вземане на статистики
$propertyCount = $db->query("SELECT COUNT(*) FROM properties")->fetchColumn();
$inquiryCount = $db->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Статистики и отчети</h1>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Общо имоти</h5>
                            <p class="card-text"><?php echo $propertyCount; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Общо запитвания</h5>
                            <p class="card-text"><?php echo $inquiryCount; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Общо потребители</h5>
                            <p class="card-text"><?php echo $userCount; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Последни запитвания</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Име</th>
                                        <th>Email</th>
                                        <th>Имоти</th>
                                        <th>Дата</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $db->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 5");
                                    $inquiries = $stmt->fetchAll();
                                    foreach ($inquiries as $inquiry): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                            <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                                            <td><?php echo htmlspecialchars($inquiry['property_id']); ?></td>
                                            <td><?php echo htmlspecialchars($inquiry['created_at']); ?></td>
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
</div> 