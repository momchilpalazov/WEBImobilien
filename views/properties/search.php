<?php include_once '../layouts/header.php'; ?>

<div class="container-fluid px-4">
    <!-- Изглед превключвател -->
    <div class="mb-4">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary active" data-view="grid">
                <i class="fas fa-th me-2"></i>Грид
            </button>
            <button type="button" class="btn btn-outline-primary" data-view="map">
                <i class="fas fa-map-marked-alt me-2"></i>Карта
            </button>
            <button type="button" class="btn btn-outline-primary" data-view="list">
                <i class="fas fa-list me-2"></i>Списък
            </button>
        </div>
        
        <div class="float-end">
            <button type="button" class="btn btn-outline-primary" id="compareBtn" disabled>
                <i class="fas fa-balance-scale me-2"></i>
                Сравни (<span id="compareCount">0</span>)
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Филтри -->
        <div class="col-md-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Филтри</h5>
                    <button class="btn btn-link btn-sm d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
                <div class="card-body collapse d-md-block" id="filterCollapse">
                    <form id="searchForm" action="/properties/search" method="GET">
                        <!-- Бързи филтри -->
                        <div class="mb-4">
                            <label class="form-label">Бързи филтри</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary quick-filter" 
                                        data-price-min="0" data-price-max="50000">
                                    До 50,000€
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary quick-filter"
                                        data-price-min="50000" data-price-max="100000">
                                    50-100,000€
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary quick-filter"
                                        data-area-min="60" data-area-max="90">
                                    60-90 m²
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary quick-filter"
                                        data-area-min="90" data-area-max="120">
                                    90-120 m²
                                </button>
                            </div>
                        </div>

                        <!-- Тип имот -->
                        <div class="mb-3">
                            <label class="form-label">Тип имот</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="property_type[]" value="apartment" 
                                       <?= in_array('apartment', $criteria['property_type'] ?? []) ? 'checked' : '' ?>>
                                <label class="form-check-label">Апартамент</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="property_type[]" value="house"
                                       <?= in_array('house', $criteria['property_type'] ?? []) ? 'checked' : '' ?>>
                                <label class="form-check-label">Къща</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="property_type[]" value="office"
                                       <?= in_array('office', $criteria['property_type'] ?? []) ? 'checked' : '' ?>>
                                <label class="form-check-label">Офис</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="property_type[]" value="land"
                                       <?= in_array('land', $criteria['property_type'] ?? []) ? 'checked' : '' ?>>
                                <label class="form-check-label">Парцел</label>
                            </div>
                        </div>

                        <!-- Тип сделка -->
                        <div class="mb-3">
                            <label class="form-label">Тип сделка</label>
                            <select class="form-select" name="transaction_type">
                                <option value="any" <?= ($criteria['transaction_type'] ?? '') === 'any' ? 'selected' : '' ?>>Без значение</option>
                                <option value="sale" <?= ($criteria['transaction_type'] ?? '') === 'sale' ? 'selected' : '' ?>>Продажба</option>
                                <option value="rent" <?= ($criteria['transaction_type'] ?? '') === 'rent' ? 'selected' : '' ?>>Наем</option>
                            </select>
                        </div>

                        <!-- Цена -->
                        <div class="mb-3">
                            <label class="form-label">Цена (€)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="price_min" placeholder="От"
                                           value="<?= $criteria['price_min'] ?? '' ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="price_max" placeholder="До"
                                           value="<?= $criteria['price_max'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Площ -->
                        <div class="mb-3">
                            <label class="form-label">Площ (m²)</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="area_min" placeholder="От"
                                           value="<?= $criteria['area_min'] ?? '' ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="area_max" placeholder="До"
                                           value="<?= $criteria['area_max'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Стаи -->
                        <div class="mb-3">
                            <label class="form-label">Брой стаи</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="rooms_min" placeholder="От"
                                           value="<?= $criteria['rooms_min'] ?? '' ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="rooms_max" placeholder="До"
                                           value="<?= $criteria['rooms_max'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Етаж -->
                        <div class="mb-3">
                            <label class="form-label">Етаж</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="floor_min" placeholder="От"
                                           value="<?= $criteria['floor_min'] ?? '' ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="floor_max" placeholder="До"
                                           value="<?= $criteria['floor_max'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Година на строителство -->
                        <div class="mb-3">
                            <label class="form-label">Година на строителство</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control" name="construction_year_min" placeholder="От"
                                           value="<?= $criteria['construction_year_min'] ?? '' ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control" name="construction_year_max" placeholder="До"
                                           value="<?= $criteria['construction_year_max'] ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Локации -->
                        <div class="mb-3">
                            <label class="form-label">Локации</label>
                            <select class="form-select" name="locations[]" multiple>
                                <?php foreach ($locations as $location): ?>
                                    <option value="<?= $location['id'] ?>"
                                            <?= in_array($location['id'], $criteria['locations'] ?? []) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($location['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Характеристики -->
                        <div class="mb-3">
                            <label class="form-label">Характеристики</label>
                            <?php foreach ($features as $feature): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="features[]" 
                                           value="<?= $feature['id'] ?>"
                                           <?= in_array($feature['id'], $criteria['features'] ?? []) ? 'checked' : '' ?>>
                                    <label class="form-check-label"><?= htmlspecialchars($feature['name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Паркинг -->
                        <div class="mb-3">
                            <label class="form-label">Паркинг</label>
                            <select class="form-select" name="parking">
                                <option value="">Без значение</option>
                                <option value="garage" <?= ($criteria['parking'] ?? '') === 'garage' ? 'selected' : '' ?>>Гараж</option>
                                <option value="parking" <?= ($criteria['parking'] ?? '') === 'parking' ? 'selected' : '' ?>>Паркомясто</option>
                            </select>
                        </div>

                        <!-- Отопление -->
                        <div class="mb-3">
                            <label class="form-label">Отопление</label>
                            <select class="form-select" name="heating">
                                <option value="">Без значение</option>
                                <option value="tec" <?= ($criteria['heating'] ?? '') === 'tec' ? 'selected' : '' ?>>ТЕЦ</option>
                                <option value="gas" <?= ($criteria['heating'] ?? '') === 'gas' ? 'selected' : '' ?>>Газ</option>
                                <option value="electric" <?= ($criteria['heating'] ?? '') === 'electric' ? 'selected' : '' ?>>Ток</option>
                            </select>
                        </div>

                        <!-- Обзавеждане -->
                        <div class="mb-3">
                            <label class="form-label">Обзавеждане</label>
                            <select class="form-select" name="furnishing">
                                <option value="">Без значение</option>
                                <option value="furnished" <?= ($criteria['furnishing'] ?? '') === 'furnished' ? 'selected' : '' ?>>Обзаведен</option>
                                <option value="semi" <?= ($criteria['furnishing'] ?? '') === 'semi' ? 'selected' : '' ?>>Частично обзаведен</option>
                                <option value="unfurnished" <?= ($criteria['furnishing'] ?? '') === 'unfurnished' ? 'selected' : '' ?>>Необзаведен</option>
                            </select>
                        </div>

                        <!-- Ключови думи -->
                        <div class="mb-3">
                            <label class="form-label">Ключови думи</label>
                            <input type="text" class="form-control" name="keywords" 
                                   value="<?= htmlspecialchars($criteria['keywords'] ?? '') ?>"
                                   placeholder="Търсене в описанието...">
                        </div>

                        <!-- Сортиране -->
                        <div class="mb-3">
                            <label class="form-label">Сортиране</label>
                            <select class="form-select" name="sort_field">
                                <option value="created_at" <?= ($criteria['sort_field'] ?? '') === 'created_at' ? 'selected' : '' ?>>Най-нови</option>
                                <option value="price_asc" <?= ($criteria['sort_field'] ?? '') === 'price_asc' ? 'selected' : '' ?>>Цена (възх.)</option>
                                <option value="price_desc" <?= ($criteria['sort_field'] ?? '') === 'price_desc' ? 'selected' : '' ?>>Цена (низх.)</option>
                                <option value="area_asc" <?= ($criteria['sort_field'] ?? '') === 'area_asc' ? 'selected' : '' ?>>Площ (възх.)</option>
                                <option value="area_desc" <?= ($criteria['sort_field'] ?? '') === 'area_desc' ? 'selected' : '' ?>>Площ (низх.)</option>
                            </select>
                        </div>

                        <?php if (isset($_SESSION['user_id'])): ?>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="save_search" value="1"
                                           <?= ($criteria['save_search'] ?? false) ? 'checked' : '' ?>>
                                    <label class="form-check-label">
                                        Запази търсенето
                                    </label>
                                </div>
                                <?php if (isset($criteria['save_search']) && $criteria['save_search']): ?>
                                    <input type="text" class="form-control mt-2" name="search_name"
                                           value="<?= htmlspecialchars($criteria['search_name'] ?? '') ?>"
                                           placeholder="Име на търсенето">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Допълнителни филтри -->
                        <div class="mb-3">
                            <button class="btn btn-link btn-sm p-0" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                                <i class="fas fa-cog me-2"></i>
                                Допълнителни филтри
                            </button>
                            <div class="collapse mt-3" id="advancedFilters">
                                <!-- Изложение -->
                                <div class="mb-3">
                                    <label class="form-label">Изложение</label>
                                    <div class="btn-group d-flex flex-wrap" role="group">
                                        <input type="checkbox" class="btn-check" name="exposure[]" value="N" 
                                               id="exposure-n" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="exposure-n">С</label>
                                        
                                        <input type="checkbox" class="btn-check" name="exposure[]" value="E"
                                               id="exposure-e" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="exposure-e">И</label>
                                        
                                        <input type="checkbox" class="btn-check" name="exposure[]" value="S"
                                               id="exposure-s" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="exposure-s">Ю</label>
                                        
                                        <input type="checkbox" class="btn-check" name="exposure[]" value="W"
                                               id="exposure-w" autocomplete="off">
                                        <label class="btn btn-outline-secondary" for="exposure-w">З</label>
                                    </div>
                                </div>

                                <!-- Последен етаж -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="not_last_floor" value="1"
                                               id="notLastFloor">
                                        <label class="form-check-label" for="notLastFloor">
                                            Без последен етаж
                                        </label>
                                    </div>
                                </div>

                                <!-- Асансьор -->
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="has_elevator" value="1"
                                               id="hasElevator">
                                        <label class="form-check-label" for="hasElevator">
                                            Със асансьор
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>
                                Търси
                            </button>
                            <button type="button" class="btn btn-secondary" id="clearFilters">
                                <i class="fas fa-times me-2"></i>
                                Изчисти филтрите
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Резултати -->
        <div class="col-md-9">
            <!-- Toolbar -->
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        Намерени <?= count($results) ?> имота
                        <?php if (!empty($criteria)): ?>
                            <button class="btn btn-link btn-sm text-decoration-none" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#activeFilters">
                                <i class="fas fa-filter me-1"></i>
                                Активни филтри
                            </button>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="d-flex gap-2">
                    <select class="form-select form-select-sm" style="width: auto;" name="per_page">
                        <option value="20">20 на страница</option>
                        <option value="40">40 на страница</option>
                        <option value="60">60 на страница</option>
                    </select>
                </div>
            </div>

            <!-- Активни филтри -->
            <?php if (!empty($criteria)): ?>
                <div class="collapse mb-3" id="activeFilters">
                    <div class="card card-body">
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($criteria as $key => $value): ?>
                                <?php if (!empty($value) && $key !== 'page' && $key !== 'per_page'): ?>
                                    <span class="badge bg-primary">
                                        <?= htmlspecialchars(formatFilterLabel($key, $value)) ?>
                                        <a href="#" class="text-white ms-2 text-decoration-none remove-filter" 
                                           data-filter="<?= $key ?>">×</a>
                                    </span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Grid View -->
            <div class="view-content" id="gridView">
                <div class="row g-4">
                    <?php foreach ($results as $property): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 property-card">
                                <div class="card-img-wrapper position-relative">
                                    <?php if (!empty($property['images'])): ?>
                                        <img src="<?= $property['images'][0]['url'] ?>" 
                                             class="card-img-top" 
                                             alt="<?= htmlspecialchars($property['title']) ?>"
                                             style="height: 200px; object-fit: cover;">
                                        
                                        <?php if (count($property['images']) > 1): ?>
                                            <div class="image-counter">
                                                <i class="fas fa-images me-1"></i>
                                                <?= count($property['images']) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <!-- Quick View Button -->
                                    <button class="btn btn-sm btn-primary position-absolute quick-view-btn"
                                            data-property-id="<?= $property['id'] ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <!-- Compare Checkbox -->
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <div class="form-check">
                                            <input class="form-check-input compare-checkbox" type="checkbox" 
                                                   value="<?= $property['id'] ?>" 
                                                   id="compare<?= $property['id'] ?>">
                                            <label class="form-check-label" for="compare<?= $property['id'] ?>">
                                                Сравни
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="/properties/<?= $property['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars($property['title']) ?>
                                        </a>
                                    </h5>
                                    
                                    <p class="card-text text-muted mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars($property['location']['name']) ?>
                                    </p>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="h5 text-primary mb-0"><?= number_format($property['price']) ?> €</span>
                                        <small class="text-muted"><?= number_format($property['price_per_sqm']) ?> €/m²</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <span class="badge bg-secondary me-1"><?= $property['area'] ?> m²</span>
                                        <?php if (isset($property['rooms'])): ?>
                                            <span class="badge bg-secondary me-1"><?= $property['rooms'] ?> стаи</span>
                                        <?php endif; ?>
                                        <?php if (isset($property['floor'])): ?>
                                            <span class="badge bg-secondary me-1"><?= $property['floor'] ?> етаж</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if (!empty($property['features'])): ?>
                                        <div class="mb-3">
                                            <?php foreach (array_slice($property['features'], 0, 3) as $feature): ?>
                                                <span class="badge bg-info me-1"><?= htmlspecialchars($feature['name']) ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($property['features']) > 3): ?>
                                                <span class="badge bg-info">+<?= count($property['features']) - 3 ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($property['nearby'])): ?>
                                        <div class="small text-muted mb-3">
                                            <strong>Наблизо:</strong>
                                            <?php foreach (array_slice($property['nearby'], 0, 2) as $place): ?>
                                                <span class="me-2"><?= htmlspecialchars($place['name']) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-grid">
                                        <a href="/properties/<?= $property['id'] ?>" class="btn btn-outline-primary">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Детайли
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Map View -->
            <div class="view-content d-none" id="mapView">
                <div id="propertyMap" style="height: 600px;"></div>
            </div>

            <!-- List View -->
            <div class="view-content d-none" id="listView">
                <div class="list-group">
                    <?php foreach ($results as $property): ?>
                        <a href="/properties/<?= $property['id'] ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <?php if (!empty($property['images'])): ?>
                                        <img src="<?= $property['images'][0]['url'] ?>" 
                                             class="img-fluid rounded" 
                                             alt="<?= htmlspecialchars($property['title']) ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-9">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="mb-1"><?= htmlspecialchars($property['title']) ?></h5>
                                        <span class="h5 text-primary"><?= number_format($property['price']) ?> €</span>
                                    </div>
                                    <p class="mb-1">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        <?= htmlspecialchars($property['location']['name']) ?>
                                    </p>
                                    <div class="small text-muted">
                                        <?= $property['area'] ?> m² • 
                                        <?= isset($property['rooms']) ? $property['rooms'] . ' стаи • ' : '' ?>
                                        <?= isset($property['floor']) ? $property['floor'] . ' етаж' : '' ?>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($criteria, ['page' => $current_page - 1])) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($criteria, ['page' => $i])) ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($criteria, ['page' => $current_page + 1])) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Quick View Modal -->
