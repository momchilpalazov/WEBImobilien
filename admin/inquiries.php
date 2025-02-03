<?php
session_start();
require_once '../src/Database.php';
require_once '../config/database.php';

// Проверка дали админът е логнат
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

use App\Database;
$db = Database::getInstance()->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Общ брой запитвания
$total_query = "SELECT COUNT(*) as total FROM inquiries";
$total_result = $db->query($total_query);
$total_inquiries = $total_result->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_inquiries / $per_page);

// Вземане на запитванията за текущата страница
$query = "SELECT i.*, p.title_bg as property_title 
          FROM inquiries i 
          LEFT JOIN properties p ON i.property_id = p.id 
          ORDER BY i.created_at DESC 
          LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($query);
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Изтриване на запитване
if (isset($_POST['delete_inquiry'])) {
    $inquiry_id = (int)$_POST['inquiry_id'];
    $delete_query = "DELETE FROM inquiries WHERE id = :id";
    $delete_stmt = $db->prepare($delete_query);
    $delete_stmt->bindValue(':id', $inquiry_id, PDO::PARAM_INT);
    $delete_stmt->execute();
    header('Location: inquiries.php');
    exit;
}

include 'header.php';
?>

<main class="flex-grow-1">
    <div class="container-xxl py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">Запитвания</h1>
        </div>

        <?php if (empty($inquiries)): ?>
        <div class="alert alert-info">
            Няма намерени запитвания.
        </div>
        <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Име</th>
                                <th>Имейл</th>
                                <th>Телефон</th>
                                <th>Имот</th>
                                <th>Съобщение</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inquiry): ?>
                            <tr>
                                <td><?php echo date('d.m.Y H:i', strtotime($inquiry['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>">
                                        <?php echo htmlspecialchars($inquiry['email']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="tel:<?php echo htmlspecialchars($inquiry['phone']); ?>">
                                        <?php echo htmlspecialchars($inquiry['phone']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if ($inquiry['property_id']): ?>
                                    <a href="../property.php?id=<?php echo $inquiry['property_id']; ?>" target="_blank">
                                        <?php echo htmlspecialchars($inquiry['property_title']); ?>
                                    </a>
                                    <?php else: ?>
                                    <span class="text-muted">Общо запитване</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></td>
                                <td>
                                    <form method="post" class="d-inline" onsubmit="return confirm('Сигурни ли сте, че искате да изтриете това запитване?');">
                                        <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                        <button type="submit" name="delete_inquiry" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'footer.php'; ?> 