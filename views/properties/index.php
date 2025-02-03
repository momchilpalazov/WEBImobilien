<?php
/**
 * Properties index view
 * @var array $properties
 * @var array $filters
 * @var array $translations
 */

// Start output buffering
ob_start();
?>

<div class="row">
    <!-- Filters Column -->
    <div class="col-lg-3">
        <div class="filter-section bg-light p-4 rounded">
            <h5 class="card-title mb-4"><?php echo $translations['filters']['title']; ?></h5>
            <form action="" method="get" id="filterForm">
                <!-- Property Type -->
                <div class="mb-3">
                    <label class="form-label"><?php echo $translations['filters']['type']['label']; ?></label>
                    <div class="property-types">
                        <?php foreach ($translations['type'] as $key => $value): ?>
                            <div class="form-check">
                                <input type="radio" name="type" value="<?php echo $key; ?>" 
                                       class="form-check-input" id="type_<?php echo $key; ?>"
                                       <?php echo ($filters['type'] ?? '') === $key ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="type_<?php echo $key; ?>">
                                    <?php echo $value; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Property Status -->
                <div class="mb-3">
                    <label class="form-label"><?php echo $translations['filters']['status']['label']; ?></label>
                    <select name="status" class="form-select">
                        <option value=""><?php echo $translations['status']['all']; ?></option>
                        <?php foreach ($translations['status'] as $key => $value): ?>
                            <?php if ($key !== 'all'): ?>
                                <option value="<?php echo $key; ?>" 
                                        <?php echo ($filters['status'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo $value; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Area Range -->
                <div class="mb-3">
                    <label class="form-label"><?php echo $translations['filters']['area']['label']; ?></label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" name="min_area" class="form-control" 
                                   placeholder="<?php echo $translations['filters']['area']['min']; ?>"
                                   value="<?php echo $filters['min_area'] ?? ''; ?>">
                        </div>
                        <div class="col-6">
                            <input type="number" name="max_area" class="form-control" 
                                   placeholder="<?php echo $translations['filters']['area']['max']; ?>"
                                   value="<?php echo $filters['max_area'] ?? ''; ?>">
                        </div>
                    </div>
                </div>

                <!-- Price Range -->
                <div class="mb-3">
                    <label class="form-label"><?php echo $translations['filters']['price']['label']; ?></label>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" name="min_price" class="form-control" 
                                   placeholder="<?php echo $translations['filters']['price']['min']; ?>"
                                   value="<?php echo $filters['min_price'] ?? ''; ?>">
                        </div>
                        <div class="col-6">
                            <input type="number" name="max_price" class="form-control" 
                                   placeholder="<?php echo $translations['filters']['price']['max']; ?>"
                                   value="<?php echo $filters['max_price'] ?? ''; ?>">
                        </div>
                    </div>
                </div>

                <!-- Sort -->
                <div class="mb-4">
                    <label class="form-label"><?php echo $translations['sort']['title']; ?></label>
                    <select name="sort" class="form-select">
                        <?php
                        $sort_options = [
                            'newest' => $translations['sort']['newest'],
                            'price_asc' => $translations['sort']['price_asc'],
                            'price_desc' => $translations['sort']['price_desc'],
                            'area_asc' => $translations['sort']['area_asc'],
                            'area_desc' => $translations['sort']['area_desc']
                        ];
                        foreach ($sort_options as $key => $value):
                        ?>
                            <option value="<?php echo $key; ?>" 
                                    <?php echo ($filters['sort'] ?? 'newest') === $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?php echo $translations['filters']['apply']; ?>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                        <?php echo $translations['filters']['clear']; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Properties Grid Column -->
    <div class="col-lg-9">
        <?php if (empty($properties)): ?>
            <div class="alert alert-info">
                <?php echo $translations['list']['no_results']; ?>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($properties as $property): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card property-card h-100">
                            <?php if ($property['status']): ?>
                                <div class="property-status <?php echo $property['status']; ?>">
                                    <?php echo $translations['status'][$property['status']]; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="position-relative">
                                <img src="<?php echo $property['image_path'] ? 'uploads/properties/' . $property['image_path'] : 'images/no-image.jpg'; ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($property["title_{$currentLanguage}"]); ?>">
                                
                                <?php if (!empty($property['pdf_flyer'])): ?>
                                    <a href="uploads/flyers/<?php echo htmlspecialchars($property['pdf_flyer']); ?>" 
                                       target="_blank" 
                                       class="pdf-flyer-link">
                                        <i class="fas fa-file-pdf"></i> 
                                        <?php 
                                        $pdf_text = [
                                            'bg' => 'Виж експозе',
                                            'en' => 'View brochure',
                                            'de' => 'Exposé ansehen',
                                            'ru' => 'Смотреть брошюру'
                                        ];
                                        echo $pdf_text[$currentLanguage] ?? $pdf_text['en'];
                                        ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($property["title_{$currentLanguage}"]); ?>
                                </h5>
                                <div class="property-features">
                                    <div class="feature">
                                        <i class="bi bi-rulers me-2"></i>
                                        <?php echo number_format($property['area']); ?> m²
                                    </div>
                                    <div class="feature">
                                        <i class="bi bi-geo-alt me-2"></i>
                                        <?php echo htmlspecialchars($property["location_{$currentLanguage}"]); ?>
                                    </div>
                                    <div class="feature">
                                        <i class="bi bi-building me-2"></i>
                                        <?php echo $translations['type'][$property['type']]; ?>
                                    </div>
                                    <div class="feature">
                                        <i class="bi bi-currency-euro me-2"></i>
                                        <?php echo number_format($property['price']); ?>
                                    </div>
                                </div>
                                <a href="/properties/<?php echo $property['id']; ?>" 
                                   class="btn btn-outline-primary mt-3 w-100">
                                    <?php echo $translations['details']['title']; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.getElementById('clearFilters').addEventListener('click', function() {
    window.location.href = '/properties';
});
</script>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
require __DIR__ . '/../layouts/main.php';
?> 