<div class="modal fade" id="quickViewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Бърз преглед</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center">
                    <div class="spinner-border text-primary"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Compare Modal -->
<div class="modal fade" id="compareModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Сравнение на имоти</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Характеристика</th>
                                <!-- Dynamic headers for properties -->
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic comparison content -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Property Card Template -->
<template id="propertyCardTemplate">
    <div class="card h-100 property-card">
        <div class="card-img-wrapper position-relative">
            <div class="property-images position-relative">
                <img src="" class="card-img-top main-image" alt="" style="height: 200px; object-fit: cover;">
                <div class="image-navigation d-none">
                    <button class="btn btn-sm btn-light prev-image">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="btn btn-sm btn-light next-image">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <span class="image-counter badge bg-dark position-absolute bottom-0 end-0 m-2">
                        <i class="fas fa-images me-1"></i>
                        <span class="current">1</span>/<span class="total"></span>
                    </span>
                </div>
            </div>
            
            <div class="property-badges position-absolute top-0 start-0 m-2">
                <span class="badge bg-primary mb-1 d-block property-type"></span>
                <span class="badge bg-success mb-1 d-block transaction-type"></span>
                <span class="badge bg-info mb-1 d-block" data-bs-toggle="tooltip" title="Дата на публикуване">
                    <i class="fas fa-clock me-1"></i>
                    <span class="published-date"></span>
                </span>
            </div>
            
            <div class="property-actions position-absolute top-0 end-0 m-2">
                <div class="btn-group">
                    <button class="btn btn-sm btn-light quick-view-btn" data-bs-toggle="tooltip" title="Бърз преглед">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-light compare-btn" data-bs-toggle="tooltip" title="Добави за сравнение">
                        <i class="fas fa-balance-scale"></i>
                    </button>
                    <button class="btn btn-sm btn-light favorite-btn" data-bs-toggle="tooltip" title="Добави в любими">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </div>
            
            <!-- Energy Rating Badge -->
            <div class="energy-rating position-absolute bottom-0 start-0 m-2">
                <span class="badge" data-energy-rating="">
                    <i class="fas fa-bolt me-1"></i>
                    <span class="rating-value"></span>
                </span>
            </div>
            
            <!-- Share Menu -->
            <div class="dropdown share-menu position-absolute bottom-0 end-0 m-2">
                <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                    <i class="fas fa-share-alt"></i>
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item share-whatsapp" href="#"><i class="fab fa-whatsapp me-2"></i>WhatsApp</a>
                    <a class="dropdown-item share-viber" href="#"><i class="fab fa-viber me-2"></i>Viber</a>
                    <a class="dropdown-item share-email" href="#"><i class="fas fa-envelope me-2"></i>Email</a>
                    <a class="dropdown-item copy-link" href="#"><i class="fas fa-link me-2"></i>Копирай линк</a>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title mb-0">
                    <a href="" class="text-decoration-none property-link">
                        <span class="property-title"></span>
                    </a>
                </h5>
                <span class="h5 text-primary mb-0 property-price"></span>
            </div>
            
            <p class="card-text text-muted mb-2">
                <i class="fas fa-map-marker-alt me-1"></i>
                <span class="property-location"></span>
            </p>
            
            <div class="property-details mb-3">
                <div class="row g-2 text-center">
                    <div class="col-4">
                        <div class="p-2 border rounded">
                            <small class="d-block text-muted">Площ</small>
                            <span class="property-area"></span> m²
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded">
                            <small class="d-block text-muted">Стаи</small>
                            <span class="property-rooms"></span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border rounded">
                            <small class="d-block text-muted">Етаж</small>
                            <span class="property-floor"></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="property-features mb-3">
                <div class="d-flex flex-wrap gap-1 features-list"></div>
            </div>
            
            <div class="property-footer d-flex justify-content-between align-items-center">
                <small class="text-muted price-per-sqm"></small>
                <a href="" class="btn btn-sm btn-outline-primary details-link">
                    <i class="fas fa-info-circle me-1"></i>
                    Детайли
                </a>
            </div>
        </div>
    </div>
