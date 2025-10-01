    </div>
    
    <footer class="bg-light text-center py-3 mt-5">
        <div class="container">
            <p class="mb-0">&copy; 2025 XeDeep. Bản quyền thuộc về công ty.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Xác nhận trước khi xóa
        function confirmDelete(message = 'Bạn có chắc chắn muốn xóa?') {
            return confirm(message);
        }
        
        // Auto hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('alert-success')) {
                    alert.style.display = 'none';
                }
            });
        }, 3000);
    </script>
</body>
</html>