<?php
// Check if user is logged in
require_once '../logic/notification_logic.php';
$isLoggedIn = isset($_SESSION['isLoggedIn']);

// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Mobile-First Navigation -->
<nav class="bg-white shadow-lg sticky top-0 z-50">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo Section -->
            <div class="flex items-center">
                <a href="student_dashboard.php" class="flex items-center no-underline">
                    <img src="../image/ememhs-logo.png" alt="EMEMHS Logo" class="h-8 w-auto sm:h-10 mr-2">
                    <div>
                        <h3 class="font-bold text-md">EMEMHS</h3>
                        <p class="text-xs opacity-90">Guidance System</p>
                    </div>
                </a>
            </div>

            <!-- Desktop Navigation -->
            <div class="hidden lg:flex items-center space-x-6">
                <?php if ($isLoggedIn): ?>
                    <a href="student_dashboard.php" class="nav-link <?php echo ($current_page == 'student_dashboard.php') ? 'active' : ''; ?>">
                        
                        Home
                    </a>
                    <a href="complaint-concern.php" class="nav-link <?php echo ($current_page == 'complaint-concern.php') ? 'active' : ''; ?>">
                       
                        Complaints
                    </a>
                    <a href="reschedule-request.php" class="nav-link <?php echo ($current_page == 'reschedule-request.php') ? 'active' : ''; ?>">
                       
                        Reschedule
                    </a>
                    <a href="lost_item.php" class="nav-link <?php echo ($current_page == 'lost_item.php') ? 'active' : ''; ?>">
                        
                        Lost Items
                    </a>
                <?php endif; ?>
            </div>

            <!-- Right Section -->
            <div class="flex items-center space-x-3">
                <?php if ($isLoggedIn): ?>
                    <!-- Notifications (Desktop & Mobile) -->
                    <a href="notifications.php" class="relative p-2 text-gray-600 hover:text-[#800000] transition-colors <?php echo ($current_page == 'notifications.php') ? 'text-[#800000]' : ''; ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <?php
                        $unread_count = getUnreadNotificationsCount($_SESSION['user']);
                        if ($unread_count > 0):
                        ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-medium"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Profile (Desktop Only) -->
                    <a href="profile.php" class="hidden lg:flex items-center p-2 text-gray-600 hover:text-[#800000] transition-colors <?php echo ($current_page == 'profile.php') ? 'text-[#800000]' : ''; ?>">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </a>

                    <a href="../logic/logout_logic.php" class="text-sm bg-[#800000] text-white px-3 py-1.5 rounded-lg hover:bg-[#600000] transition-colors duration-300 hidden lg:block">Logout</a>

                    <!-- Mobile Menu Button -->
                    <button class="lg:hidden mobile-menu-btn p-2 rounded-lg text-gray-600 hover:text-[#800000] hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] focus:ring-opacity-20">
                        <svg class="w-6 h-6 hamburger-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path class="line-1" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16" />
                            <path class="line-2" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16" />
                            <path class="line-3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 18h16" />
                        </svg>
                    </button>
                <?php else: ?>
                    <a href="index.php" class="text-sm text-gray-600 hover:text-[#800000] px-3 py-2 <?php echo ($current_page == 'index.php') ? 'text-[#800000] font-medium' : ''; ?>">Login</a>
                    <a href="register.php" class="text-sm bg-[#800000] text-white px-4 py-2 rounded-lg hover:bg-[#600000] transition-colors">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Mobile Menu Overlay -->
<div class="mobile-overlay fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>