</template>

<!-- Map Controls Template -->
<template id="mapControlsTemplate">
    <div class="map-controls bg-white p-3 rounded shadow-sm">
        <div class="mb-3">
            <label class="form-label">Радиус на търсене</label>
            <input type="range" class="form-range" id="searchRadius" min="500" max="5000" step="500" value="1000">
            <div class="d-flex justify-content-between">
                <small>500m</small>
                <small class="radius-value">1000m</small>
                <small>5km</small>
            </div>
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="showHeatmap">
                <label class="form-check-label" for="showHeatmap">
                    Покажи топлинна карта на цените
                </label>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Близки обекти</label>
            <div class="btn-group d-flex flex-wrap" role="group">
                <input type="checkbox" class="btn-check" id="showSchools" autocomplete="off">
                <label class="btn btn-outline-secondary" for="showSchools">
                    <i class="fas fa-school"></i>
                </label>
                
                <input type="checkbox" class="btn-check" id="showTransport" autocomplete="off">
                <label class="btn btn-outline-secondary" for="showTransport">
                    <i class="fas fa-bus"></i>
                </label>
                
                <input type="checkbox" class="btn-check" id="showShops" autocomplete="off">
                <label class="btn btn-outline-secondary" for="showShops">
                    <i class="fas fa-shopping-cart"></i>
                </label>
                
                <input type="checkbox" class="btn-check" id="showParks" autocomplete="off">
                <label class="btn btn-outline-secondary" for="showParks">
                    <i class="fas fa-tree"></i>
                </label>
            </div>
        </div>
        
        <div class="selected-area-info d-none">
            <hr>
            <h6>Избран район</h6>
            <p class="mb-2">
                <strong>Среден брой имоти:</strong>
                <span class="area-properties-count"></span>
            </p>
            <p class="mb-2">
                <strong>Средна цена:</strong>
                <span class="area-avg-price"></span>
            </p>
            <p class="mb-0">
                <strong>Тенденция:</strong>
                <span class="area-trend"></span>
            </p>
        </div>
        
        <!-- Draw Area Controls -->
        <div class="mb-3">
            <label class="form-label">Очертай район</label>
            <div class="btn-group w-100">
                <button class="btn btn-outline-primary" id="startDrawing">
                    <i class="fas fa-draw-polygon me-2"></i>Очертай
                </button>
                <button class="btn btn-outline-danger" id="clearDrawing" disabled>
                    <i class="fas fa-times me-2"></i>Изчисти
                </button>
            </div>
        </div>
        
        <!-- Transit Time -->
        <div class="mb-3">
            <label class="form-label">Време за път до</label>
            <div class="input-group mb-2">
                <input type="text" class="form-control" id="transitDestination" placeholder="Въведи адрес">
                <button class="btn btn-outline-secondary" id="showTransitTimes">
                    <i class="fas fa-route"></i>
                </button>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="transitMode" id="transitCar" value="car" checked>
                <label class="form-check-label" for="transitCar"><i class="fas fa-car"></i></label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="transitMode" id="transitTransit" value="transit">
                <label class="form-check-label" for="transitTransit"><i class="fas fa-bus"></i></label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="transitMode" id="transitWalk" value="walk">
                <label class="form-check-label" for="transitWalk"><i class="fas fa-walking"></i></label>
            </div>
        </div>
        
        <!-- Saved Areas -->
        <div class="mb-3">
            <label class="form-label d-flex justify-content-between align-items-center">
                Запазени райони
                <button class="btn btn-sm btn-outline-primary" id="saveCurrentArea" disabled>
                    <i class="fas fa-save"></i>
                </button>
            </label>
            <select class="form-select" id="savedAreas" size="3">
                <!-- Dynamic content -->
            </select>
        </div>
        
        <!-- Разширена статистика за района -->
        <div class="selected-area-stats d-none">
            <hr>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Статистика за района</h6>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active" data-period="1m">1м</button>
                    <button class="btn btn-outline-secondary" data-period="3m">3м</button>
                    <button class="btn btn-outline-secondary" data-period="6m">6м</button>
                    <button class="btn btn-outline-secondary" data-period="1y">1г</button>
                    <button class="btn btn-outline-secondary" data-period="all">Всички</button>
                </div>
            </div>

            <!-- Основни метрики -->
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="p-2 border rounded text-center">
                        <small class="d-block text-muted">Среден наем</small>
                        <span class="area-avg-rent"></span>
                        <small class="d-block text-success rent-trend"></small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded text-center">
                        <small class="d-block text-muted">Средна продажба</small>
                        <span class="area-avg-sale"></span>
                        <small class="d-block text-success sale-trend"></small>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded text-center">
                        <small class="d-block text-muted">ROI</small>
                        <span class="area-roi"></span>
                        <div class="progress mt-1" style="height: 3px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 border rounded text-center">
                        <small class="d-block text-muted">Ликвидност</small>
                        <span class="area-liquidity"></span>
                        <small class="d-block text-muted">дни среден период</small>
                    </div>
                </div>
            </div>

            <!-- Графики -->
            <div class="mb-3">
                <ul class="nav nav-tabs nav-fill">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#priceChart">
                            <i class="fas fa-chart-line me-1"></i>Цени
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#supplyChart">
                            <i class="fas fa-chart-bar me-1"></i>Предлагане
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#demandChart">
                            <i class="fas fa-chart-pie me-1"></i>Търсене
                        </a>
                    </li>
                </ul>
                <div class="tab-content border-start border-end border-bottom p-3">
                    <div class="tab-pane fade show active" id="priceChart">
                        <canvas id="areaPriceHistory"></canvas>
                    </div>
                    <div class="tab-pane fade" id="supplyChart">
                        <canvas id="areaSupplyHistory"></canvas>
                    </div>
                    <div class="tab-pane fade" id="demandChart">
                        <canvas id="areaDemandHistory"></canvas>
                    </div>
                </div>
            </div>

            <!-- Детайлна информация -->
            <div class="accordion" id="areaDetails">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#demographicsInfo">
                            <i class="fas fa-users me-2"></i>Демографска информация
                        </button>
                    </h2>
                    <div id="demographicsInfo" class="accordion-collapse collapse" data-bs-parent="#areaDetails">
                        <div class="accordion-body">
                            <div class="row g-2">
                                <div class="col-6">
                                    <small class="d-block text-muted">Население</small>
                                    <span class="area-population"></span>
                                </div>
                                <div class="col-6">
                                    <small class="d-block text-muted">Средна възраст</small>
                                    <span class="area-avg-age"></span>
                                </div>
                                <div class="col-6">
                                    <small class="d-block text-muted">Доход на глава</small>
                                    <span class="area-income"></span>
                                </div>
                                <div class="col-6">
                                    <small class="d-block text-muted">Безработица</small>
                                    <span class="area-unemployment"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#infrastructureInfo">
                            <i class="fas fa-road me-2"></i>Инфраструктура
                        </button>
                    </h2>
                    <div id="infrastructureInfo" class="accordion-collapse collapse" data-bs-parent="#areaDetails">
                        <div class="accordion-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Транспортна достъпност</span>
                                    <div class="stars"></div>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-success transport-score" role="progressbar"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Социална инфраструктура</span>
                                    <div class="stars"></div>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-success social-score" role="progressbar"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Зелени площи</span>
                                    <div class="stars"></div>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-success green-score" role="progressbar"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#developmentInfo">
                            <i class="fas fa-building me-2"></i>Развитие на района
                        </button>
                    </h2>
                    <div id="developmentInfo" class="accordion-collapse collapse" data-bs-parent="#areaDetails">
                        <div class="accordion-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Планирани проекти</h6>
                                        <ul class="list-unstyled planned-projects">
                                            <!-- Динамично съдържание -->
                                        </ul>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Текущи проекти</h6>
                                        <ul class="list-unstyled current-projects">
                                            <!-- Динамично съдържание -->
                                        </ul>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Завършени проекти</h6>
                                        <ul class="list-unstyled completed-projects">
                                            <!-- Динамично съдържание -->
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Прогнози -->
            <div class="mt-3">
                <h6 class="mb-3">Прогнози за района</h6>
                <div class="row g-2">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="card-title mb-0">Ценови тренд (12 месеца)</h6>
                                    <div class="trend-indicator"></div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="trend-arrow me-2"></div>
                                    <div class="trend-value"></div>
                                </div>
                                <small class="text-muted trend-description"></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Риск</h6>
                                <div class="risk-meter"></div>
                                <small class="text-muted risk-description"></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Потенциал</h6>
                                <div class="potential-meter"></div>
                                <small class="text-muted potential-description"></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<!-- Advanced Sort Template -->
