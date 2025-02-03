        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="/js/admin/script.js"></script>
    <?php if (str_contains($_SERVER['REQUEST_URI'], '/properties')): ?>
        <script src="/js/admin/properties.js"></script>
    <?php endif; ?>
</body>
</html> 