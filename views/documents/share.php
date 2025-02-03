<?php
use App\Utils\Format;
require_once 'views/layout/header.php';
?>

<div class="container-fluid px-4">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mt-4">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php elseif ($share['is_expired']): ?>
        <div class="alert alert-warning mt-4">
            <i class="fas fa-exclamation-circle me-2"></i>
            Срокът за достъп до този документ е изтекъл на <?= Format::date($share['expiration_date'], 'd.m.Y H:i') ?>
        </div>
    <?php endif; ?>

    <div class="card my-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-share-alt me-2"></i>
                Споделен документ
            </h5>
        </div>
        <div class="card-body">
            <!-- Информация за документа -->
            <div class="d-flex align-items-center mb-4">
                <i class="fas fa-file-<?= Format::fileIcon($document['file_type']) ?> fa-3x text-muted me-3"></i>
                <div>
                    <h4 class="mb-1"><?= htmlspecialchars($document['title']) ?></h4>
                    <div class="text-muted">
                        <?php
                        $categories = [
                            'contract' => 'Договор',
                            'deed' => 'Нотариален акт',
                            'certificate' => 'Сертификат',
                            'permit' => 'Разрешително',
                            'tax' => 'Данъчен документ',
                            'insurance' => 'Застраховка',
                            'appraisal' => 'Оценка',
                            'other' => 'Друго'
                        ];
                        echo $categories[$document['category']] ?? 'Неизвестно';
                        ?>
                    </div>
                </div>
            </div>

            <?php if (!empty($document['description'])): ?>
                <div class="mb-4">
                    <h6>Описание</h6>
                    <p class="mb-0">
                        <?= nl2br(htmlspecialchars($document['description'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Информация за документа</h6>
                    <div class="text-muted">
                        <div>Размер: <?= Format::fileSize($document['file_size']) ?></div>
                        <div>Създаден на: <?= Format::date($document['created_at']) ?></div>
                        <div>Създаден от: <?= htmlspecialchars($document['created_by_name']) ?></div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h6>Информация за споделянето</h6>
                    <div class="text-muted">
                        <div>Споделено с: <?= htmlspecialchars($share['shared_with_email']) ?></div>
                        <div>Споделено от: <?= htmlspecialchars($share['shared_by_name']) ?></div>
                        <div>Споделено на: <?= Format::date($share['created_at'], 'd.m.Y H:i') ?></div>
                        <?php if ($share['expiration_date']): ?>
                            <div>Валидно до: <?= Format::date($share['expiration_date'], 'd.m.Y H:i') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (!$share['is_expired']): ?>
                <!-- Преглед на документа -->
                <div class="mb-4">
                    <h6 class="mb-3">Преглед на документа</h6>
                    <div class="ratio ratio-16x9 mb-3">
                        <iframe src="/documents/preview/<?= $document['id'] ?>?share_id=<?= $share['id'] ?>" 
                                class="border rounded"
                                allowfullscreen></iframe>
                    </div>
                </div>

                <!-- Действия -->
                <div class="d-flex gap-2">
                    <?php if (in_array('download', $share['permissions'])): ?>
                        <a href="/documents/download/<?= $document['id'] ?>?share_id=<?= $share['id'] ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-download"></i> Изтегли
                        </a>
                    <?php endif; ?>

                    <?php if (in_array('print', $share['permissions'])): ?>
                        <a href="/documents/print/<?= $document['id'] ?>?share_id=<?= $share['id'] ?>" 
                           class="btn btn-outline-primary"
                           target="_blank">
                            <i class="fas fa-print"></i> Принтирай
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- История на достъпа -->
            <div class="mt-4">
                <h6 class="mb-3">История на достъпа</h6>
                <?php if (empty($access_logs)): ?>
                    <p class="text-muted mb-0">Няма история на достъпа</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Действие</th>
                                    <th>IP адрес</th>
                                    <th>Браузър</th>
                                    <th>Дата и час</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($access_logs as $log): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            $actionText = [
                                                'view' => 'Преглед',
                                                'download' => 'Изтегляне',
                                                'print' => 'Принтиране'
                                            ][$log['action_type']] ?? 'Неизвестно';
                                            echo $actionText;
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($log['ip_address']) ?></td>
                                        <td><?= htmlspecialchars($log['user_agent']) ?></td>
                                        <td><?= Format::date($log['created_at'], 'd.m.Y H:i:s') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'views/layout/footer.php'; ?> 