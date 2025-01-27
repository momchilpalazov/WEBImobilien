<?php if ($property['status']): ?>
    <div class="property-status <?php echo $property['status']; ?>">
        <?php echo $translations[$current_language]['property']['status'][$property['status']]; ?>
    </div>
<?php endif; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8">
            <h1><?php echo htmlspecialchars($property['title_' . $current_language]); ?></h1>
            <!-- Останалата част от кода -->
        </div>
    </div>
</div> 