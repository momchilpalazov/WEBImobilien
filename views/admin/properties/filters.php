<?php
/**
 * @var array $types
 * @var array $statuses
 * @var array $filters
 * @var array $sortOptions
 */
?>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Филтри</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <!-- Тип имот -->
            <div class="col-md-3">
                <label for="type" class="form-label">Тип имот</label>
                <select name="type" id="type" class="form-select">
                    <option value="">Всички типове</option>
                    <?php foreach ($types as $value => $label): ?>
                        <option value="<?= $value ?>" <?= ($filters['type'] ?? '') === $value ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Статус -->
            <div class="col-md-3">
                <label for="status" class="form-label">Статус</label>
                <select name="status" id="status" class="form-select">
                    <option value="">Всички статуси</option>
                    <?php foreach ($statuses as $value => $label): ?>
                        <option value="<?= $value ?>" <?= ($filters['status'] ?? '') === $value ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Цена -->
            <div class="col-md-3">
                <label for="min_price" class="form-label">Минимална цена</label>
                <input type="number" class="form-control" id="min_price" name="min_price" 
                       value="<?= $filters['min_price'] ?? '' ?>" min="0" step="1000">
            </div>
            <div class="col-md-3">
                <label for="max_price" class="form-label">Максимална цена</label>
                <input type="number" class="form-control" id="max_price" name="max_price" 
                       value="<?= $filters['max_price'] ?? '' ?>" min="0" step="1000">
            </div>

            <!-- Площ -->
            <div class="col-md-3">
                <label for="min_area" class="form-label">Минимална площ</label>
                <input type="number" class="form-control" id="min_area" name="min_area" 
                       value="<?= $filters['min_area'] ?? '' ?>" min="0" step="10">
            </div>
            <div class="col-md-3">
                <label for="max_area" class="form-label">Максимална площ</label>
                <input type="number" class="form-control" id="max_area" name="max_area" 
                       value="<?= $filters['max_area'] ?? '' ?>" min="0" step="10">
            </div>

            <!-- Локация -->
            <div class="col-md-3">
                <label for="location" class="form-label">Локация</label>
                <input type="text" class="form-control" id="location" name="location" 
                       value="<?= $filters['location'] ?? '' ?>" placeholder="Търсене по адрес...">
            </div>

            <!-- Сортиране -->
            <div class="col-md-3">
                <label for="sort" class="form-label">Сортиране</label>
                <select name="sort" id="sort" class="form-select">
                    <?php foreach ($sortOptions as $value => $label): ?>
                        <option value="<?= $value ?>" <?= ($filters['sort'] ?? 'date_desc') === $value ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Бутони -->
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-1"></i> Търсене
                </button>
                <a href="<?= $_SERVER['SCRIPT_NAME'] ?>" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Изчисти филтрите
                </a>
            </div>
        </form>
    </div>
</div> 