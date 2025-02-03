<?php
/**
 * Login form view
 * @var array $translations
 */
?>

<!DOCTYPE html>
<html lang="<?php echo $currentLanguage; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $translations['auth']['login']; ?> - <?php echo $translations['site_name']; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="/css/admin.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <h1 class="h4"><?php echo $translations['auth']['login']; ?></h1>
                            <p class="text-muted"><?php echo $translations['auth']['login_text']; ?></p>
                        </div>

                        <?php if (isset($_SESSION['errors']['auth'])): ?>
                            <div class="alert alert-danger">
                                <?php echo $_SESSION['errors']['auth']; ?>
                            </div>
                        <?php endif; ?>

                        <form action="/admin/login" method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <?php echo $translations['auth']['email']; ?>
                                </label>
                                <input type="email" 
                                       class="form-control <?php echo isset($_SESSION['errors']['email']) ? 'is-invalid' : ''; ?>" 
                                       id="email" 
                                       name="email"
                                       value="<?php echo $_SESSION['old']['email'] ?? ''; ?>"
                                       required>
                                <?php if (isset($_SESSION['errors']['email'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $_SESSION['errors']['email']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <?php echo $translations['auth']['password']; ?>
                                </label>
                                <input type="password" 
                                       class="form-control <?php echo isset($_SESSION['errors']['password']) ? 'is-invalid' : ''; ?>" 
                                       id="password" 
                                       name="password"
                                       required>
                                <?php if (isset($_SESSION['errors']['password'])): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $_SESSION['errors']['password']; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <?php echo $translations['auth']['login_button']; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Clear session data
unset($_SESSION['errors']);
unset($_SESSION['old']);
?> 