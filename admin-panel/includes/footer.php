            </main>
        </div>
    </div>

    <script>
        function toggleUserMenu() {
            const menu = document.getElementById('user-menu');
            menu.classList.toggle('hidden');
        }

        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const menu = document.getElementById('user-menu');
            const button = e.target.closest('button');
            if (!button || !button.onclick) {
                menu.classList.add('hidden');
            }
        });

        // Update page title dynamically
        function setPageTitle(title, subtitle = '') {
            document.getElementById('page-title').textContent = title;
            document.getElementById('page-subtitle').textContent = subtitle;
            document.title = title + ' - ' + '<?php echo ADMIN_TITLE; ?>';
        }

        // Show loading state
        function showLoading(element) {
            element.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
        }

        // Show success message
        function showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg max-w-sm`;
            
            if (type === 'success') {
                alertDiv.className += ' bg-green-500 text-white';
            } else if (type === 'error') {
                alertDiv.className += ' bg-red-500 text-white';
            } else if (type === 'warning') {
                alertDiv.className += ' bg-yellow-500 text-white';
            }
            
            alertDiv.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentElement) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        // Format numbers with commas
        function formatNumber(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        // Confirm delete
        function confirmDelete(message = 'Are you sure you want to delete this item?') {
            return confirm(message);
        }
    </script>
</body>
</html>