        </main>
        
        <!-- Footer -->
        <footer class="site-footer py-5">
            <div class="container-fluid px-4">
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <h5 class="text-white mb-4">Industrial Properties</h5>
                        <p class="text-light opacity-75 mb-4">
                            <?php echo $translations['footer']['description']; ?>
                        </p>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h5 class="text-white mb-4"><?php echo $translations['menu']['properties']; ?></h5>
                        <div class="row">
                            <div class="col-6">
                                <ul class="list-unstyled footer-properties">
                                    <li>
                                        <a href="properties.php?type=manufacturing" class="footer-link">
                                            <?php echo $translations['property']['type']['manufacturing']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="properties.php?type=logistics" class="footer-link">
                                            <?php echo $translations['property']['type']['logistics']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="properties.php?type=office" class="footer-link">
                                            <?php echo $translations['property']['type']['office']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="properties.php?type=logistics_park" class="footer-link">
                                            <?php echo $translations['property']['type']['logistics_park']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="properties.php?type=specialized" class="footer-link">
                                            <?php echo $translations['property']['type']['specialized']; ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-6">
                                <ul class="list-unstyled footer-properties">
                                    <li>
                                        <a href="properties.php?type=logistics_terminal" class="footer-link">
                                            <?php echo $translations['property']['type']['logistics_terminal']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="properties.php?type=land" class="footer-link">
                                            <?php echo $translations['property']['type']['land']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="properties.php?type=food_industry" class="footer-link">
                                            <?php echo $translations['property']['type']['food_industry']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="properties.php?type=heavy_industry" class="footer-link">
                                            <?php echo $translations['property']['type']['heavy_industry']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="properties.php?type=tech_industry" class="footer-link">
                                            <?php echo $translations['property']['type']['tech_industry']; ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="properties.php?type=hotels" class="footer-link">
                                            <?php echo $translations['property']['type']['hotels']; ?>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <h5 class="text-white mb-4"><?php echo $translations['contact']['title']; ?></h5>
                        <ul class="list-unstyled contact-list">
                            <?php
                            // Зареждане на контактната информация от базата данни
                            $db = new PDO(
                                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                                DB_USER,
                                DB_PASS,
                                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                            );
                            
                            // Зареждане на основната контактна информация
                            $stmt = $db->query("SELECT * FROM contact_information WHERE type IN ('phone', 'email', 'address', 'working_hours') AND is_active = 1 ORDER BY sort_order");
                            $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($contacts as $contact): 
                                $value = $contact['value_' . $current_language] ?: $contact['value_bg'];
                            ?>
                                <li class="mb-3">
                                    <?php if ($contact['icon']): ?>
                                        <i class="<?php echo htmlspecialchars($contact['icon']); ?> me-2 opacity-75"></i>
                                    <?php endif; ?>
                                    <?php if ($contact['link']): ?>
                                        <a href="<?php echo htmlspecialchars($contact['link']); ?>" class="footer-link">
                                            <?php echo htmlspecialchars($value); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-light opacity-75"><?php echo htmlspecialchars($value); ?></span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Социални мрежи -->
                        <div class="social-links mt-4">
                            <h6 class="text-white mb-3">
                            <?php 
                                $followUsText = [
                                    'bg' => 'Последвайте ни',
                                    'en' => 'Follow us',
                                    'de' => 'Folgen Sie uns',
                                    'ru' => 'Подписывайтесь на нас'
                                ];
                                echo $followUsText[$current_language] ?? $followUsText['bg'];
                            ?>
                            </h6>
                            <div class="d-flex gap-3">
                                <?php
                                // Зареждане на социалните мрежи
                                $stmt = $db->query("SELECT * FROM contact_information WHERE type IN ('facebook', 'instagram', 'linkedin', 'twitter') AND is_active = 1 ORDER BY sort_order");
                                $socialLinks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($socialLinks as $social): 
                                    $iconClass = empty($social['value_bg']) ? 'social-icon disabled' : 'social-icon';
                                ?>
                                    <?php if (empty($social['value_bg'])): ?>
                                        <i class="<?php echo htmlspecialchars($social['icon']); ?> <?php echo $iconClass; ?>" 
                                           title="<?php echo ucfirst($social['type']); ?> (неактивен)"></i>
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars($social['value_bg']); ?>" 
                                           class="<?php echo $iconClass; ?>"
                                           target="_blank" 
                                           title="<?php echo ucfirst($social['type']); ?>">
                                            <i class="<?php echo htmlspecialchars($social['icon']); ?>"></i>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-4 opacity-25">
                <div class="text-center">
                    <small class="text-light opacity-75">&copy; <?php echo date('Y'); ?> Industrial Properties. <?php echo $translations['footer']['all_rights_reserved']; ?></small>
                </div>
            </div>
        </footer>
        
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
        <script src="js/main.js"></script>

<style>
.site-footer {
    background: linear-gradient(to right, #1a1c20, #2c3e50);
    color: #fff;
}

.footer-link {
    color: rgba(255, 255, 255, 0.75);
    text-decoration: none;
    transition: all 0.3s ease;
    display: block;
    padding: 0.4rem 0;
}

.footer-link:hover {
    color: #fff;
    transform: translateX(5px);
}

.contact-list li {
    display: flex;
    align-items: center;
}

.social-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-icon:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
    transform: translateY(-3px);
}

.social-icon.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.social-icon i {
    font-size: 1.2rem;
}

@media (max-width: 768px) {
    .site-footer {
        text-align: center;
    }
    
    .contact-list li {
        justify-content: center;
    }
    
    .social-links .d-flex {
        justify-content: center;
    }
}
</style>
    </body>
</html> 