<template id="advancedSortTemplate">
    <div class="dropdown-menu p-3" style="width: 300px;">
        <h6 class="dropdown-header">Подредба по множество критерии</h6>
        <div class="mb-3">
            <select class="form-select form-select-sm mb-2" id="primarySort">
                <option value="created_at">Дата на публикуване</option>
                <option value="price">Цена</option>
                <option value="area">Площ</option>
                <option value="price_per_sqm">Цена на кв.м.</option>
            </select>
            <select class="form-select form-select-sm" id="primarySortDir">
                <option value="desc">Низходящо</option>
                <option value="asc">Възходящо</option>
            </select>
        </div>
        
        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="enableSecondarySort">
                <label class="form-check-label" for="enableSecondarySort">
                    Добави втори критерий
                </label>
            </div>
        </div>
        
        <div class="secondary-sort d-none">
            <select class="form-select form-select-sm mb-2" id="secondarySort">
                <option value="created_at">Дата на публикуване</option>
                <option value="price">Цена</option>
                <option value="area">Площ</option>
                <option value="price_per_sqm">Цена на кв.м.</option>
            </select>
            <select class="form-select form-select-sm" id="secondarySortDir">
                <option value="desc">Низходящо</option>
                <option value="asc">Възходящо</option>
            </select>
        </div>
        
        <div class="mt-3">
            <button type="button" class="btn btn-primary btn-sm w-100" id="applySort">
                Приложи
            </button>
        </div>
        
        <!-- Custom Scoring -->
        <div class="mt-3">
            <h6 class="dropdown-header">Персонализирано класиране</h6>
            <div class="scoring-criteria">
                <div class="mb-2">
                    <label class="form-label d-flex justify-content-between">
                        Цена
                        <input type="range" class="form-range w-50" name="score_price" value="1">
                    </label>
                </div>
                <div class="mb-2">
                    <label class="form-label d-flex justify-content-between">
                        Локация
                        <input type="range" class="form-range w-50" name="score_location" value="1">
                    </label>
                </div>
                <div class="mb-2">
                    <label class="form-label d-flex justify-content-between">
                        Площ
                        <input type="range" class="form-range w-50" name="score_area" value="1">
                    </label>
                </div>
                <div class="mb-2">
                    <label class="form-label d-flex justify-content-between">
                        Състояние
                        <input type="range" class="form-range w-50" name="score_condition" value="1">
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Additional Sort Criteria -->
        <div class="mt-3">
            <h6 class="dropdown-header">Допълнителни критерии</h6>
            <select class="form-select form-select-sm mb-2" id="additionalSort">
                <option value="">Избери критерий</option>
                <option value="roi">Възвръщаемост</option>
                <option value="price_trend">Тренд на цените</option>
                <option value="views">Брой прегледи</option>
                <option value="energy_rating">Енергийна ефективност</option>
                <option value="nearby_amenities">Близки удобства</option>
            </select>
        </div>
        
        <!-- Save Sort Preference -->
        <div class="mt-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="saveSortPreference">
                <label class="form-check-label" for="saveSortPreference">
                    Запази като предпочитана подредба
                </label>
            </div>
            <input type="text" class="form-control form-control-sm mt-2 d-none" id="sortPreferenceName" 
                   placeholder="Име на подредбата">
        </div>
    </div>
