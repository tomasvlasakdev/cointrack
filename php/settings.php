<?php
session_start();
include_once 'sidebar.php';
include_once '../config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Portfolio Tracker</title>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            }
        })();
    </script>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Anta&display=swap" rel="stylesheet">
    <link rel="icon" href="../favicon.ico">

</head>

<body>
    <div class="main-container">
        <?php sidebar(); ?>

        <div class="content">
            <div class="center">
                <div class="middle">
                    <div class="settings-container">
                        <h1>Settings</h1>

                        <!-- Appearance Section -->
                        <div class="settings-section">
                            <h2>Appearance</h2>
                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <span class="settings-item-title">Theme</span>
                                    <span class="settings-item-description">Switch between light and dark mode</span>
                                </div>
                                <div class="theme-toggle" id="theme-toggle-settings" onclick="toggleTheme()">
                                    <div class="theme-toggle-slider"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Section -->
                        <div class="settings-section">
                            <h2>Account Information</h2>

                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <span class="settings-item-title">Username</span>
                                    <span class="settings-item-description">Change your display name</span>
                                </div>
                                <button class="settings-btn" onclick="showModal('username')">Change</button>
                            </div>

                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <span class="settings-item-title">Email</span>
                                    <span class="settings-item-description">Update your email address</span>
                                </div>
                                <button class="settings-btn" onclick="showModal('email')">Change</button>
                            </div>

                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <span class="settings-item-title">Password</span>
                                    <span class="settings-item-description">Update your password</span>
                                </div>
                                <button class="settings-btn" onclick="showModal('password')">Change</button>
                            </div>
                        </div>

                        <!-- Downloads -->
                        <div class="settings-section">
                            <h2>Downloads</h2>

                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <span class="settings-item-title">Export Data</span>
                                    <span class="settings-item-description">Download all your portfolio data</span>
                                </div>
                                <button class="settings-btn" onclick="exportData()">Export</button>
                            </div>
                        </div>

                        <!-- Danger Zone -->
                        <div class="settings-section">
                            <h2 style="color: #ff4d4d;">Danger Zone</h2>

                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <span class="settings-item-title">Delete Account</span>
                                    <span class="settings-item-description">Permanently delete your account and all
                                        data</span>
                                </div>
                                <button class="settings-btn danger" onclick="showModal('delete')">Delete
                                    Account</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for forms -->
    <div id="settingsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div id="modalBody"></div>
        </div>
    </div>

    <script>
        // Theme toggle
        function toggleTheme() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

            updateThemeToggles(newTheme);
        }

        function updateThemeToggles(theme) {
            const toggles = document.querySelectorAll('.theme-toggle');
            toggles.forEach(toggle => {
                if (theme === 'light') {
                    toggle.classList.add('active');
                } else {
                    toggle.classList.remove('active');
                }
            });
        }

        // Load theme on page load
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeToggles(savedTheme);
        });

        // Modal functions
        function showModal(type) {
            const modal = document.getElementById('settingsModal');
            const modalBody = document.getElementById('modalBody');

            let content = '';

            switch (type) {
                case 'username':
                    content = `
                        <form class="modal-form" onsubmit="return submitForm(event, 'username')">
                            <h2>Change Username</h2>
                            <div>
                                <label>New Username</label>
                                <input type="text" name="username" required minlength="3" maxlength="30" pattern="[a-zA-Z0-9_]+">
                                <small style="color: var(--text-muted); font-size: 0.85rem;">3-30 characters, letters, numbers, and underscores only</small>
                            </div>
                            <div>
                                <label>Confirm Password</label>
                                <input type="password" name="password" required>
                            </div>
                            <div class="modal-buttons">
                                <button type="submit" class="modal-submit">Save Changes</button>
                                <button type="button" class="modal-cancel" onclick="closeModal()">Cancel</button>
                            </div>
                        </form>
                    `;
                    break;

                case 'email':
                    content = `
                        <form class="modal-form" onsubmit="return submitForm(event, 'email')">
                            <h2>Change Email</h2>
                            <div>
                                <label>New Email Address</label>
                                <input type="email" name="email" required>
                            </div>
                            <div>
                                <label>Confirm Password</label>
                                <input type="password" name="password" required>
                            </div>
                            <div class="modal-buttons">
                                <button type="submit" class="modal-submit">Save Changes</button>
                                <button type="button" class="modal-cancel" onclick="closeModal()">Cancel</button>
                            </div>
                        </form>
                    `;
                    break;

                case 'password':
                    content = `
                        <form class="modal-form" onsubmit="return submitForm(event, 'password')">
                            <h2>Change Password</h2>
                            <div>
                                <label>Current Password</label>
                                <input type="password" name="current_password" required>
                            </div>
                            <div>
                                <label>New Password</label>
                                <input type="password" name="new_password" required minlength="8">
                                <small style="color: var(--text-muted); font-size: 0.85rem;">At least 8 characters with uppercase, lowercase, and numbers</small>
                            </div>
                            <div>
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" required>
                            </div>
                            <div class="modal-buttons">
                                <button type="submit" class="modal-submit">Save Changes</button>
                                <button type="button" class="modal-cancel" onclick="closeModal()">Cancel</button>
                            </div>
                        </form>
                    `;
                    break;

                case 'delete':
                    content = `
                        <form class="modal-form" onsubmit="return submitForm(event, 'delete')">
                            <h2 style="color: #ff4d4d;">Delete Account</h2>
                            <p style="color: var(--text-secondary);">⚠️ This action cannot be undone. All your data will be permanently deleted.</p>
                            <div>
                                <label>Type "DELETE" to confirm</label>
                                <input type="text" name="confirm" required pattern="DELETE" placeholder="DELETE">
                            </div>
                            <div>
                                <label>Enter your password</label>
                                <input type="password" name="password" required>
                            </div>
                            <div class="modal-buttons">
                                <button type="submit" class="modal-submit" style="background: #ff4d4d;">Delete My Account</button>
                                <button type="button" class="modal-cancel" onclick="closeModal()">Cancel</button>
                            </div>
                        </form>
                    `;
                    break;

                case 'disable2fa':
                    content = `
                        <form class="modal-form" onsubmit="return disable2FA(event)">
                            <h2>Disable Two-Factor Authentication</h2>
                            <p style="color: var(--text-secondary);">Are you sure you want to disable 2FA? This will make your account less secure.</p>
                            <div>
                                <label>Enter your password to confirm</label>
                                <input type="password" name="password" required>
                            </div>
                            <div class="modal-buttons">
                                <button type="submit" class="modal-submit" style="background: #ff4d4d;">Disable 2FA</button>
                                <button type="button" class="modal-cancel" onclick="closeModal()">Cancel</button>
                            </div>
                        </form>
                    `;
                    break;
            }

            modalBody.innerHTML = content;
            modal.style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('settingsModal').style.display = 'none';
        }

        function submitForm(event, type) {
            event.preventDefault();

            const formData = new FormData(event.target);
            const submitBtn = event.target.querySelector('.modal-submit');
            const originalText = submitBtn.textContent;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            fetch(`update_${type}.php`, {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('success', data.message);
                        closeModal();

                        if (type === 'delete') {
                            setTimeout(() => {
                                window.location.href = 'logout.php';
                            }, 1500);
                        } else {
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        }
                    } else {
                        showMessage('error', data.message);
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('error', 'An error occurred. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });

            return false;
        }

        function toggleNotifications(type) {
            const btn = document.getElementById(`${type}-notif-btn`);
            const originalText = btn.textContent;

            btn.disabled = true;
            btn.textContent = 'Processing...';

            const formData = new FormData();
            formData.append('type', type);

            fetch('toggle_notifications.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('success', data.message);
                        btn.textContent = data.enabled ? 'Disable' : 'Enable';
                        btn.setAttribute('data-enabled', data.enabled ? 'true' : 'false');
                    } else {
                        showMessage('error', data.message);
                        btn.textContent = originalText;
                    }
                    btn.disabled = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('error', 'An error occurred. Please try again.');
                    btn.textContent = originalText;
                    btn.disabled = false;
                });
        }

        function setup2FA() {
            const formData = new FormData();
            formData.append('action', 'generate');

            fetch('setup_2fa.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        show2FASetup(data);
                    } else {
                        showMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('error', 'An error occurred. Please try again.');
                });
        }

        function show2FASetup(data) {
            const modal = document.getElementById('settingsModal');
            const modalBody = document.getElementById('modalBody');

            const backupCodesList = data.backup_codes.map(code => `<li>${code}</li>`).join('');

            const content = `
                <div class="modal-form">
                    <h2>Setup Two-Factor Authentication</h2>
                    <p style="color: var(--text-secondary);">Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.)</p>
                    
                    <div class="qr-code-container">
                        <img src="${data.qr_code_url}" alt="QR Code">
                        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 10px;">Secret Key: <code style="background: var(--bg-primary); padding: 5px; border-radius: 4px;">${data.secret}</code></p>
                    </div>
                    
                    <div class="backup-codes">
                        <h3>⚠️ Backup Codes</h3>
                        <p style="font-size: 0.85rem; color: var(--text-muted);">Save these codes in a safe place. You can use them to access your account if you lose your device.</p>
                        <ul>${backupCodesList}</ul>
                    </div>
                    
                    <form onsubmit="return verify2FA(event)">
                        <div>
                            <label>Enter the 6-digit code from your app</label>
                            <input type="text" name="code" required pattern="[0-9]{6}" maxlength="6" placeholder="000000" style="text-align: center; font-size: 1.2rem; letter-spacing: 0.5rem;">
                        </div>
                        <div class="modal-buttons">
                            <button type="submit" class="modal-submit">Verify & Enable</button>
                            <button type="button" class="modal-cancel" onclick="closeModal()">Cancel</button>
                        </div>
                    </form>
                </div>
            `;

            modalBody.innerHTML = content;
            modal.style.display = 'flex';
        }

        function verify2FA(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            formData.append('action', 'verify');

            const submitBtn = event.target.querySelector('.modal-submit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Verifying...';

            fetch('setup_2fa.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('success', data.message);
                        closeModal();
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showMessage('error', data.message);
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Verify & Enable';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('error', 'An error occurred. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Verify & Enable';
                });

            return false;
        }

        function disable2FA(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            formData.append('action', 'disable');

            const submitBtn = event.target.querySelector('.modal-submit');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            fetch('setup_2fa.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('success', data.message);
                        closeModal();
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showMessage('error', data.message);
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Disable 2FA';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('error', 'An error occurred. Please try again.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Disable 2FA';
                });

            return false;
        }

        function exportData() {
            showMessage('success', 'Preparing your data export...');
            window.location.href = 'export_data.php';
        }

        function showMessage(type, message) {
            const existingMessage = document.querySelector('.success-message, .error-message');
            if (existingMessage) {
                existingMessage.remove();
            }

            const messageDiv = document.createElement('div');
            messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
            messageDiv.textContent = message;
            messageDiv.style.position = 'fixed';
            messageDiv.style.top = '20px';
            messageDiv.style.right = '20px';
            messageDiv.style.zIndex = '3000';
            messageDiv.style.minWidth = '300px';
            messageDiv.style.animation = 'slideIn 0.3s ease';

            document.body.appendChild(messageDiv);

            setTimeout(() => {
                messageDiv.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => {
                    messageDiv.remove();
                }, 300);
            }, 3000);
        }

        // Close modal when clicking outside
        window.onclick = function (event) {
            const modal = document.getElementById('settingsModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        // Add animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            @keyframes slideOut {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(400px);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>

</html>