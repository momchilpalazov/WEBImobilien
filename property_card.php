<?php
$current_language = getCurrentLanguage();
error_log("Property Card - Property ID: " . $property['id'] . ", Status: " . $property['status']);
error_log("Property Card - Current Language: " . $current_language);
error_log("Property Card - Status Translation: " . print_r($translations[$current_language]['property']['status'], true));
error_log("Property Card - PDF Flyer: " . ($property['pdf_flyer'] ?? 'Not set'));
?>
<div class="card h-100 property-card">
    <div class="position-relative">
        <img src="uploads/properties/<?php echo htmlspecialchars($property['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($property['title_' . $current_language]); ?>">
        <?php if (!empty($property['status'])): ?>
            <?php error_log("Property Card - Status is not empty: " . $property['status']); ?>
            <div class="property-status <?php echo htmlspecialchars($property['status']); ?>">
                <?php 
                $status_translation = $translations[$current_language]['property']['status'][$property['status']] ?? 'Unknown';
                error_log("Property Card - Status Translation Result: " . $status_translation);
                echo $status_translation;
                ?>
            </div>
        <?php else: ?>
            <?php error_log("Property Card - Status is empty"); ?>
        <?php endif; ?>
        <?php if (isset($property['pdf_flyer']) && !empty(trim($property['pdf_flyer']))): ?>
            <?php error_log("Property Card - Adding PDF link for property: " . $property['id']); ?>
            <a href="uploads/flyers/<?php echo htmlspecialchars($property['pdf_flyer']); ?>" target="_blank" class="pdf-flyer-link">
                <i class="fas fa-file-pdf"></i> 
                <?php 
                $pdf_text = [
                    'bg' => 'Виж експозе',
                    'en' => 'View brochure',
                    'de' => 'Exposé ansehen',
                    'ru' => 'Смотреть брошюру'
                ];
                echo $pdf_text[$current_language] ?? $pdf_text['en'];
                ?>
            </a>
        <?php else: ?>
            <?php error_log("Property Card - No PDF flyer for property: " . $property['id']); ?>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <h5 class="card-title"><?php echo htmlspecialchars($property['title_' . $current_language]); ?></h5>
        <p class="card-text">
            <strong><?php echo number_format($property['price'], 0, '.', ' '); ?> €</strong> | 
            <?php echo number_format($property['area'], 0, '.', ' '); ?> м²
        </p>
        <p class="card-text">
            <small class="text-muted">
                <i class="fas fa-map-marker-alt"></i> 
                <?php echo htmlspecialchars($property['location_' . $current_language]); ?>
            </small>
        </p>
    </div>
</div>

<style>
.property-image-container {
    position: relative;
    overflow: hidden;
}

.property-image {
    width: 100%;
    height: auto;
    transition: transform 0.3s ease;
}

.property-status {
    position: absolute;
    top: 10px;
    right: 10px;
    padding: 5px 10px;
    border-radius: 4px;
    color: white;
    font-weight: 500;
    z-index: 1;
}

.property-status.available {
    background-color: #28a745;
}

.property-status.reserved {
    background-color: #ffc107;
    color: #000;
}

.property-status.rented {
    background-color: #dc3545;
}

.property-status.sold {
    background-color: #dc3545;
}

.pdf-flyer-link {
    position: absolute;
    bottom: 15px;
    right: 15px;
    background-color: rgba(255, 255, 255, 0.95);
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    color: #333;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 2;
}

.pdf-flyer-link:hover {
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
    transform: translateY(-2px);
    color: #333;
    text-decoration: none;
}

.pdf-flyer-link i {
    color: #dc3545;
    font-size: 1.1rem;
}
</style> 