</template>

<style>
/* Property Card Styles */
.property-card {
    transition: transform 0.2s;
}

.property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
}

.property-images {
    overflow: hidden;
}

.image-navigation button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 2;
    opacity: 0;
    transition: opacity 0.2s;
}

.property-images:hover .image-navigation button {
    opacity: 1;
}

.prev-image {
    left: 10px;
}

.next-image {
    right: 10px;
}

.property-badges span {
    text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
}

.property-features .badge {
    font-size: 0.75rem;
    font-weight: normal;
}

/* Map Styles */
.map-controls {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
    max-width: 300px;
}

.map-controls .btn-group {
    flex-wrap: wrap;
}

.map-controls .btn-group .btn {
    flex: 0 0 calc(25% - 4px);
    margin: 2px;
}

/* Advanced Sort Styles */
.dropdown-menu {
    max-height: 400px;
    overflow-y: auto;
}

.form-select-sm {
    font-size: 0.875rem;
}

/* Стилове за времевата линия */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: -24px;
    top: 12px;
    width: 1px;
    height: calc(100% - 12px);
    background-color: #dee2e6;
}

/* Стилове за метри */
.risk-meter, .potential-meter {
    height: 8px;
    background: #eee;
    border-radius: 4px;
    margin: 0.5rem 0;
    position: relative;
    overflow: hidden;
}

