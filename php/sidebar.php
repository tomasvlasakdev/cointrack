<?php
function sidebar()
{
    ?>
    <nav class="sidebar">
        <div><a href="dashboard.php"><img src="../favicon.ico" id="home_icon" alt="logo reference to home"></a>
        </div>

        <div id="sidebar-menu">
            <ul>
                <li><a href="dashboard.php">Portfolio</a></li>
            </ul>
            <div class="dash"></div>
            <ul>
                <li><a href="transactions.php">Transactions</a></li>

            </ul>

        </div>

        <div class="account-section">
            <div id="acc_btn" onclick="account_dropdown()" class="dropdown" role="button" aria-haspopup="true"
                aria-expanded="false">Account ▾
            </div>
            <div id="acc_dropdown" class="dropdown-content" role="menu" aria-hidden="true">
                <div class="theme-toggle-container">
                    <span style="color: var(--text-secondary);">🌙 Dark Theme</span>
                    <div class="theme-toggle" id="theme-toggle" onclick="toggleTheme(event)">
                        <div class="theme-toggle-slider"></div>
                    </div>
                </div>
                <a href="settings.php" role="menuitem">⚙️ Settings</a>
                <a href="logout.php" class="logout" role="menuitem">🚪 Sign Out: <div id="email_signout">
                        <?= $_SESSION['email'] ?>
                    </div></a>
            </div>
        </div>


    </nav>
    <?php
}
?>

<script>



    function toggleTheme(event) {
        event.stopPropagation();
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';

        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        const toggle = document.getElementById('theme-toggle');
        if (newTheme === 'light') {
            toggle.classList.add('active');
        } else {
            toggle.classList.remove('active');
        }
    }


    document.addEventListener('DOMContentLoaded', () => {
        
        // This is needed to ensure the toggle button's visual state matches the theme on load.
        const savedTheme = document.documentElement.getAttribute('data-theme') || 'dark'; 
        
        const toggle = document.getElementById('theme-toggle');
        if (savedTheme === 'light') {
            toggle.classList.add('active');
        }

        const btn = document.getElementById('acc_btn');
        const menu = document.getElementById('acc_dropdown');

        // Open or close menu by clicking on the button
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            menu.classList.toggle('show');
            const opened = menu.classList.contains('show');
            btn.setAttribute('aria-expanded', opened ? 'true' : 'false');
            menu.setAttribute('aria-hidden', opened ? 'false' : 'true');
        });

        menu.addEventListener('click', (e) => {
            e.stopPropagation();
            if (e.target.matches('a[href="#"]')) {
                e.preventDefault();
            }
        });

        // Click outside of menu closes the menu
        window.addEventListener('click', () => {
            if (menu.classList.contains('show')) {
                menu.classList.remove('show');
                btn.setAttribute('aria-expanded', 'false');
                menu.setAttribute('aria-hidden', 'true');
            }
        });

        // Escape closes the menu
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && menu.classList.contains('show')) {
                menu.classList.remove('show');
                btn.setAttribute('aria-expanded', 'false');
                menu.setAttribute('aria-hidden', 'true');
            }
        });
    });
</script>