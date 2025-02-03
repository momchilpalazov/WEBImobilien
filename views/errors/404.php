<?php
/**
 * 404 error page
 * @var array $translations
 */

// Start output buffering
ob_start();
?>

<div class="container text-center py-5">
    <h1 class="display-1">404</h1>
    <h2 class="h4 mb-4"><?php echo $translations['errors']['page_not_found'] ?? 'Page Not Found'; ?></h2>
    <p class="mb-4">
        <?php echo $translations['errors']['page_not_found_message'] ?? 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.'; ?>
    </p>
    <a href="/" class="btn btn-primary">
        <?php echo $translations['errors']['back_to_home'] ?? 'Back to Home'; ?>
    </a>
</div>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
require __DIR__ . '/../layouts/main.php';
?> 