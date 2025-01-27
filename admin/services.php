<?php
require_once 'includes/header.php';
use App\Database;

$db = Database::getInstance()->getConnection();

// Филтриране
$where = "1=1";
$params = [];

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where .= " AND type = :type";
    $params[':type'] = $_GET['type'];
}

// Пагинация
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Общ брой услуги
$countStmt = $db->prepare("SELECT COUNT(*) FROM services WHERE $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = ceil($total / $perPage);

// Вземане на услугите за текущата страница
$stmt = $db->prepare("
    SELECT * FROM services 
    WHERE $where
    ORDER BY created_at DESC 
    LIMIT :offset, :perPage
");

$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$services = $stmt->fetchAll();

// Обработка на изтриване
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $serviceId = (int)$_POST['delete'];
    
    try {
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$serviceId]);
        $success = "Услугата беше успешно изтрита.";
    } catch (Exception $e) {
        $error = "Възникна грешка при изтриването на услугата.";
    }
}
?>

<div class="services-page">
    <div class="page-header">
        <h1>Управление на услуги</h1>
        <a href="service-edit.php" class="btn btn-primary">Добави нова услуга</a>
    </div>

    <!-- Филтри -->
    <div class="filters">
        <form method="GET" class="filter-form">
            <div class="form-group">
                <label for="type">Тип услуга</label>
                <select name="type" id="type">
                    <option value="">Всички</option>
                    <option value="company_registration" <?php echo isset($_GET['type']) && $_GET['type'] === 'company_registration' ? 'selected' : ''; ?>>Регистрация на фирми</option>
                    <option value="recruitment" <?php echo isset($_GET['type']) && $_GET['type'] === 'recruitment' ? 'selected' : ''; ?>>Подбор на персонал</option>
                    <option value="consulting" <?php echo isset($_GET['type']) && $_GET['type'] === 'consulting' ? 'selected' : ''; ?>>Консултации</option>
                </select>
            </div>
            
            <button type="submit" class="btn">Филтрирай</button>
        </form>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <!-- Таблица с услуги -->
    <div class="services-table">
        <table>
            <thead>
                <tr>
                    <th>Заглавие</th>
                    <th>Тип</th>
                    <th>Статус</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $service): ?>
                <tr>
                    <td><?php echo htmlspecialchars($service['title_bg']); ?></td>
                    <td><?php echo htmlspecialchars($service['type']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo $service['active'] ? 'active' : 'inactive'; ?>">
                            <?php echo $service['active'] ? 'Активна' : 'Неактивна'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d.m.Y', strtotime($service['created_at'])); ?></td>
                    <td class="actions">
                        <a href="service-edit.php?id=<?php echo $service['id']; ?>" class="btn btn-small">Редактирай</a>
                        <form method="POST" class="delete-form" onsubmit="return confirm('Сигурни ли сте, че искате да изтриете тази услуга?');">
                            <input type="hidden" name="delete" value="<?php echo $service['id']; ?>">
                            <button type="submit" class="btn btn-small btn-danger">Изтрий</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Пагинация -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?><?php echo isset($_GET['type']) ? '&type=' . htmlspecialchars($_GET['type']) : ''; ?>" 
               class="<?php echo $page === $i ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 