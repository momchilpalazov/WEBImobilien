<?php
require_once 'includes/header.php';
use App\Database;

$db = Database::getInstance()->getConnection();

if (!isset($_GET['id'])) {
    header('Location: inquiries.php');
    exit;
}

$id = (int)$_GET['id'];

// Вземане на детайлите за запитването
$stmt = $db->prepare("
    SELECT i.*, 
           p.title_bg as property_title,
           s.title_bg as service_title
    FROM inquiries i 
    LEFT JOIN properties p ON i.property_id = p.id
    LEFT JOIN services s ON i.service_id = s.id
    WHERE i.id = ?
");
$stmt->execute([$id]);
$inquiry = $stmt->fetch();

if (!$inquiry) {
    header('Location: inquiries.php');
    exit;
}
?>

<div class="inquiry-view-page">
    <div class="page-header">
        <h1>Преглед на запитване</h1>
        <a href="inquiries.php" class="btn btn-secondary">Назад към списъка</a>
    </div>

    <div class="inquiry-details">
        <div class="card">
            <div class="card-header">
                <h2>Информация за контакт</h2>
            </div>
            <div class="card-body">
                <div class="detail-row">
                    <strong>Име:</strong>
                    <span><?php echo htmlspecialchars($inquiry['name']); ?></span>
                </div>
                
                <div class="detail-row">
                    <strong>Имейл:</strong>
                    <span>
                        <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>">
                            <?php echo htmlspecialchars($inquiry['email']); ?>
                        </a>
                    </span>
                </div>
                
                <?php if ($inquiry['phone']): ?>
                <div class="detail-row">
                    <strong>Телефон:</strong>
                    <span>
                        <a href="tel:<?php echo htmlspecialchars($inquiry['phone']); ?>">
                            <?php echo htmlspecialchars($inquiry['phone']); ?>
                        </a>
                    </span>
                </div>
                <?php endif; ?>
                
                <div class="detail-row">
                    <strong>Дата на запитване:</strong>
                    <span><?php echo date('d.m.Y H:i', strtotime($inquiry['created_at'])); ?></span>
                </div>
            </div>
        </div>

        <?php if ($inquiry['property_id'] || $inquiry['service_id']): ?>
        <div class="card">
            <div class="card-header">
                <h2>Относно</h2>
            </div>
            <div class="card-body">
                <?php if ($inquiry['property_id']): ?>
                <div class="detail-row">
                    <strong>Имот:</strong>
                    <span>
                        <a href="property-edit.php?id=<?php echo $inquiry['property_id']; ?>">
                            <?php echo htmlspecialchars($inquiry['property_title']); ?>
                        </a>
                    </span>
                </div>
                <?php endif; ?>

                <?php if ($inquiry['service_id']): ?>
                <div class="detail-row">
                    <strong>Услуга:</strong>
                    <span>
                        <a href="service-edit.php?id=<?php echo $inquiry['service_id']; ?>">
                            <?php echo htmlspecialchars($inquiry['service_title']); ?>
                        </a>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Съобщение</h2>
            </div>
            <div class="card-body">
                <div class="message-content">
                    <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Статус на запитването</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="inquiries.php">
                    <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                    <input type="hidden" name="update_status" value="1">
                    <div class="form-group">
                        <select name="status" class="status-select status-<?php echo $inquiry['status']; ?>">
                            <option value="new" <?php echo $inquiry['status'] === 'new' ? 'selected' : ''; ?>>Ново</option>
                            <option value="in_progress" <?php echo $inquiry['status'] === 'in_progress' ? 'selected' : ''; ?>>В процес</option>
                            <option value="completed" <?php echo $inquiry['status'] === 'completed' ? 'selected' : ''; ?>>Приключено</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Обнови статуса</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 