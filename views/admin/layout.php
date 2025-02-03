<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title ?? 'Административен панел') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Admin CSS -->
    <link href="/css/admin.css" rel="stylesheet">
    
    <?= $this->section('styles') ?>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="active">
            <div class="sidebar-header">
                <h3>Имоти ООД</h3>
            </div>

            <ul class="list-unstyled components">
                <?php foreach ($menu as $item): ?>
                <li class="<?= isset($item['submenu']) ? 'has-submenu' : '' ?>">
                    <a href="<?= $item['url'] ?>" class="<?= $_SERVER['REQUEST_URI'] === $item['url'] ? 'active' : '' ?>">
                        <i class="<?= $item['icon'] ?>"></i>
                        <span><?= $item['title'] ?></span>
                        <?php if (isset($item['submenu'])): ?>
                        <i class="fas fa-chevron-down submenu-icon"></i>
                        <?php endif ?>
                    </a>
                    <?php if (isset($item['submenu'])): ?>
                    <ul class="collapse list-unstyled" id="submenu-<?= $loop->index ?>">
                        <?php foreach ($item['submenu'] as $subitem): ?>
                        <li>
                            <a href="<?= $subitem['url'] ?>" class="<?= $_SERVER['REQUEST_URI'] === $subitem['url'] ? 'active' : '' ?>">
                                <?= $subitem['title'] ?>
                            </a>
                        </li>
                        <?php endforeach ?>
                    </ul>
                    <?php endif ?>
                </li>
                <?php endforeach ?>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-user"></i>
                                    <?= htmlspecialchars($user['name']) ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="/admin/profile">Профил</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="/admin/logout">Изход</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Flash Messages -->
            <?php if (!empty($flash)): ?>
                <?php foreach ($flash as $type => $message): ?>
                    <?php
                    $alertClass = match($type) {
                        'error' => 'alert-danger',
                        'success' => 'alert-success',
                        'info' => 'alert-info',
                        'warning' => 'alert-warning',
                        default => 'alert-info'
                    };
                    ?>
                    <div class="alert <?= $alertClass ?> alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endforeach ?>
            <?php endif ?>

            <!-- Main Content -->
            <?= $this->section('content') ?>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle sidebar
        document.getElementById('sidebarCollapse').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });
        
        // Handle submenu toggle
        document.querySelectorAll('.has-submenu > a').forEach(function(item) {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.parentElement.querySelector('.collapse').classList.toggle('show');
                this.querySelector('.submenu-icon').classList.toggle('fa-chevron-down');
                this.querySelector('.submenu-icon').classList.toggle('fa-chevron-up');
            });
        });
        
        // Auto-hide alerts
        document.querySelectorAll('.alert').forEach(function(alert) {
            setTimeout(function() {
                let bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });
    });
    </script>
    
    <?= $this->section('scripts') ?>
</body>
</html> 