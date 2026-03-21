<?php
require_once '../logic/notification_logic.php';
require_once '../logic/db_connection.php';
// Check if user is logged in
$isLoggedIn = isset($_SESSION['isLoggedIn']);

// Get current page filename
$current_page = basename($_SERVER['PHP_SELF']);

// Function to get pending complaints count
function getPendingComplaintsCount()
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM complaints_concerns WHERE status = 'pending'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    } catch (PDOException $e) {
        error_log("Error getting pending complaints count: " . $e->getMessage());
        return 0;
    }
}

// Function to get scheduled complaints count
function getScheduledComplaintsCount()
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM complaints_concerns WHERE status = 'scheduled'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    } catch (PDOException $e) {
        error_log("Error getting scheduled complaints count: " . $e->getMessage());
        return 0;
    }
}

// Function to get unresolved issues count
function getUnresolvedIssuesCount()
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM complaints_concerns WHERE status = 'unresolved'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    } catch (PDOException $e) {
        error_log("Error getting unresolved issues count: " . $e->getMessage());
        return 0;
    }
}

// Function to get pending reschedule requests count
function getPendingRescheduleRequestsCount()
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM reschedule_requests WHERE status = 'pending'");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    } catch (PDOException $e) {
        error_log("Error getting pending reschedule requests count: " . $e->getMessage());
        return 0;
    }
}

// Function to get unread suggestions count
function getUnreadSuggestionsCount()
{
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM anonymous_suggestions WHERE is_read = 0");
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    } catch (PDOException $e) {
        error_log("Error getting unread suggestions count: " . $e->getMessage());
        return 0;
    }
}
?>

