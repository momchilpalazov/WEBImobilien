<?php
use App\Database;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_inquiry'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);
    $property_id = isset($_POST['property_id']) ? (int)$_POST['property_id'] : null;
    
    $errors = [];
    
    // Валидация
    if (empty($name)) {
        $errors[] = 'Моля, въведете име';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Моля, въведете валиден имейл адрес';
    }
    
    if (empty($phone)) {
        $errors[] = 'Моля, въведете телефон';
    }
    
    if (empty($message)) {
        $errors[] = 'Моля, въведете съобщение';
    }
    
    if (empty($errors)) {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                INSERT INTO inquiries (property_id, name, email, phone, message)
                VALUES (:property_id, :name, :email, :phone, :message)
            ");
            
            $stmt->execute([
                ':property_id' => $property_id,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':message' => $message
            ]);
            
            $success = 'Благодарим за запитването! Ще се свържем с вас възможно най-скоро.';
            
            // Изчистване на формата
            $name = $email = $phone = $message = '';
        } catch (PDOException $e) {
            $errors[] = 'Възникна грешка при изпращането на запитването. Моля, опитайте отново.';
        }
    }
}
?>

<div class="card shadow-sm">
    <div class="card-body">
        <h3 class="card-title mb-4">Изпратете запитване</h3>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" class="inquiry-form">
            <?php if (isset($property_id)): ?>
                <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
            <?php endif; ?>
            
            <div class="mb-3">
                <label for="name" class="form-label">Име <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Имейл <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="phone" class="form-label">Телефон <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="message" class="form-label">Съобщение <span class="text-danger">*</span></label>
                <textarea class="form-control" id="message" name="message" rows="4" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
            </div>
            
            <button type="submit" name="send_inquiry" class="btn btn-primary">
                <i class="bi bi-send me-2"></i>Изпрати запитване
            </button>
        </form>
    </div>
</div> 