<!-- Mobile Menu -->
<div class="mobile-menu fixed top-0 right-0 h-full w-80 max-w-[85vw] bg-white shadow-2xl z-50 transform translate-x-full transition-transform duration-300 overflow-y-auto">
    <?php if ($isLoggedIn): ?>
        <!-- Mobile Menu Header -->
        <div class="p-6 text-black">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <img src="../image/ememhs-logo.png" alt="EMEMHS Logo" class="h-10 w-auto mr-3">
                    <div>
                        <h3 class="font-bold text-lg">EMEMHS</h3>
                        <p class="text-sm opacity-90">Guidance System</p>
                    </div>
                </div>
                <button class="mobile-close p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-30">
                    <svg class="w-6 h-6 transition-transform duration-200 hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Content -->
        <div class="p-4">
            <!-- Main Navigation -->
            <div class="space-y-2 mb-6">
                <a href="student_dashboard.php" class="mobile-nav-item <?php echo ($current_page == 'student_dashboard.php') ? 'active' : ''; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Home</span>
                </a>

                <a href="complaint-concern.php" class="mobile-nav-item <?php echo ($current_page == 'complaint-concern.php') ? 'active' : ''; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                    <span>Complaints & Concerns</span>
                </a>

                <a href="reschedule-request.php" class="mobile-nav-item <?php echo ($current_page == 'reschedule-request.php') ? 'active' : ''; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Reschedule Request</span>
                </a>

                <a href="lost_item.php" class="mobile-nav-item <?php echo ($current_page == 'lost_item.php') ? 'active' : ''; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Lost Items</span>
                </a>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-200 my-6"></div>

            <!-- Secondary Navigation -->
            <div class="space-y-2 mb-6">
                <a href="profile.php" class="mobile-nav-item <?php echo ($current_page == 'profile.php') ? 'active' : ''; ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>Profile</span>
                </a>
            </div>

            <!-- Logout Button -->
            <div class="pt-4 border-t border-gray-200">
                <a href="../logic/logout_logic.php" class="flex items-center justify-center w-full py-3 px-4 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Desktop Navigation */
    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        color: #6b7280;
        text-decoration: none;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .nav-link:hover {
        color: #800000;
        background-color: #f9fafb;
        transform: translateY(-1px);
    }

    .nav-link.active {
        color: #800000;
        background-color: #fef2f2;
        font-weight: 600;
    }

    /* Mobile Navigation Items */
    .mobile-nav-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        color: #374151;
        text-decoration: none;
        border-radius: 0.75rem;
        transition: all 0.2s ease;
        font-weight: 500;
        margin-bottom: 0.25rem;
    }

    .mobile-nav-item svg {
        margin-right: 0.75rem;
        flex-shrink: 0;
    }

    .mobile-nav-item:hover {
        background-color: #f3f4f6;
        color: #800000;
        transform: translateX(4px);
    }

    .mobile-nav-item.active {
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
    }

    .mobile-nav-item.active svg {
        color: white;
    }

    /* Mobile Menu States */
    .mobile-menu.active {
        transform: translateX(0);
    }

    .mobile-overlay.active {
        display: block !important;
        opacity: 1;
    }

    /* Mobile Menu Button Styling */
    .mobile-menu-btn {
        position: relative;
        z-index: 51;
    }

    .mobile-menu-btn:hover {
        transform: translateY(-1px);
    }

    .mobile-menu-btn.active {
        color: #800000;
        background-color: #fef2f2;
    }

    /* Hamburger Animation */
    .mobile-menu-btn .hamburger-icon path {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        transform-origin: center;
    }

    .mobile-menu-btn.active .line-1 {
        transform: rotate(45deg) translate(5px, 5px);
    }

    .mobile-menu-btn.active .line-2 {
        opacity: 0;
        transform: scale(0);
    }

    .mobile-menu-btn.active .line-3 {
        transform: rotate(-45deg) translate(7px, -6px);
    }

    /* Responsive Breakpoints */
    @media (max-width: 320px) {
        .mobile-menu {
            width: 100vw;
        }
    }

    @media (min-width: 480px) {
        .xs\:block {
            display: block !important;
        }
    }

    /* Touch-friendly sizing for mobile */
    @media (max-width: 1023px) {
        .mobile-nav-item {
            min-height: 3rem;
            font-size: 1rem;
        }
    }

    /* Smooth scrolling for mobile menu */
    .mobile-menu {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .mobile-menu::-webkit-scrollbar {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        const mobileClose = document.querySelector('.mobile-close');
        const mobileMenu = document.querySelector('.mobile-menu');
        const mobileOverlay = document.querySelector('.mobile-overlay');
        const body = document.body;

        function openMenu() {
            mobileMenu.classList.add('active');
            mobileOverlay.classList.add('active');
            mobileMenuBtn.classList.add('active');
            body.style.overflow = 'hidden';
        }

        function closeMenu() {
            mobileMenu.classList.remove('active');
            mobileOverlay.classList.remove('active');
            mobileMenuBtn.classList.remove('active');
            body.style.overflow = '';
        }

        // Event listeners
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', openMenu);
        }

        if (mobileClose) {
            mobileClose.addEventListener('click', closeMenu);
        }

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', closeMenu);
        }

        // Close menu when clicking on navigation links
        const mobileNavItems = document.querySelectorAll('.mobile-nav-item');
        mobileNavItems.forEach(item => {
            item.addEventListener('click', closeMenu);
        });

        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
                closeMenu();
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeMenu();
            }
        });
    });
</script>