<!-- Sidebar -->
<div class="fixed inset-y-0 left-0 bg-white shadow-lg transform transition-all duration-300 ease-in-out z-50" id="sidebar">
    <!-- Logo Section -->
    <div class="flex items-center justify-between h-12 px-4 border-b border-gray-200 sidebar-header">
        <div class="flex items-center space-x-2 sidebar-logo">
            <img src="../image/ememhs-logo.png" alt="EMEMHS Logo" class="sidebar-logo-img h-8 w-auto max-w-[32px] mx-auto">
            <span class="text-xs font-bold text-[#800000] whitespace-nowrap sidebar-logo-text">EMEMHS Guidance</span>
        </div>
        <div class="flex items-center space-x-2 sidebar-toggle">
            <button class="hidden lg:flex text-gray-500 hover:text-gray-700 focus:outline-none" id="toggleSidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <button class="lg:hidden text-gray-500 hover:text-gray-700 focus:outline-none" id="closeSidebar">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="flex flex-col h-[calc(100vh-3rem)] overflow-hidden">
        <div class="flex-1 px-3 py-2 overflow-y-auto space-y-0.5" style="scrollbar-width: thin; scrollbar-color: #800000 #f1f1f1;">
        <?php if ($isLoggedIn): ?>
            <a href="staff-dashboard.php" class="nav-link flex items-center space-x-2 px-3 py-1.5 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-xs <?php echo ($current_page == 'staff-dashboard.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Dashboard">
                <span class="icon-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
                <span class="truncate text-sm">Dashboard</span>
                <span class="sidebar-tooltip">Dashboard</span>
            </a>

            <div class="border-t border-gray-200 my-2"></div>

            <!-- Complaints Management -->
            <a href="complaint-concern-admin.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'complaint-concern-admin.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Pending Complaints">
                <span class="icon-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                    <?php
                    if ($isLoggedIn) {
                        $pending_count = getPendingComplaintsCount();
                        if ($pending_count > 0):
                    ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?php echo $pending_count; ?></span>
                    <?php
                        endif;
                    }
                    ?>
                </span>
                <span class="truncate text-sm">Pending Complaints</span>
                <span class="sidebar-tooltip">Pending Complaints</span>
            </a>

            <a href="scheduled-complaints.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'scheduled-complaints.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Scheduled Complaints">
                <span class="icon-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <?php
                    if ($isLoggedIn) {
                        $scheduled_count = getScheduledComplaintsCount();
                        if ($scheduled_count > 0):
                    ?>
                            <span class="absolute -top-1 -right-1 bg-green-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?php echo $scheduled_count; ?></span>
                    <?php
                        endif;
                    }
                    ?>
                </span>
                <span class="truncate text-sm">Scheduled Complaints</span>
                <span class="sidebar-tooltip">Scheduled Complaints</span>
            </a>

            <a href="reschedule-requests-admin.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'reschedule-requests-admin.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Reschedule Requests">
                <span class="icon-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <?php
                    if ($isLoggedIn) {
                        $pending_reschedule_count = getPendingRescheduleRequestsCount();
                        if ($pending_reschedule_count > 0):
                    ?>
                            <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?php echo $pending_reschedule_count; ?></span>
                    <?php
                        endif;
                    }
                    ?>
                </span>
                <span class="truncate text-sm">Reschedule Requests</span>
                <span class="sidebar-tooltip">Reschedule Requests</span>
            </a>

            <a href="unresolved-issues.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'unresolved-issues.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Unresolved Issues">
                <span class="icon-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?php
                    if ($isLoggedIn) {
                        $unresolved_count = getUnresolvedIssuesCount();
                        if ($unresolved_count > 0):
                    ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?php echo $unresolved_count; ?></span>
                    <?php
                        endif;
                    }
                    ?>
                </span>
                <span class="truncate text-sm">Unresolved Issues</span>
                <span class="sidebar-tooltip">Unresolved Issues</span>
            </a>

            <a href="all-complaints.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'all-complaints.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="All Complaints">
                <span class="icon-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                </span>
                <span class="truncate text-sm">All Complaints</span>
                <span class="sidebar-tooltip">All Complaints</span>
            </a>

            <a href="reports.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'reports.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Reports">
                <span class="icon-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </span>
                <span class="truncate text-sm">Reports</span>
                <span class="sidebar-tooltip">Reports</span>
            </a>

            <a href="record-walkin-complaint.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'record-walkin-complaint.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Record Walk-in">
                <span class="icon-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </span>
                <span class="truncate text-sm">Record Walk-in</span>
                <span class="sidebar-tooltip">Record Walk-in</span>
            </a>

            <div class="border-t border-gray-200 my-2"></div>

            <!-- Student Management -->
            <a href="students-list.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'students-list.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Students">
                <span class="icon-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                    </svg>
                </span>
                <span class="truncate text-sm">Students</span>
                <span class="sidebar-tooltip">Students</span>
            </a>

            <a href="student-verification.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'student-verification.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Student Verification">
                <span class="icon-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                    <?php
                    if ($isLoggedIn) {
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE is_verified = 0");
                        $stmt->execute();
                        $unverified_count = $stmt->fetchColumn();
                        if ($unverified_count > 0):
                    ?>
                            <span class="absolute -top-1 -right-1 bg-yellow-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?php echo $unverified_count; ?></span>
                    <?php
                        endif;
                    }
                    ?>
                </span>
                <span class="truncate text-sm">Student Verification</span>
                <span class="sidebar-tooltip">Student Verification</span>
            </a>

            <div class="border-t border-gray-200 my-2"></div>

            <!-- Other Services -->
            <a href="admin-lost-items.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'admin-lost-items.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Lost Items">
                <span class="icon-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </span>
                <span class="truncate text-sm">Lost Items</span>
                <span class="sidebar-tooltip">Lost Items</span>
            </a>

            <a href="admin-suggestions.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'admin-suggestions.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Suggestions">
                <span class="icon-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <?php
                    if ($isLoggedIn) {
                        $unread_suggestions_count = getUnreadSuggestionsCount();
                        if ($unread_suggestions_count > 0):
                    ?>
                            <span class="absolute -top-1 -right-1 bg-blue-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?php echo $unread_suggestions_count; ?></span>
                    <?php
                        endif;
                    }
                    ?>
                </span>
                <span class="truncate text-sm">Suggestions</span>
                <span class="sidebar-tooltip">Suggestions</span>
            </a>

            <div class="border-t border-gray-200 my-2"></div>

            <a href="notifications.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'notifications.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : 'text-gray-700 hover:text-[#800000] hover:bg-gray-50'; ?>" tabindex="0" data-tooltip="Notifications">
                <span class="icon-center relative">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <?php
                    $unread_count = getAdminUnreadNotificationsCount($_SESSION['user']);
                    if ($unread_count > 0):
                    ?>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center"><?php echo $unread_count; ?></span>
                    <?php endif; ?>
                </span>
                <span class="truncate text-sm">Notifications</span>
                <span class="sidebar-tooltip">Notifications</span>
            </a>
        <?php else: ?>
            <a href="login.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-700 hover:text-[#800000] hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'login.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : ''; ?>" tabindex="0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                <span class="text-sm">Login</span>
            </a>
            <a href="register.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg text-gray-700 hover:text-[#800000] hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#800000] text-sm <?php echo ($current_page == 'register.php') ? 'bg-[#800000]/10 text-[#800000] font-medium' : ''; ?>" tabindex="0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span class="text-sm">Register</span>
            </a>
        <?php endif; ?>
        </div>

        <!-- Logout at bottom -->
        <?php if ($isLoggedIn): ?>
        <div class="px-4 py-3 border-t border-gray-200 bg-white">
            <a href="../logic/logout_logic.php" class="nav-link flex items-center space-x-3 px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm <?php echo ($current_page == 'logout_logic.php') ? 'bg-red-100 font-medium' : ''; ?>" tabindex="0" data-tooltip="Logout">
                <span class="icon-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </span>
                <span class="truncate text-sm">Logout</span>
                <span class="sidebar-tooltip">Logout</span>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Mobile Menu Button -->
<button class="lg:hidden fixed top-4 left-4 z-50 p-2 rounded-lg bg-[#800000] text-white shadow-lg focus:outline-none" id="openSidebar">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<!-- Overlay -->
<div class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300" id="overlay"></div>

