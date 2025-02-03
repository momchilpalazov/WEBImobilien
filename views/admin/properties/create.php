<?php
/**
 * Property creation form
 * @var array $translations
 */
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><?php echo $translations['admin']['properties']['create']; ?></h1>
    <a href="/admin/properties" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>
        <?php echo $translations['admin']['back']; ?>
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="/admin/properties" method="post" enctype="multipart/form-data">
            <!-- Основна информация -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="card-title"><?php echo $translations['admin']['properties']['basic_info']; ?></h5>
                </div>
                
                <!-- Тип имот -->
                <div class="col-md-6 mb-3">
                    <label for="type" class="form-label required">
                        <?php echo $translations['admin']['properties']['type']; ?>
                    </label>
                    <select name="type" id="type" class="form-select <?php echo isset($_SESSION['errors']['type']) ? 'is-invalid' : ''; ?>" required>
                        <option value=""><?php echo $translations['admin']['properties']['select_type']; ?></option>
                        <?php foreach ($translations['property']['type'] as $key => $value): ?>
                            <option value="<?php echo $key; ?>" <?php echo ($_SESSION['old']['type'] ?? '') === $key ? 'selected' : ''; ?>>
                                <?php echo $value; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($_SESSION['errors']['type'])): ?>
                        <div class="invalid-feedback"><?php echo $_SESSION['errors']['type']; ?></div>
                    <?php endif; ?>
                </div>

                <!-- Статус -->
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label required">
                        <?php echo $translations['admin']['properties']['status']; ?>
                    </label>
                    <select name="status" id="status" class="form-select <?php echo isset($_SESSION['errors']['status']) ? 'is-invalid' : ''; ?>" required>
                        <option value=""><?php echo $translations['admin']['properties']['select_status']; ?></option>
                        <?php foreach ($translations['property']['status'] as $key => $value): ?>
                            <?php if ($key !== 'all'): ?>
                                <option value="<?php echo $key; ?>" <?php echo ($_SESSION['old']['status'] ?? '') === $key ? 'selected' : ''; ?>>
                                    <?php echo $value; ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($_SESSION['errors']['status'])): ?>
                        <div class="invalid-feedback"><?php echo $_SESSION['errors']['status']; ?></div>
                    <?php endif; ?>
                </div>

                <!-- Цена -->
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label required">
                        <?php echo $translations['admin']['properties']['price']; ?>
                    </label>
                    <div class="input-group">
                        <input type="number" 
                               name="price" 
                               id="price" 
                               class="form-control <?php echo isset($_SESSION['errors']['price']) ? 'is-invalid' : ''; ?>"
                               value="<?php echo $_SESSION['old']['price'] ?? ''; ?>"
                               step="0.01"
                               min="0"
                               required>
                        <span class="input-group-text">€</span>
                        <?php if (isset($_SESSION['errors']['price'])): ?>
                            <div class="invalid-feedback"><?php echo $_SESSION['errors']['price']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Площ -->
                <div class="col-md-6 mb-3">
                    <label for="area" class="form-label required">
                        <?php echo $translations['admin']['properties']['area']; ?>
                    </label>
                    <div class="input-group">
                        <input type="number" 
                               name="area" 
                               id="area" 
                               class="form-control <?php echo isset($_SESSION['errors']['area']) ? 'is-invalid' : ''; ?>"
                               value="<?php echo $_SESSION['old']['area'] ?? ''; ?>"
                               step="0.01"
                               min="0"
                               required>
                        <span class="input-group-text">m²</span>
                        <?php if (isset($_SESSION['errors']['area'])): ?>
                            <div class="invalid-feedback"><?php echo $_SESSION['errors']['area']; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Преводи -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="card-title"><?php echo $translations['admin']['properties']['translations']; ?></h5>
                </div>

                <?php foreach (['bg', 'en', 'de', 'ru'] as $lang): ?>
                    <!-- Заглавие -->
                    <div class="col-md-6 mb-3">
                        <label for="title_<?php echo $lang; ?>" class="form-label required">
                            <?php echo $translations['admin']['properties']['title_in']; ?> <?php echo strtoupper($lang); ?>
                        </label>
                        <input type="text" 
                               name="title_<?php echo $lang; ?>" 
                               id="title_<?php echo $lang; ?>" 
                               class="form-control <?php echo isset($_SESSION['errors']["title_{$lang}"]) ? 'is-invalid' : ''; ?>"
                               value="<?php echo $_SESSION['old']["title_{$lang}"] ?? ''; ?>"
                               required>
                        <?php if (isset($_SESSION['errors']["title_{$lang}"])): ?>
                            <div class="invalid-feedback"><?php echo $_SESSION['errors']["title_{$lang}"]; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Локация -->
                    <div class="col-md-6 mb-3">
                        <label for="location_<?php echo $lang; ?>" class="form-label required">
                            <?php echo $translations['admin']['properties']['location_in']; ?> <?php echo strtoupper($lang); ?>
                        </label>
                        <input type="text" 
                               name="location_<?php echo $lang; ?>" 
                               id="location_<?php echo $lang; ?>" 
                               class="form-control <?php echo isset($_SESSION['errors']["location_{$lang}"]) ? 'is-invalid' : ''; ?>"
                               value="<?php echo $_SESSION['old']["location_{$lang}"] ?? ''; ?>"
                               required>
                        <?php if (isset($_SESSION['errors']["location_{$lang}"])): ?>
                            <div class="invalid-feedback"><?php echo $_SESSION['errors']["location_{$lang}"]; ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Описание -->
                    <div class="col-12 mb-3">
                        <label for="description_<?php echo $lang; ?>" class="form-label">
                            <?php echo $translations['admin']['properties']['description_in']; ?> <?php echo strtoupper($lang); ?>
                        </label>
                        <textarea name="description_<?php echo $lang; ?>" 
                                  id="description_<?php echo $lang; ?>" 
                                  class="form-control <?php echo isset($_SESSION['errors']["description_{$lang}"]) ? 'is-invalid' : ''; ?>"
                                  rows="3"><?php echo $_SESSION['old']["description_{$lang}"] ?? ''; ?></textarea>
                        <?php if (isset($_SESSION['errors']["description_{$lang}"])): ?>
                            <div class="invalid-feedback"><?php echo $_SESSION['errors']["description_{$lang}"]; ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Медия файлове -->
            <div class="row">
                <div class="col-12">
                    <h5 class="card-title"><?php echo $translations['admin']['properties']['media']; ?></h5>
                </div>

                <!-- Изображения -->
                <div class="col-md-6 mb-3">
                    <label for="images" class="form-label">
                        <?php echo $translations['admin']['properties']['images']; ?>
                    </label>
                    <input type="file" 
                           name="images[]" 
                           id="images" 
                           class="form-control <?php echo isset($_SESSION['errors']['images']) ? 'is-invalid' : ''; ?>"
                           accept="image/*"
                           multiple>
                    <?php if (isset($_SESSION['errors']['images'])): ?>
                        <div class="invalid-feedback"><?php echo $_SESSION['errors']['images']; ?></div>
                    <?php endif; ?>
                    <div class="form-text"><?php echo $translations['admin']['properties']['images_help']; ?></div>
                </div>

                <!-- PDF флаер -->
                <div class="col-md-6 mb-3">
                    <label for="pdf_flyer" class="form-label">
                        <?php echo $translations['admin']['properties']['pdf_flyer']; ?>
                    </label>
                    <input type="file" 
                           name="pdf_flyer" 
                           id="pdf_flyer" 
                           class="form-control <?php echo isset($_SESSION['errors']['pdf_flyer']) ? 'is-invalid' : ''; ?>"
                           accept="application/pdf">
                    <?php if (isset($_SESSION['errors']['pdf_flyer'])): ?>
                        <div class="invalid-feedback"><?php echo $_SESSION['errors']['pdf_flyer']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <hr>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-2"></i>
                    <?php echo $translations['admin']['properties']['create']; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
// Clear session data
unset($_SESSION['errors']);
unset($_SESSION['old']);
?> 