<?php
// Check if user is logged in
require_once '../logic/notification_logic.php';
$isLoggedIn = isset($_SESSION['isLoggedIn']);
?>

<nav class="bg-white shadow-lg sticky top-0">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
            <div class="flex items-center">
                <span class="text-2xl font-bold text-[#800000]">EMEMHS</span>
            </div>
            <div class="desktop-nav flex items-center space-x-8">
                <?php if ($isLoggedIn): ?>
                    <a href="student_dashboard.php" class="nav-link text-gray-700 hover:text-[#800000]">Home</a>
                    <a href="complaint-concern.php" class="nav-link text-gray-700 hover:text-[#800000]">Complaint/Concern</a>
                    <a href="lost_item.php" class="nav-link text-gray-700 hover:text-[#800000]">Lost Item</a>
                    <a href="notifications.php" class="nav-link text-gray-700 hover:text-[#800000] relative">
                        Notification
                        <?php 
                        $unread_count = getUnreadNotificationsCount($_SESSION['user']);
                        if ($unread_count > 0): 
                        ?>
                            <span class="notification-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="profile.php" class="nav-link text-gray-700 hover:text-[#800000]">Profile</a>
                    <a href="../logic/logout_logic.php" class="nav-link text-gray-700 hover:text-[#800000]">Logout</a>
                <?php else: ?>
                    <a href="index.php" class="nav-link text-gray-700 hover:text-[#800000]">Login</a>
                    <a href="register.php" class="nav-link text-gray-700 hover:text-[#800000]">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Menu Button -->
<button class="mobile-menu-button fixed top-4 right-4 z-50 p-2 rounded-lg bg-[#800000] text-white md:hidden">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<!-- Mobile Menu Overlay -->
<div class="mobile-menu-overlay"></div>

<!-- Mobile Menu -->
<div class="mobile-menu">
    <div class="flex justify-between items-center mb-8">
        <span class="text-2xl font-bold text-[#800000]">EMEMHS</span>
        <button class="close-menu p-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
    <div class="flex flex-col space-y-4">
        <?php if ($isLoggedIn): ?>
            <a href="student_dashboard.php" class="nav-link text-gray-700 hover:text-[#800000] text-lg py-2 px-4 rounded-lg hover:bg-gray-50">Home</a>
            <a href="complaint-concern.php" class="nav-link text-gray-700 hover:text-[#800000] text-lg py-2 px-4 rounded-lg hover:bg-gray-50">Complaint/Concern</a>
            <a href="lost_item.php" class="nav-link text-gray-700 hover:text-[#800000] text-lg py-2 px-4 rounded-lg hover:bg-gray-50">Lost Item</a>
            <a href="notifications.php" class="nav-link text-gray-700 hover:text-[#800000] text-lg py-2 px-4 rounded-lg hover:bg-gray-50">Notification</a>
            <a href="profile.php" class="nav-link text-gray-700 hover:text-[#800000] text-lg py-2 px-4 rounded-lg hover:bg-gray-50"></a>
            <a href="../logic/logout_logic.php" class="nav-link text-gray-700 hover:text-[#800000] text-lg py-2 px-4 rounded-lg hover:bg-gray-50">Profile</a>
        <?php else: ?>
            <a href="index.php" class="nav-link text-gray-700 hover:text-[#800000] text-lg py-2 px-4 rounded-lg hover:bg-gray-50">Login</a>
            <a href="register.php" class="nav-link text-gray-700 hover:text-[#800000] text-lg py-2 px-4 rounded-lg hover:bg-gray-50">Register</a>
        <?php endif; ?>
    </div>
</div>

<style>
    .nav-link {
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        color: #800000;
        transform: translateY(-2px);
    }

    /* Mobile menu styles */
    .mobile-menu {
        display: none;
        position: fixed;
        top: 0;
        right: 0;
        width: 100%;
        height: 100vh;
        background: white;
        z-index: 50;
        padding: 1rem;
        transform: translateX(100%);
        transition: transform 0.3s ease-in-out;
        box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
    }

    .mobile-menu.active {
        transform: translateX(0);
        display: block;
    }

    .mobile-menu-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 40;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    }

    .mobile-menu-overlay.active {
        display: block;
        opacity: 1;
    }

    @media (max-width: 768px) {
        .desktop-nav {
            display: none;
        }
        .mobile-menu-button {
            display: block;
        }
    }

    @media (min-width: 769px) {
        .mobile-menu {
            display: none !important;
        }
        .mobile-menu-button {
            display: none;
        }
        .mobile-menu-overlay {
            display: none !important;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu functionality
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const closeMenuButton = document.querySelector('.close-menu');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileMenuOverlay = document.querySelector('.mobile-menu-overlay');
    const body = document.body;

    // Function to open menu
    function openMenu() {
        mobileMenu.classList.add('active');
        mobileMenuOverlay.classList.add('active');
        body.style.overflow = 'hidden'; // Prevent scrolling when menu is open
    }

    // Function to close menu
    function closeMenu() {
        mobileMenu.classList.remove('active');
        mobileMenuOverlay.classList.remove('active');
        body.style.overflow = ''; // Restore scrolling
    }

    // Event listeners
    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', openMenu);
    }

    if (closeMenuButton) {
        closeMenuButton.addEventListener('click', closeMenu);
    }

    // Close menu when clicking on overlay
    if (mobileMenuOverlay) {
        mobileMenuOverlay.addEventListener('click', closeMenu);
    }

    // Close menu when clicking on a menu link
    const menuLinks = mobileMenu.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    // Close menu when pressing Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
            closeMenu();
        }
    });
});
</script>