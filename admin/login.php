<?php
session_start();

// Проверка дали администраторът вече е влязъл
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Проверка за изпратена форма
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Тук може да добавите проверка срещу база данни
    // За демо целите използваме хардкоднати данни
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Невалидно потребителско име или парола';
    }
}
?>

<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в админ панел - Industrial Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <img src="../images/logo.svg" alt="Logo" style="height: 48px;">
                            <h4 class="mt-3">Вход в админ панел</h4>
                        </div>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Потребителско име</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Парола</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Вход</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 