<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="../assets/images/logo.png" alt="Logo">
        </div>
        <button class="sidebar-toggle">
            <span></span>
        </button>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                <a href="index.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Табло</span>
                </a>
            </li>
            
            <?php if (checkPermission('manage_properties')): ?>
            <li class="<?php echo strpos($_SERVER['PHP_SELF'], 'properties') !== false ? 'active' : ''; ?>">
                <a href="properties.php">
                    <i class="bi bi-building"></i>
                    <span>Имоти</span>
                </a>
            </li>
            <?php endif; ?>
            
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'inquiries.php' ? 'active' : ''; ?>">
                <a href="inquiries.php">
                    <i class="bi bi-envelope"></i>
                    <span>Запитвания</span>
                </a>
            </li>
            
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'deals.php' ? 'active' : ''; ?>">
                <a href="deals.php">
                    <i class="bi bi-handshake"></i>
                    <span>Сделки</span>
                </a>
            </li>
            
            <?php if (isAdmin()): ?>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>">
                <a href="users.php">
                    <i class="bi bi-people"></i>
                    <span>Потребители</span>
                </a>
            </li>
            
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>">
                <a href="settings.php">
                    <i class="bi bi-gear-fill"></i>
                    <span>Настройки на сайта</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'blog.php' ? 'active' : ''; ?>">
                <a href="blog.php">
                    <i class="bi bi-newspaper"></i>
                    <span>Блог</span>
                </a>
            </li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <a href="logout.php" class="logout-btn">
            <i class="bi bi-box-arrow-right"></i>
            <span>Изход</span>
        </a>
    </div>
</aside> 

<!-- Divider -->
<hr class="sidebar-divider">

<!-- Heading -->
<div class="sidebar-heading">
    Настройки
</div>

<!-- Nav Item - Settings -->
<li class="nav-item">
    <a class="nav-link" href="settings.php">
        <i class="fas fa-fw fa-cog"></i>
        <span>Настройки на сайта</span>
    </a>
</li>

<!-- Divider -->
<hr class="sidebar-divider d-none d-md-block">

<!-- Sidebar Toggler (Sidebar) -->
<div class="text-center d-none d-md-inline">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div> 