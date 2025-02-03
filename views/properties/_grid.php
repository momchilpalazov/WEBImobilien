<?php if (empty($properties)): ?>
    <div class="alert alert-info">
        <?= $translations['property']['no_results'] ?>
    </div>
<?php else: ?>
    <div class="row row-cols-1 row-cols-md-3 g-4 properties-grid">
        <?php foreach ($properties as $property): ?>
            <div class="col">
                <div class="card h-100">
                    <?php if (!empty($property['main_image'])): ?>
                        <img src="/uploads/properties/<?= $property['main_image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($property["title_{$currentLanguage}"]) ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($property["title_{$currentLanguage}"]) ?></h5>
                        <p class="card-text"><?= htmlspecialchars($property["location_{$currentLanguage}"]) ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-primary">
                                <?= $translations['property']['type'][$property['type']] ?>
                            </span>
                            <span class="badge bg-<?= $property['status'] === 'available' ? 'success' : 'warning' ?>">
                                <?= $translations['property']['status'][$property['status']] ?>
                            </span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <div>
                                <i class="bi bi-rulers"></i>
                                <?= number_format($property['area']) ?> m²
                            </div>
                            <div>
                                <strong><?= number_format($property['price']) ?> €</strong>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="/properties/<?= $property['id'] ?>" class="btn btn-primary w-100">
                            <?= $translations['property']['details'] ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?> 
