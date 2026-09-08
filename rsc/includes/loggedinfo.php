<?php defined('includeExist') || die("<div class='auth-card'><span class='badge badge-danger mb-2'>WARNING</span><h2>Forbidden: Direct access prohibited</h2><em class='text-muted'>System Response Code</em></div>");?>

<?php

    $currentdir = substr(getcwd(), -5);//get the current working directory
    if ($currentdir == 'admin' || $currentdir == 'ports' || $currentdir == 'wsers' || $currentdir == 'helps') {
        $appendroot = '../';
    } else {
        $appendroot = '';
    }
    $is_logged_in = !empty($_SESSION['username']) || !empty($_SESSION['username_myacc']);
    $logout_msg = !empty($_SESSION['username']) 
        ? 'Are you sure you want to log out of your session?' 
        : 'Are you sure you want to log out of your patron account?';
?>
<div class="header-bar">
    <div class="header-brand-wrap">
        <a href="<?php echo $appendroot;?>opac.php?cl=ear" class="header-brand">
            <strong><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8');?></strong>
        </a>
        <button type="button" class="header-nav-toggle" id="headerNavToggle" onclick="toggleMobileNav();" aria-label="Toggle navigation" aria-expanded="false">
            <i class="fa-solid fa-bars" id="headerNavToggleIcon"></i>
        </button>
    </div>
    <div class="header-links" id="headerNavLinks">
        <?php
            if (!isset($_SESSION['username'])) {
                echo "<a href=\"".$appendroot."opac.php?cl=ear\" class=\"nav\"><i class=\"fa-solid fa-magnifying-glass me-1\"></i> Search</a>";
            } else {
                echo "<a href=\"".$appendroot."index2.php?cl=ear\" class=\"nav\"><i class=\"fa-solid fa-gauge-high me-1\"></i> Admin Dashboard</a>";
                echo "<a href=\"".$appendroot."helps/index.php\" class=\"nav\"><i class=\"fa-solid fa-book-bookmark me-1\"></i> Admin Guide</a>";
            }
        ?>
        <?php
            if (empty($_SESSION['username_myacc']) && empty($_SESSION['username'])) {
                echo "<a href=\"".$appendroot."index.php?modal=patron\" class=\"nav\"><i class=\"fa-solid fa-right-to-bracket me-1\"></i> Login to My Account</a>";
            } elseif (!empty($_SESSION['username_myacc'])) {
                $tiny_avatar = (function_exists('getPatronAvatarPath')) ? getPatronAvatarPath($_SESSION['username_myacc']) : null;
                echo "<a href=\"".$appendroot."logged.php\" class=\"nav nav-user-link\">";
                if ($tiny_avatar) {
                    echo "<img src=\"" . htmlspecialchars($tiny_avatar, ENT_QUOTES, 'UTF-8') . "\" alt=\"Avatar\" class=\"nav-avatar\" />";
                } else {
                    echo "<i class=\"fa-solid fa-user me-1\"></i>";
                }
                echo "<span>My Account</span></a>";
            }
        ?>
        <a href='<?php echo $appendroot;?>project.php' class="nav"><i class="fa-solid fa-diagram-project me-1"></i> <?= $core_product;?></a>
        <a href='<?php echo $appendroot;?>about.php' class="nav"><i class="fa-solid fa-circle-info me-1"></i> About</a>
        <?php if ($is_logged_in): ?>
            <button type="button" class="btn btn-danger btn-sm nav-logout-btn" onclick="openLogoutModal();" title="Logout">
                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
            </button>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleMobileNav() {
        var navLinks = document.getElementById('headerNavLinks');
        var navIcon = document.getElementById('headerNavToggleIcon');
        var navToggle = document.getElementById('headerNavToggle');
        if (navLinks) {
            var isOpen = navLinks.classList.toggle('show');
            if (navToggle) {
                navToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }
            if (navIcon) {
                if (isOpen) {
                    navIcon.classList.remove('fa-bars');
                    navIcon.classList.add('fa-xmark');
                } else {
                    navIcon.classList.remove('fa-xmark');
                    navIcon.classList.add('fa-bars');
                }
            }
        }
    }
</script>

<?php if ($is_logged_in): ?>
    <!-- Logout Confirmation Modal Dialog -->
    <div id="logoutModal" class="modal-backdrop" onclick="if(event.target===this) closeLogoutModal();">
        <div class="modal-dialog modal-sm">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-right-from-bracket text-danger me-2"></i> Confirm Logout</h5>
                <button type="button" class="modal-close-btn" onclick="closeLogoutModal();" title="Close dialog">&times;</button>
            </div>
            <div class="modal-body">
                <p class="mb-0 text-muted"><?php echo $logout_msg; ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" onclick="closeLogoutModal();">Cancel</button>
                <a href="<?php echo $appendroot;?>index.php" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> Yes, Logout
                </a>
            </div>
        </div>
    </div>

    <script>
        function openLogoutModal() {
            var m = document.getElementById('logoutModal');
            if (m) m.classList.add('show');
        }
        function closeLogoutModal() {
            var m = document.getElementById('logoutModal');
            if (m) m.classList.remove('show');
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
            }
        });
    </script>
<?php endif; ?>

