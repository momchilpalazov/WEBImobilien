<?php if ($pagination['total'] > 1): ?>
    <nav aria-label="<?= $translations['pagination']['title'] ?>">
        <ul class="pagination justify-content-center">
            <!-- Previous page -->
            <li class="page-item <?= !$pagination['has_prev'] ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['current'] - 1 ?>" <?= !$pagination['has_prev'] ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                    <i class="bi bi-chevron-left"></i>
                    <?= $translations['pagination']['previous'] ?>
                </a>
            </li>

            <!-- Page numbers -->
            <?php
            $start = max(1, $pagination['current'] - 2);
            $end = min($pagination['total'], $pagination['current'] + 2);
            
            if ($start > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=1">1</a>
                </li>
                <?php if ($start > 2): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif;
            endif;

            for ($i = $start; $i <= $end; $i++): ?>
                <li class="page-item <?= $i === $pagination['current'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor;

            if ($end < $pagination['total']): ?>
                <?php if ($end < $pagination['total'] - 1): ?>
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                <?php endif; ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $pagination['total'] ?>"><?= $pagination['total'] ?></a>
                </li>
            <?php endif; ?>

            <!-- Next page -->
            <li class="page-item <?= !$pagination['has_next'] ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $pagination['current'] + 1 ?>" <?= !$pagination['has_next'] ? 'tabindex="-1" aria-disabled="true"' : '' ?>>
                    <?= $translations['pagination']['next'] ?>
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?> 