<style>
    /* Sidebar styles */
    #sidebar {
        width: 260px;
        transition: all 0.3s ease-in-out;
    }

    #sidebar.collapsed {
        width: 70px;
    }

    #sidebar.collapsed .truncate {
        display: none;
    }

    #sidebar.collapsed .space-x-3 {
        margin-left: 0;
        margin-right: 0;
    }

    #sidebar.collapsed .px-4 {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    #sidebar.collapsed .justify-between {
        justify-content: center;
    }

    #sidebar.collapsed #toggleSidebar svg {
        transform: rotate(180deg);
    }

    #sidebar.collapsed .whitespace-nowrap {
        display: none;
    }

    /* Mobile sidebar styles */
    @media (max-width: 1024px) {
        #sidebar {
            transform: translateX(-100%);
        }

        #sidebar.active {
            transform: translateX(0);
        }

        .main-content {
            margin-left: 0;
            width: 100%;
        }
    }

    @media (min-width: 1025px) {
        .main-content {
            margin-left: 280px;
            width: calc(100% - 280px);
            transition: all 0.3s ease-in-out;
        }

        .main-content.expanded {
            margin-left: 70px;
            width: calc(100% - 70px);
        }

        #openSidebar {
            display: none;
        }
    }

    @media (max-width: 640px) {
        #sidebar {
            width: 100%;
        }
    }

    /* Scrollbar styles */
    .overflow-y-auto {
        scrollbar-width: thin;
        scrollbar-color: #800000 #F7FAFC;
    }

    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: #F7FAFC;
        border-radius: 3px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background-color: #800000;
        border-radius: 3px;
        transition: background-color 0.2s;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb:hover {
        background-color: #a52a2a;
    }

    #sidebar.collapsed .sidebar-header {
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        height: 80px;
    }

    #sidebar.collapsed .sidebar-logo {
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    #sidebar.collapsed .sidebar-logo-img {
        margin: 0 auto;
        display: block;
        height: 40px;
        width: 40px;
        max-width: 40px;
    }

    #sidebar.collapsed .sidebar-logo-text {
        display: none;
    }

    #sidebar.collapsed .sidebar-toggle {
        justify-content: center;
        width: 100%;
        margin-top: 0.5rem;
    }

    /* Center icons in collapsed mode */
    #sidebar.collapsed .icon-center {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
    }

    #sidebar .icon-center {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        width: 24px;
    }

    /* Hide text in collapsed mode */
    #sidebar.collapsed .truncate {
        display: none !important;
    }

    /* Tooltip styles */
    .sidebar-tooltip {
        display: block;
        visibility: hidden;
        position: fixed;
        left: 70px;
        top: 50%;
        transform: translateY(-50%);
        background: #222;
        color: #fff;
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 0.95rem;
        white-space: nowrap;
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.18s;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }

    #sidebar.collapsed .nav-link {
        position: relative;
        justify-content: center;
    }

    #sidebar.collapsed .nav-link:hover .sidebar-tooltip {
        visibility: visible;
        opacity: 1;
        pointer-events: auto;
        top: 50%;
    }

    #sidebar.collapsed .sidebar-tooltip {
        left: 70px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const openSidebarBtn = document.getElementById('openSidebar');
        const closeSidebarBtn = document.getElementById('closeSidebar');
        const toggleSidebarBtn = document.getElementById('toggleSidebar');
        const mainContent = document.querySelector('.main-content');

        function openSidebar() {
            sidebar.classList.add('active');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        function toggleSidebar() {
            sidebar.classList.toggle('collapsed');
            if (mainContent && window.innerWidth >= 1025) {
                mainContent.classList.toggle('expanded');
            }
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        }

        // Event listeners
        if (openSidebarBtn) {
            openSidebarBtn.addEventListener('click', openSidebar);
        }
        if (closeSidebarBtn) {
            closeSidebarBtn.addEventListener('click', closeSidebar);
        }
        if (toggleSidebarBtn) {
            toggleSidebarBtn.addEventListener('click', toggleSidebar);
        }
        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        // Close sidebar when pressing Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // Close sidebar on link click (mobile)
        document.querySelectorAll('#sidebar .nav-link').forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024) {
                    closeSidebar();
                }
            });
        });

        // Handle window resize
        function handleResize() {
            if (window.innerWidth >= 1025) {
                sidebar.classList.remove('active');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
                // Restore expanded/collapsed state
                if (mainContent) {
                    if (sidebar.classList.contains('collapsed')) {
                        mainContent.classList.add('expanded');
                    } else {
                        mainContent.classList.remove('expanded');
                    }
                }
            } else {
                // On mobile, always remove expanded
                if (mainContent) mainContent.classList.remove('expanded');
            }
        }

        // Restore sidebar state from localStorage
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            sidebar.classList.add('collapsed');
            if (mainContent) {
                mainContent.classList.add('expanded');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize(); // Initial check
    });
</script>
