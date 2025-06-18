/**
 * Advanced Notification and Toast Manager for the AI Chat Assistant.
 *
 * This script handles browser notifications, toast messages, and manages
 * user permissions for a seamless and interactive user experience.
 */

// Self-invoking function to encapsulate the logic
(function(window) {
    'use strict';

    // --- Toast Notification Manager ---
    const toastManager = {
        container: null,
        
        // Initialize the toast container
        init: function() {
            if (this.container) return;
            
            this.container = document.createElement('div');
            this.container.id = 'toast-container';
            this.container.className = 'fixed bottom-5 right-5 z-[100] w-80 space-y-2';
            document.body.appendChild(this.container);
        },

        /**
         * Show a toast notification.
         * @param {string} message - The message to display.
         * @param {string} type - 'success', 'error', 'warning', 'info'.
         * @param {number} duration - Duration in milliseconds.
         * @param {Array} actions - Array of action objects { text, onClick }.
         */
        show: function(message, type = 'info', duration = 4000, actions = []) {
            this.init();

            const toast = document.createElement('div');
            toast.className = `toast-message flex items-start p-4 rounded-lg shadow-lg text-white transition-all duration-300 transform translate-x-full animate-slide-in ${this.getToastColor(type)}`;
            
            const icon = this.getToastIcon(type);
            const content = `
                <div class="flex-shrink-0">${icon}</div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">${message}</p>
                    ${actions.length > 0 ? this.createActions(actions) : ''}
                </div>
                <button class="ml-4 flex-shrink-0 text-white opacity-70 hover:opacity-100">&times;</button>
            `;
            
            toast.innerHTML = content;
            
            // Close button functionality
            toast.querySelector('button').onclick = () => this.hide(toast);
            
            // Add to container and set timeout for removal
            this.container.appendChild(toast);
            setTimeout(() => this.hide(toast), duration);
            
            return toast;
        },

        // Hide and remove a toast
        hide: function(toast) {
            if (!toast || !toast.parentElement) return;
            
            toast.classList.add('animate-slide-out');
            toast.addEventListener('animationend', () => {
                if (toast.parentElement) {
                    toast.parentElement.removeChild(toast);
                }
            });
        },

        // Helper to get toast color based on type
        getToastColor: function(type) {
            switch (type) {
                case 'success': return 'bg-green-500';
                case 'error': return 'bg-red-500';
                case 'warning': return 'bg-yellow-500';
                default: return 'bg-blue-500';
            }
        },

        // Helper to get toast icon based on type
        getToastIcon: function(type) {
            const iconMap = {
                success: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                error: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                warning: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                info: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            };
            return iconMap[type] || iconMap['info'];
        },

        // Helper to create action buttons for toasts
        createActions: function(actions) {
            let buttonsHTML = '<div class="mt-2 space-x-2">';
            actions.forEach(action => {
                buttonsHTML += `<button class="font-semibold underline hover:no-underline">${action.text}</button>`;
            });
            buttonsHTML += '</div>';
            return buttonsHTML;
        }
    };

    // --- Web Notification Manager ---
    const notificationManager = {
        permission: 'default',

        // Initialize and check for notification support
        init: function() {
            if (!('Notification' in window)) {
                console.warn('This browser does not support desktop notifications.');
                this.permission = 'denied';
                return;
            }
            this.permission = Notification.permission;
        },

        // Request permission from the user
        requestPermission: function() {
            if (this.permission === 'granted') {
                toastManager.show('Notifications are already enabled.', 'success');
                return Promise.resolve('granted');
            }
            
            return Notification.requestPermission().then(permission => {
                this.permission = permission;
                if (permission === 'granted') {
                    toastManager.show('Notifications enabled!', 'success');
                    this.showNotification('Great!', { body: 'You will now receive updates.' });
                } else {
                    toastManager.show('Notifications permission denied.', 'warning');
                }
                return permission;
            });
        },

        /**
         * Show a desktop notification.
         * @param {string} title - The notification title.
         * @param {object} options - Notification options (body, icon, tag, etc.).
         */
        showNotification: function(title, options = {}) {
            if (this.permission !== 'granted') {
                console.warn('Notification permission not granted.');
                return;
            }

            const notification = new Notification(title, {
                body: options.body || '',
                icon: options.icon || '/assets/icons/icon-192x192.png', // Default icon
                tag: options.tag || 'ai-chat-notification', // Tag to prevent spam
                renotify: true,
                silent: false
            });

            // Handle notification click
            notification.onclick = (event) => {
                parent.focus();
                window.focus();
                event.target.close();
            };
        },

        /**
         * Show a notification only if the document is hidden.
         * @param {string} title - The notification title.
         * @param {object} options - Notification options.
         */
        showNotificationIfHidden: function(title, options = {}) {
            if (document.hidden) {
                this.showNotification(title, options);
            }
        }
    };

    // --- Global Integration ---
    
    // Initialize managers on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', () => {
        toastManager.init();
        notificationManager.init();

        // Add some CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slide-in {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            .animate-slide-in { animation: slide-in 0.5s forwards; }
            
            @keyframes slide-out {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .animate-slide-out { animation: slide-out 0.5s forwards; }
        `;
        document.head.appendChild(style);
    });

    // Expose managers to the window object
    window.toastManager = toastManager;
    window.notificationManager = notificationManager;

    // Global flag to control notifications for new messages
    window.showNotificationOnResponse = true;

    // Update flag when window visibility changes
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            window.showNotificationOnResponse = true;
        }
    });

})(window);