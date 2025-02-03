<?php
$translations = $translations ?? [];
$property = $property ?? null;
$types = $types ?? [];
$statuses = $statuses ?? [];
$isEdit = !empty($property);

$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>

<form action="<?= $isEdit ? "/admin/properties/edit/{$property['id']}" : '/admin/properties/create' ?>" 
      method="post" 
      enctype="multipart/form-data"
      class="needs-validation" 
      novalidate>
    
    <!-- Basic Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><?= $translations['form']['basic_info'] ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Title BG -->
                <div class="col-md-4 mb-3">
                    <label for="title_bg" class="form-label"><?= $translations['form']['title'] ?> (BG) *</label>
                    <input type="text" 
                           class="form-control <?= !empty($_SESSION['errors']['title_bg']) ? 'is-invalid' : '' ?>" 
                           id="title_bg" 
                           name="title_bg" 
                           value="<?= $old['title_bg'] ?? $property['title_bg'] ?? '' ?>" 
                           required>
                    <?php if (!empty($_SESSION['errors']['title_bg'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['title_bg'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Title DE -->
                <div class="col-md-4 mb-3">
                    <label for="title_de" class="form-label"><?= $translations['form']['title'] ?> (DE) *</label>
                    <input type="text" 
                           class="form-control <?= !empty($_SESSION['errors']['title_de']) ? 'is-invalid' : '' ?>" 
                           id="title_de" 
                           name="title_de" 
                           value="<?= $old['title_de'] ?? $property['title_de'] ?? '' ?>" 
                           required>
                    <?php if (!empty($_SESSION['errors']['title_de'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['title_de'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Title RU -->
                <div class="col-md-4 mb-3">
                    <label for="title_ru" class="form-label"><?= $translations['form']['title'] ?> (RU) *</label>
                    <input type="text" 
                           class="form-control <?= !empty($_SESSION['errors']['title_ru']) ? 'is-invalid' : '' ?>" 
                           id="title_ru" 
                           name="title_ru" 
                           value="<?= $old['title_ru'] ?? $property['title_ru'] ?? '' ?>" 
                           required>
                    <?php if (!empty($_SESSION['errors']['title_ru'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['title_ru'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Type -->
                <div class="col-md-3 mb-3">
                    <label for="type" class="form-label"><?= $translations['form']['type'] ?> *</label>
                    <select class="form-select <?= !empty($_SESSION['errors']['type']) ? 'is-invalid' : '' ?>" 
                            id="type" 
                            name="type" 
                            required>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type ?>" 
                                    <?= ($old['type'] ?? $property['type'] ?? '') === $type ? 'selected' : '' ?>>
                                <?= $translations['filters']['type'][$type] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($_SESSION['errors']['type'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['type'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Status -->
                <div class="col-md-3 mb-3">
                    <label for="status" class="form-label"><?= $translations['form']['status'] ?> *</label>
                    <select class="form-select <?= !empty($_SESSION['errors']['status']) ? 'is-invalid' : '' ?>" 
                            id="status" 
                            name="status" 
                            required>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= $status ?>" 
                                    <?= ($old['status'] ?? $property['status'] ?? '') === $status ? 'selected' : '' ?>>
                                <?= $translations['filters']['status'][$status] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (!empty($_SESSION['errors']['status'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['status'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Price -->
                <div class="col-md-3 mb-3">
                    <label for="price" class="form-label"><?= $translations['form']['price'] ?> *</label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control <?= !empty($_SESSION['errors']['price']) ? 'is-invalid' : '' ?>" 
                               id="price" 
                               name="price" 
                               value="<?= $old['price'] ?? $property['price'] ?? '' ?>" 
                               step="0.01" 
                               min="0" 
                               required>
                        <span class="input-group-text">€</span>
                        <?php if (!empty($_SESSION['errors']['price'])): ?>
                            <div class="invalid-feedback"><?= $_SESSION['errors']['price'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Area -->
                <div class="col-md-3 mb-3">
                    <label for="area" class="form-label"><?= $translations['form']['area'] ?> *</label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control <?= !empty($_SESSION['errors']['area']) ? 'is-invalid' : '' ?>" 
                               id="area" 
                               name="area" 
                               value="<?= $old['area'] ?? $property['area'] ?? '' ?>" 
                               step="0.01" 
                               min="0" 
                               required>
                        <span class="input-group-text">m²</span>
                        <?php if (!empty($_SESSION['errors']['area'])): ?>
                            <div class="invalid-feedback"><?= $_SESSION['errors']['area'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Location BG -->
                <div class="col-md-4 mb-3">
                    <label for="location_bg" class="form-label"><?= $translations['form']['location'] ?> (BG) *</label>
                    <input type="text" 
                           class="form-control <?= !empty($_SESSION['errors']['location_bg']) ? 'is-invalid' : '' ?>" 
                           id="location_bg" 
                           name="location_bg" 
                           value="<?= $old['location_bg'] ?? $property['location_bg'] ?? '' ?>" 
                           required>
                    <?php if (!empty($_SESSION['errors']['location_bg'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['location_bg'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Location DE -->
                <div class="col-md-4 mb-3">
                    <label for="location_de" class="form-label"><?= $translations['form']['location'] ?> (DE) *</label>
                    <input type="text" 
                           class="form-control <?= !empty($_SESSION['errors']['location_de']) ? 'is-invalid' : '' ?>" 
                           id="location_de" 
                           name="location_de" 
                           value="<?= $old['location_de'] ?? $property['location_de'] ?? '' ?>" 
                           required>
                    <?php if (!empty($_SESSION['errors']['location_de'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['location_de'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Location RU -->
                <div class="col-md-4 mb-3">
                    <label for="location_ru" class="form-label"><?= $translations['form']['location'] ?> (RU) *</label>
                    <input type="text" 
                           class="form-control <?= !empty($_SESSION['errors']['location_ru']) ? 'is-invalid' : '' ?>" 
                           id="location_ru" 
                           name="location_ru" 
                           value="<?= $old['location_ru'] ?? $property['location_ru'] ?? '' ?>" 
                           required>
                    <?php if (!empty($_SESSION['errors']['location_ru'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['location_ru'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Address and Location -->
            <div class="row">
                <!-- Address -->
                <div class="col-md-6 mb-3">
                    <label for="address" class="form-label"><?= $translations['form']['address'] ?> *</label>
                    <div class="input-group">
                        <input type="text" 
                               class="form-control <?= !empty($_SESSION['errors']['address']) ? 'is-invalid' : '' ?>" 
                               id="address" 
                               name="address" 
                               value="<?= $old['address'] ?? $property['address'] ?? '' ?>" 
                               required>
                        <button class="btn btn-outline-secondary" type="button" id="searchAddress">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <?php if (!empty($_SESSION['errors']['address'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['address'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Map Container -->
                <div class="col-md-6 mb-3">
                    <div id="map-container" style="height: 300px; width: 100%;"></div>
                </div>

                <!-- Coordinates -->
                <div class="col-md-3 mb-3">
                    <label for="lat" class="form-label">Latitude</label>
                    <input type="text" 
                           class="form-control <?= !empty($_SESSION['errors']['lat']) ? 'is-invalid' : '' ?>" 
                           id="lat" 
                           name="lat" 
                           value="<?= $old['lat'] ?? $property['lat'] ?? '' ?>">
                    <?php if (!empty($_SESSION['errors']['lat'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['lat'] ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-3 mb-3">
                    <label for="lng" class="form-label">Longitude</label>
                    <input type="text" 
                           class="form-control <?= !empty($_SESSION['errors']['lng']) ? 'is-invalid' : '' ?>" 
                           id="lng" 
                           name="lng" 
                           value="<?= $old['lng'] ?? $property['lng'] ?? '' ?>">
                    <?php if (!empty($_SESSION['errors']['lng'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['lng'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Features -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><?= $translations['form']['features'] ?></h5>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- Built Year -->
                <div class="col-md-3 mb-3">
                    <label for="built_year" class="form-label"><?= $translations['form']['built_year'] ?></label>
                    <input type="number" 
                           class="form-control <?= !empty($_SESSION['errors']['built_year']) ? 'is-invalid' : '' ?>" 
                           id="built_year" 
                           name="built_year" 
                           value="<?= $old['built_year'] ?? $property['built_year'] ?? '' ?>" 
                           min="1900" 
                           max="<?= date('Y') ?>">
                    <?php if (!empty($_SESSION['errors']['built_year'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['built_year'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Last Renovation -->
                <div class="col-md-3 mb-3">
                    <label for="last_renovation" class="form-label"><?= $translations['form']['last_renovation'] ?></label>
                    <input type="number" 
                           class="form-control <?= !empty($_SESSION['errors']['last_renovation']) ? 'is-invalid' : '' ?>" 
                           id="last_renovation" 
                           name="last_renovation" 
                           value="<?= $old['last_renovation'] ?? $property['last_renovation'] ?? '' ?>" 
                           min="1900" 
                           max="<?= date('Y') ?>">
                    <?php if (!empty($_SESSION['errors']['last_renovation'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['last_renovation'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Floors -->
                <div class="col-md-3 mb-3">
                    <label for="floors" class="form-label"><?= $translations['form']['floors'] ?></label>
                    <input type="number" 
                           class="form-control <?= !empty($_SESSION['errors']['floors']) ? 'is-invalid' : '' ?>" 
                           id="floors" 
                           name="floors" 
                           value="<?= $old['floors'] ?? $property['floors'] ?? '' ?>" 
                           min="1">
                    <?php if (!empty($_SESSION['errors']['floors'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['floors'] ?></div>
                    <?php endif; ?>
                </div>

                <!-- Parking Spots -->
                <div class="col-md-3 mb-3">
                    <label for="parking_spots" class="form-label"><?= $translations['form']['parking_spots'] ?></label>
                    <input type="number" 
                           class="form-control <?= !empty($_SESSION['errors']['parking_spots']) ? 'is-invalid' : '' ?>" 
                           id="parking_spots" 
                           name="parking_spots" 
                           value="<?= $old['parking_spots'] ?? $property['parking_spots'] ?? '' ?>" 
                           min="0">
                    <?php if (!empty($_SESSION['errors']['parking_spots'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['parking_spots'] ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <!-- Ceiling Height -->
                <div class="col-md-3 mb-3">
                    <label for="ceiling_height" class="form-label"><?= $translations['form']['ceiling_height'] ?></label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control <?= !empty($_SESSION['errors']['ceiling_height']) ? 'is-invalid' : '' ?>" 
                               id="ceiling_height" 
                               name="ceiling_height" 
                               value="<?= $old['ceiling_height'] ?? $property['ceiling_height'] ?? '' ?>" 
                               step="0.1" 
                               min="0">
                        <span class="input-group-text">m</span>
                        <?php if (!empty($_SESSION['errors']['ceiling_height'])): ?>
                            <div class="invalid-feedback"><?= $_SESSION['errors']['ceiling_height'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Office Space -->
                <div class="col-md-3 mb-3">
                    <label for="office_space" class="form-label"><?= $translations['form']['office_space'] ?></label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control <?= !empty($_SESSION['errors']['office_space']) ? 'is-invalid' : '' ?>" 
                               id="office_space" 
                               name="office_space" 
                               value="<?= $old['office_space'] ?? $property['office_space'] ?? '' ?>" 
                               step="0.01" 
                               min="0">
                        <span class="input-group-text">m²</span>
                        <?php if (!empty($_SESSION['errors']['office_space'])): ?>
                            <div class="invalid-feedback"><?= $_SESSION['errors']['office_space'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Storage Space -->
                <div class="col-md-3 mb-3">
                    <label for="storage_space" class="form-label"><?= $translations['form']['storage_space'] ?></label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control <?= !empty($_SESSION['errors']['storage_space']) ? 'is-invalid' : '' ?>" 
                               id="storage_space" 
                               name="storage_space" 
                               value="<?= $old['storage_space'] ?? $property['storage_space'] ?? '' ?>" 
                               step="0.01" 
                               min="0">
                        <span class="input-group-text">m²</span>
                        <?php if (!empty($_SESSION['errors']['storage_space'])): ?>
                            <div class="invalid-feedback"><?= $_SESSION['errors']['storage_space'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Production Space -->
                <div class="col-md-3 mb-3">
                    <label for="production_space" class="form-label"><?= $translations['form']['production_space'] ?></label>
                    <div class="input-group">
                        <input type="number" 
                               class="form-control <?= !empty($_SESSION['errors']['production_space']) ? 'is-invalid' : '' ?>" 
                               id="production_space" 
                               name="production_space" 
                               value="<?= $old['production_space'] ?? $property['production_space'] ?? '' ?>" 
                               step="0.01" 
                               min="0">
                        <span class="input-group-text">m²</span>
                        <?php if (!empty($_SESSION['errors']['production_space'])): ?>
                            <div class="invalid-feedback"><?= $_SESSION['errors']['production_space'] ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Utilities -->
            <div class="row mt-3">
                <div class="col-12">
                    <label class="form-label"><?= $translations['form']['utilities'] ?></label>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="heating" 
                               name="heating" 
                               value="1" 
                               <?= ($old['heating'] ?? $property['heating'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="heating">
                            <?= $translations['form']['heating'] ?>
                        </label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="electricity" 
                               name="electricity" 
                               value="1" 
                               <?= ($old['electricity'] ?? $property['electricity'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="electricity">
                            <?= $translations['form']['electricity'] ?>
                        </label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="water_supply" 
                               name="water_supply" 
                               value="1" 
                               <?= ($old['water_supply'] ?? $property['water_supply'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="water_supply">
                            <?= $translations['form']['water_supply'] ?>
                        </label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="security" 
                               name="security" 
                               value="1" 
                               <?= ($old['security'] ?? $property['security'] ?? false) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="security">
                            <?= $translations['form']['security'] ?>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Loading Docks -->
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="loading_docks" class="form-label"><?= $translations['form']['loading_docks'] ?></label>
                    <input type="number" 
                           class="form-control <?= !empty($_SESSION['errors']['loading_docks']) ? 'is-invalid' : '' ?>" 
                           id="loading_docks" 
                           name="loading_docks" 
                           value="<?= $old['loading_docks'] ?? $property['loading_docks'] ?? '0' ?>" 
                           min="0">
                    <?php if (!empty($_SESSION['errors']['loading_docks'])): ?>
                        <div class="invalid-feedback"><?= $_SESSION['errors']['loading_docks'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Media -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><?= $translations['form']['media'] ?></h5>
        </div>
        <div class="card-body">
            <!-- Images -->
            <div class="mb-3">
                <label for="images" class="form-label"><?= $translations['form']['images'] ?></label>
                <div class="dropzone-container">
                    <input type="file" 
                           class="form-control <?= !empty($_SESSION['errors']['images']) ? 'is-invalid' : '' ?>" 
                           id="images" 
                           name="images[]" 
                           multiple 
                           accept="image/*">
                    <div class="dropzone-message">
                        <i class="fas fa-cloud-upload-alt fa-2x mb-2"></i>
                        <p class="mb-0">Drag and drop images here or click to select</p>
                        <small class="text-muted">Supported formats: JPG, PNG, WebP (max 5MB per file)</small>
                    </div>
                </div>
                <?php if (!empty($_SESSION['errors']['images'])): ?>
                    <div class="invalid-feedback"><?= $_SESSION['errors']['images'] ?></div>
                <?php endif; ?>

                <div id="image-preview" class="row mt-3">
                    <?php if ($isEdit && !empty($property['images'])): ?>
                        <?php foreach ($property['images'] as $image): ?>
                            <div class="col-md-2 mb-3 image-preview-item">
                                <div class="position-relative">
                                    <img src="/<?= $image['path'] ?>" 
                                         alt="" 
                                         class="img-thumbnail">
                                    <div class="position-absolute top-0 end-0">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   name="delete_images[]" 
                                                   value="<?= $image['id'] ?>">
                                            <label class="form-check-label">Delete</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Virtual Tour -->
            <div class="mb-3">
                <label for="virtual_tour_url" class="form-label"><?= $translations['form']['virtual_tour_url'] ?></label>
                <input type="url" 
                       class="form-control <?= !empty($_SESSION['errors']['virtual_tour_url']) ? 'is-invalid' : '' ?>" 
                       id="virtual_tour_url" 
                       name="virtual_tour_url" 
                       value="<?= $old['virtual_tour_url'] ?? $property['virtual_tour_url'] ?? '' ?>">
                <?php if (!empty($_SESSION['errors']['virtual_tour_url'])): ?>
                    <div class="invalid-feedback"><?= $_SESSION['errors']['virtual_tour_url'] ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0"><?= $translations['form']['description'] ?></h5>
        </div>
        <div class="card-body">
            <!-- Description BG -->
            <div class="mb-3">
                <label for="description_bg" class="form-label"><?= $translations['form']['description'] ?> (BG)</label>
                <textarea class="form-control <?= !empty($_SESSION['errors']['description_bg']) ? 'is-invalid' : '' ?>" 
                          id="description_bg" 
                          name="description_bg" 
                          rows="5"><?= $old['description_bg'] ?? $property['description_bg'] ?? '' ?></textarea>
                <?php if (!empty($_SESSION['errors']['description_bg'])): ?>
                    <div class="invalid-feedback"><?= $_SESSION['errors']['description_bg'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Description DE -->
            <div class="mb-3">
                <label for="description_de" class="form-label"><?= $translations['form']['description'] ?> (DE)</label>
                <textarea class="form-control <?= !empty($_SESSION['errors']['description_de']) ? 'is-invalid' : '' ?>" 
                          id="description_de" 
                          name="description_de" 
                          rows="5"><?= $old['description_de'] ?? $property['description_de'] ?? '' ?></textarea>
                <?php if (!empty($_SESSION['errors']['description_de'])): ?>
                    <div class="invalid-feedback"><?= $_SESSION['errors']['description_de'] ?></div>
                <?php endif; ?>
            </div>

            <!-- Description RU -->
            <div class="mb-3">
                <label for="description_ru" class="form-label"><?= $translations['form']['description'] ?> (RU)</label>
                <textarea class="form-control <?= !empty($_SESSION['errors']['description_ru']) ? 'is-invalid' : '' ?>" 
                          id="description_ru" 
                          name="description_ru" 
                          rows="5"><?= $old['description_ru'] ?? $property['description_ru'] ?? '' ?></textarea>
                <?php if (!empty($_SESSION['errors']['description_ru'])): ?>
                    <div class="invalid-feedback"><?= $_SESSION['errors']['description_ru'] ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Featured -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="form-check">
                <input type="checkbox" 
                       class="form-check-input" 
                       id="featured" 
                       name="featured" 
                       value="1" 
                       <?= ($old['featured'] ?? $property['featured'] ?? false) ? 'checked' : '' ?>>
                <label class="form-check-label" for="featured">
                    Featured Property
                </label>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="d-flex justify-content-end gap-2">
        <a href="/admin/properties" class="btn btn-secondary"><?= $translations['form']['cancel'] ?></a>
        <button type="submit" class="btn btn-primary"><?= $translations['form']['save'] ?></button>
    </div>
</form>

<?php unset($_SESSION['errors']); ?> 
