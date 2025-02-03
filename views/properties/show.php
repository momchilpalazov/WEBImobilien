<?php
/**
 * Property details view
 * @var array $property
 * @var array $translations
 */

// Start output buffering
ob_start();

require_once __DIR__ . '/../layout/header.php';
?>

<div class="container my-5">
    <div class="row">
        <!-- Property Images Gallery -->
        <div class="col-md-8">
            <?php if (!empty($images)): ?>
                <div id="propertyGallery" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                <img src="/uploads/properties/<?= $image['filename'] ?>" class="d-block w-100" alt="<?= htmlspecialchars($property["title_{$currentLanguage}"]) ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($images) > 1): ?>
                        <button class="carousel-control-prev" type="button" data-bs-target="#propertyGallery" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#propertyGallery" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Property Details -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h1 class="card-title h4"><?= htmlspecialchars($property["title_{$currentLanguage}"]) ?></h1>
                    <p class="text-muted mb-4"><?= htmlspecialchars($property["location_{$currentLanguage}"]) ?></p>

                    <div class="d-flex justify-content-between mb-4">
                        <span class="badge bg-primary">
                            <?= $translations['property']['type'][$property['type']] ?>
                        </span>
                        <span class="badge bg-<?= $property['status'] === 'available' ? 'success' : 'warning' ?>">
                            <?= $translations['property']['status'][$property['status']] ?>
                        </span>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <i class="bi bi-rulers mb-2"></i>
                                <div class="small text-muted"><?= $translations['property']['filter']['area'] ?></div>
                                <strong><?= number_format($property['area']) ?> m²</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-3 text-center">
                                <i class="bi bi-currency-euro mb-2"></i>
                                <div class="small text-muted"><?= $translations['property']['filter']['price'] ?></div>
                                <strong><?= number_format($property['price']) ?> €</strong>
                            </div>
                        </div>
                    </div>

                    <?php if ($property['pdf_flyer']): ?>
                        <a href="/uploads/flyers/<?= $property['pdf_flyer'] ?>" class="btn btn-outline-primary w-100 mb-3" target="_blank">
                            <i class="bi bi-file-pdf me-2"></i> <?= $translations['property']['download_pdf'] ?>
                        </a>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#brochureModal">
                                    <i class="fas fa-file-pdf me-2"></i>
                                    Генерирай брошура
                                </button>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#contractModal">
                                    <i class="fas fa-file-contract me-2"></i>
                                    Генерирай договор
                                </button>
                                <button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#marketingModal">
                                    <i class="fas fa-bullhorn me-2"></i>
                                    Маркетингови материали
                                </button>
                                <button type="button" class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#viewingModal">
                                    <i class="fas fa-calendar-plus me-2"></i>
                                    Насрочи оглед
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#contactModal">
                        <?= $translations['contact']['contact_us'] ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Description -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 mb-4"><?= $translations['property']['description'] ?></h2>
                    <?= nl2br(htmlspecialchars($property["description_{$currentLanguage}"])) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= $translations['contact']['title'] ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success'] ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php unset($_SESSION['errors']); ?>
                    </div>
                <?php endif; ?>

                <form action="/properties/<?= $property['id'] ?>/contact" method="post" id="contact-form">
                    <div class="mb-3">
                        <label class="form-label"><?= $translations['contact']['name'] ?></label>
                        <input type="text" name="name" class="form-control" value="<?= $_SESSION['old']['name'] ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $translations['contact']['email'] ?></label>
                        <input type="email" name="email" class="form-control" value="<?= $_SESSION['old']['email'] ?? '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $translations['contact']['phone'] ?></label>
                        <input type="tel" name="phone" class="form-control" value="<?= $_SESSION['old']['phone'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?= $translations['contact']['message'] ?></label>
                        <textarea name="message" class="form-control" rows="4" required><?= $_SESSION['old']['message'] ?? '' ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <?= $translations['contact']['send'] ?>
                    </button>
                </form>
                <?php unset($_SESSION['old']); ?>
            </div>
        </div>
    </div>
</div>

<!-- Image Lightbox Modal -->
<div class="modal fade" id="imageLightbox" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="lightboxImage" src="" class="img-fluid" alt="">
            </div>
        </div>
    </div>
</div>

<!-- Добавяме модалния прозорец за договори -->
<?php include '_contract_form.php'; ?>
<?php include '_marketing_form.php'; ?>
<?php include '_viewing_form.php'; ?>

<?php
// Get the buffered content
$content = ob_get_clean();

// Include the layout
require __DIR__ . '/../layouts/main.php';

require_once __DIR__ . '/../layout/footer.php';

// Add JavaScript
echo '<script src="/js/properties.js"></script>';
?> 