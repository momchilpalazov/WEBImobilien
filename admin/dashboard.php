<?php
require_once 'includes/header.php';
use App\Database;

// Вземане на статистика
$db = Database::getInstance()->getConnection();

// Брой имоти
$propertiesCount = $db->query("SELECT COUNT(*) FROM properties")->fetchColumn();

// Брой нови запитвания
$newInquiriesCount = $db->query("SELECT COUNT(*) FROM inquiries WHERE status = 'new'")->fetchColumn();

// Последни 5 запитвания
$latestInquiries = $db->query("
    SELECT i.*, p.title_bg as property_title 
    FROM inquiries i 
    LEFT JOIN properties p ON i.property_id = p.id 
    ORDER BY i.created_at DESC 
    LIMIT 5
")->fetchAll();
?>

<div class="dashboard">
    <h1>Табло за управление</h1>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Общо имоти</h3>
            <p class="stat-number"><?php echo $propertiesCount; ?></p>
        </div>
        
        <div class="stat-card">
            <h3>Нови запитвания</h3>
            <p class="stat-number"><?php echo $newInquiriesCount; ?></p>
        </div>
    </div>
    
    <div class="recent-inquiries">
        <h2>Последни запитвания</h2>
        <table>
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Име</th>
                    <th>Имейл</th>
                    <th>Имот</th>
                    <th>Статус</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($latestInquiries as $inquiry): ?>
                <tr>
                    <td><?php echo date('d.m.Y H:i', strtotime($inquiry['created_at'])); ?></td>
                    <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                    <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                    <td><?php echo htmlspecialchars($inquiry['property_title'] ?? 'Общо запитване'); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $inquiry['status']; ?>">
                            <?php echo $inquiry['status']; ?>
                        </span>
                    </td>
                    <td>
                        <a href="inquiry.php?id=<?php echo $inquiry['id']; ?>" class="btn btn-small">Преглед</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 