.risk-meter::after, .potential-meter::after {
    content: '';
    position: absolute;
    height: 100%;
    left: 0;
    top: 0;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.risk-meter::after {
    background: linear-gradient(90deg, #28a745, #ffc107, #dc3545);
}

.potential-meter::after {
    background: linear-gradient(90deg, #dc3545, #ffc107, #28a745);
}

/* Стилове за звезди */
.stars {
    color: #ffc107;
}

/* Стилове за трендове */
.trend-arrow {
    font-size: 1.5rem;
    font-weight: bold;
}

.trend-indicator {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Existing initialization code
    // ... existing code ...

    // View switching
    document.querySelectorAll('[data-view]').forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Update buttons
            document.querySelectorAll('[data-view]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Update views
            document.querySelectorAll('.view-content').forEach(content => content.classList.add('d-none'));
            document.getElementById(view + 'View').classList.remove('d-none');
            
            // Initialize map if needed
            if (view === 'map' && !mapInitialized) {
                initializeMap();
            }
        });
    });

    // Quick filters
    document.querySelectorAll('.quick-filter').forEach(button => {
        button.addEventListener('click', function() {
            const priceMin = this.dataset.priceMin;
            const priceMax = this.dataset.priceMax;
            const areaMin = this.dataset.areaMin;
            const areaMax = this.dataset.areaMax;
            
            if (priceMin) document.querySelector('[name="price_min"]').value = priceMin;
            if (priceMax) document.querySelector('[name="price_max"]').value = priceMax;
            if (areaMin) document.querySelector('[name="area_min"]').value = areaMin;
            if (areaMax) document.querySelector('[name="area_max"]').value = areaMax;
            
            document.getElementById('searchForm').submit();
        });
    });

    // Compare functionality
    let compareItems = [];
    const compareBtn = document.getElementById('compareBtn');
    const compareCount = document.getElementById('compareCount');
    
    document.querySelectorAll('.compare-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                if (compareItems.length >= 4) {
                    this.checked = false;
                    alert('Можете да сравните максимум 4 имота едновременно');
                    return;
                }
                compareItems.push(this.value);
            } else {
                compareItems = compareItems.filter(id => id !== this.value);
            }
            
            compareCount.textContent = compareItems.length;
            compareBtn.disabled = compareItems.length < 2;
        });
    });
    
    compareBtn.addEventListener('click', function() {
        if (compareItems.length < 2) return;
        
        // Load comparison data
        fetch(`/properties/compare?ids=${compareItems.join(',')}`)
            .then(response => response.json())
            .then(data => {
                // Populate comparison modal
                const modal = new bootstrap.Modal(document.getElementById('compareModal'));
                modal.show();
            });
    });

    // Quick view functionality
    document.querySelectorAll('.quick-view-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const propertyId = this.dataset.propertyId;
            const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
            
            // Show modal with loading state
            modal.show();
            
            // Load property data
            fetch(`/properties/${propertyId}/quick-view`)
                .then(response => response.text())
                .then(html => {
                    document.querySelector('#quickViewModal .modal-body').innerHTML = html;
                });
        });
    });

    // Remove filter functionality
    document.querySelectorAll('.remove-filter').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;
            const form = document.getElementById('searchForm');
            
            // Remove the filter from the form
            const input = form.querySelector(`[name="${filter}"]`);
            if (input) {
                if (input.type === 'checkbox') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
            }
            
            form.submit();
        });
    });

    // Map initialization
    let mapInitialized = false;
    
    function initializeMap() {
        if (mapInitialized) return;
        
        const map = L.map('propertyMap').setView([42.6977, 23.3219], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        
        // Add markers for properties
        <?php foreach ($results as $property): ?>
            <?php if (!empty($property['latitude']) && !empty($property['longitude'])): ?>
                L.marker([<?= $property['latitude'] ?>, <?= $property['longitude'] ?>])
                    .bindPopup(`
                        <div class="text-center">
                            <img src="<?= $property['images'][0]['url'] ?>" class="img-fluid mb-2" style="max-height: 100px;">
                            <h6><?= htmlspecialchars($property['title']) ?></h6>
                            <p class="mb-1"><?= number_format($property['price']) ?> €</p>
                            <a href="/properties/<?= $property['id'] ?>" class="btn btn-sm btn-primary">Детайли</a>
                        </div>
                    `)
                    .addTo(map);
            <?php endif; ?>
        <?php endforeach; ?>
        
        mapInitialized = true;
    }
});

