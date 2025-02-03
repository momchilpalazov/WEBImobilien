<?php
require_once 'includes/header.php';
use App\Database;

$db = Database::getInstance()->getConnection();
$service = null;

// Ако редактираме съществуваща услуга
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service = $stmt->fetch();
}

// Обработка на формата
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = [
            'type' => $_POST['type'],
            'title_bg' => $_POST['title_bg'],
            'title_de' => $_POST['title_de'],
            'title_ru' => $_POST['title_ru'],
            'description_bg' => $_POST['description_bg'],
            'description_de' => $_POST['description_de'],
            'description_ru' => $_POST['description_ru'],
            'active' => isset($_POST['active']) ? 1 : 0
        ];
        
        if (isset($_GET['id'])) {
            // Обновяване на съществуваща услуга
            $sql = "UPDATE services SET 
                    type = :type,
                    title_bg = :title_bg, title_de = :title_de, title_ru = :title_ru,
                    description_bg = :description_bg, description_de = :description_de, description_ru = :description_ru,
                    active = :active
                    WHERE id = :id";
            $data['id'] = $_GET['id'];
            
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
        } else {
            // Добавяне на нова услуга
            $sql = "INSERT INTO services 
                    (type, title_bg, title_de, title_ru, description_bg, description_de, description_ru, active)
                    VALUES 
                    (:type, :title_bg, :title_de, :title_ru, :description_bg, :description_de, :description_ru, :active)";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($data);
            
            // Пренасочване към редактиране на новосъздадената услуга
            header("Location: service-edit.php?id=" . $db->lastInsertId());
            exit;
        }
        
        $success = "Услугата беше успешно " . (isset($_GET['id']) ? "обновена" : "добавена");
    } catch (Exception $e) {
        $error = "Възникна грешка: " . $e->getMessage();
    }
}
?>

<div class="service-edit-page">
    <div class="page-header">
        <h1><?php echo isset($_GET['id']) ? 'Редактиране на услуга' : 'Добавяне на нова услуга'; ?></h1>
        <a href="services.php" class="btn btn-secondary">Назад към списъка</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" class="service-form">
        <!-- Основна информация -->
        <div class="form-section">
            <h2>Основна информация</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Тип услуга</label>
                    <select name="type" id="type" required>
                        <option value="company_registration" <?php echo isset($service) && $service['type'] === 'company_registration' ? 'selected' : ''; ?>>Регистрация на фирми</option>
                        <option value="recruitment" <?php echo isset($service) && $service['type'] === 'recruitment' ? 'selected' : ''; ?>>Подбор на персонал</option>
                        <option value="consulting" <?php echo isset($service) && $service['type'] === 'consulting' ? 'selected' : ''; ?>>Консултации</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="active" value="1" 
                               <?php echo !isset($service) || $service['active'] ? 'checked' : ''; ?>>
                        Активна услуга
                    </label>
                </div>
            </div>
        </div>

        <!-- Многоезично съдържание -->
        <div class="form-section">
            <h2>Съдържание</h2>
            
            <!-- Табове за езици -->
            <div class="language-tabs">
                <button type="button" class="tab-btn active" data-lang="bg">Български</button>
                <button type="button" class="tab-btn" data-lang="de">Deutsch</button>
                <button type="button" class="tab-btn" data-lang="ru">Русский</button>
            </div>

            <!-- Съдържание за български -->
            <div class="tab-content active" data-lang="bg">
                <div class="form-group">
                    <label for="title_bg">Заглавие (BG)</label>
                    <input type="text" name="title_bg" id="title_bg" required
                           value="<?php echo isset($service) ? $service['title_bg'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description_bg">Описание (BG)</label>
                    <textarea name="description_bg" id="description_bg" class="tinymce" required>
                        <?php echo isset($service) ? $service['description_bg'] : ''; ?>
                    </textarea>
                </div>
            </div>

            <!-- Съдържание за немски -->
            <div class="tab-content" data-lang="de">
                <div class="form-group">
                    <label for="title_de">Заглавие (DE)</label>
                    <input type="text" name="title_de" id="title_de" required
                           value="<?php echo isset($service) ? $service['title_de'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description_de">Описание (DE)</label>
                    <textarea name="description_de" id="description_de" class="tinymce" required>
                        <?php echo isset($service) ? $service['description_de'] : ''; ?>
                    </textarea>
                </div>
            </div>

            <!-- Съдържание за руски -->
            <div class="tab-content" data-lang="ru">
                <div class="form-group">
                    <label for="title_ru">Заглавие (RU)</label>
                    <input type="text" name="title_ru" id="title_ru" required
                           value="<?php echo isset($service) ? $service['title_ru'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description_ru">Описание (RU)</label>
                    <textarea name="description_ru" id="description_ru" class="tinymce" required>
                        <?php echo isset($service) ? $service['description_ru'] : ''; ?>
                    </textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <?php echo isset($_GET['id']) ? 'Запази промените' : 'Добави услуга'; ?>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация на TinyMCE
    tinymce.init({
        selector: '.tinymce',
        height: 300,
        plugins: 'lists link table',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link'
    });

    // Табове
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const lang = this.dataset.lang;
            
            // Активиране на таба
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Показване на съответното съдържание
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
                if (content.dataset.lang === lang) {
                    content.classList.add('active');
                }
            });
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 