class AreaAnalytics {
    constructor(container) {
        this.container = container;
        this.charts = {};
        this.currentPeriod = '1m';
        this.setupCharts();
        this.setupEventListeners();
    }

    setupCharts() {
        // Ценова история
        this.charts.price = new Chart(document.getElementById('areaPriceHistory'), {
            type: 'line',
            options: {
                responsive: true,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.parsed.y.toLocaleString('bg-BG')} €`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false
                    }
                }
            }
        });

        // Предлагане
        this.charts.supply = new Chart(document.getElementById('areaSupplyHistory'), {
            type: 'bar',
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });

        // Търсене
        this.charts.demand = new Chart(document.getElementById('areaDemandHistory'), {
            type: 'doughnut',
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    }

    setupEventListeners() {
        // Период на статистиката
        document.querySelectorAll('[data-period]').forEach(button => {
            button.addEventListener('click', (e) => {
                document.querySelectorAll('[data-period]').forEach(btn => btn.classList.remove('active'));
                e.target.classList.add('active');
                this.currentPeriod = e.target.dataset.period;
                this.updateData();
            });
        });
    }

    async updateData() {
        const data = await this.fetchAreaData();
        this.updateMetrics(data.metrics);
        this.updateCharts(data.charts);
        this.updateDemographics(data.demographics);
        this.updateInfrastructure(data.infrastructure);
        this.updateDevelopment(data.development);
        this.updateForecasts(data.forecasts);
    }

    async fetchAreaData() {
        const response = await fetch(`/api/area-analytics?period=${this.currentPeriod}`);
        return await response.json();
    }

    updateMetrics(metrics) {
        // Основни метрики
        this.container.querySelector('.area-avg-rent').textContent = 
            `${metrics.avgRent.toLocaleString('bg-BG')} €`;
        this.container.querySelector('.area-avg-sale').textContent = 
            `${metrics.avgSale.toLocaleString('bg-BG')} €`;
        
        // Трендове
        this.updateTrend('.rent-trend', metrics.rentTrend);
        this.updateTrend('.sale-trend', metrics.saleTrend);
        
        // ROI
        this.container.querySelector('.area-roi').textContent = `${metrics.roi.toFixed(1)}%`;
        this.container.querySelector('.progress-bar').style.width = `${metrics.roi * 2}%`;
        
        // Ликвидност
        this.container.querySelector('.area-liquidity').textContent = metrics.liquidity;
    }

    updateTrend(selector, trend) {
        const element = this.container.querySelector(selector);
        const isPositive = trend > 0;
        const arrow = isPositive ? '↑' : '↓';
        const color = isPositive ? 'success' : 'danger';
        element.className = `d-block text-${color}`;
        element.textContent = `${arrow} ${Math.abs(trend).toFixed(1)}%`;
    }

    updateCharts(data) {
        // Цени
        this.charts.price.data = {
            labels: data.price.labels,
            datasets: [{
                label: 'Продажби',
                data: data.price.sale,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }, {
                label: 'Наеми',
                data: data.price.rent,
                borderColor: 'rgb(255, 99, 132)',
                tension: 0.1
            }]
        };
        this.charts.price.update();

        // Предлагане
        this.charts.supply.data = {
            labels: data.supply.labels,
            datasets: [{
                label: 'Брой имоти',
                data: data.supply.data,
                backgroundColor: 'rgba(75, 192, 192, 0.5)'
            }]
        };
        this.charts.supply.update();

        // Търсене
        this.charts.demand.data = {
            labels: data.demand.labels,
            datasets: [{
                data: data.demand.data,
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ]
            }]
        };
        this.charts.demand.update();
    }

    updateDemographics(data) {
        this.container.querySelector('.area-population').textContent = 
            data.population.toLocaleString('bg-BG');
        this.container.querySelector('.area-avg-age').textContent = 
            `${data.avgAge} години`;
        this.container.querySelector('.area-income').textContent = 
            `${data.income.toLocaleString('bg-BG')} лв`;
        this.container.querySelector('.area-unemployment').textContent = 
            `${data.unemployment}%`;
    }

    updateInfrastructure(data) {
        this.updateScore('transport', data.transport);
        this.updateScore('social', data.social);
        this.updateScore('green', data.green);
    }

    updateScore(type, score) {
        const stars = this.container.querySelector(`.${type}-score`).parentElement.previousElementSibling.querySelector('.stars');
        stars.innerHTML = '★'.repeat(Math.round(score)) + '☆'.repeat(5 - Math.round(score));
        this.container.querySelector(`.${type}-score`).style.width = `${score * 20}%`;
    }

    updateDevelopment(data) {
        this.updateProjects('planned', data.planned);
        this.updateProjects('current', data.current);
        this.updateProjects('completed', data.completed);
    }

    updateProjects(type, projects) {
        const container = this.container.querySelector(`.${type}-projects`);
        container.innerHTML = projects.map(project => `
            <li class="mb-2">
                <div class="d-flex justify-content-between">
                    <strong>${project.name}</strong>
                    <small>${project.date}</small>
                </div>
                <small class="text-muted d-block">${project.description}</small>
            </li>
        `).join('');
    }

    updateForecasts(data) {
        // Ценови тренд
        const trendIndicator = this.container.querySelector('.trend-indicator');
        trendIndicator.style.backgroundColor = this.getTrendColor(data.priceTrend);
        
        const trendArrow = this.container.querySelector('.trend-arrow');
        trendArrow.textContent = this.getTrendArrow(data.priceTrend);
        trendArrow.style.color = this.getTrendColor(data.priceTrend);
        
        this.container.querySelector('.trend-value').textContent = 
            `${Math.abs(data.priceTrend).toFixed(1)}%`;
        this.container.querySelector('.trend-description').textContent = 
            data.trendDescription;

        // Риск
        this.container.querySelector('.risk-meter').style.setProperty(
            '--value', `${data.risk}%`
        );
        this.container.querySelector('.risk-description').textContent = 
            data.riskDescription;

        // Потенциал
        this.container.querySelector('.potential-meter').style.setProperty(
            '--value', `${data.potential}%`
        );
        this.container.querySelector('.potential-description').textContent = 
            data.potentialDescription;
    }

    getTrendColor(trend) {
        if (trend > 5) return '#28a745';
        if (trend > 0) return '#ffc107';
        return '#dc3545';
    }

    getTrendArrow(trend) {
        if (trend > 5) return '↑↑';
        if (trend > 0) return '↗';
        if (trend > -5) return '↘';
        return '↓↓';
    }
}

// Инициализация
document.addEventListener('DOMContentLoaded', function() {
    const analytics = new AreaAnalytics(document.querySelector('.selected-area-stats'));
});
</script>

<?php
function formatFilterLabel($key, $value) {
    $labels = [
        'property_type' => 'Тип имот',
        'transaction_type' => 'Тип сделка',
        'price_min' => 'Цена от',
        'price_max' => 'Цена до',
        // Add more labels as needed
    ];
    
    $label = $labels[$key] ?? $key;
    
    if (is_array($value)) {
        return $label . ': ' . implode(', ', $value);
    }
    
    return $label . ': ' . $value;
}
?>

<?php include_once '../layouts/footer.php'; ?> 