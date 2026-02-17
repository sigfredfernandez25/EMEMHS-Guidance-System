<!-- Floating Action Button Component -->
<style>
    /* FAB Styles */
    .fab-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }
    
    .fab-main {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #800000 0%, #600000 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(128, 0, 0, 0.3);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        color: white;
    }
    
    .fab-main:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 16px rgba(128, 0, 0, 0.4);
    }
    
    .fab-main.active {
        transform: rotate(45deg);
    }
    
    .fab-menu {
        position: absolute;
        bottom: 70px;
        right: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
        opacity: 0;
        transform: translateY(20px);
        pointer-events: none;
        transition: all 0.3s ease;
    }
    
    .fab-menu.active {
        opacity: 1;
        transform: translateY(0);
        pointer-events: all;
    }
    
    .fab-action {
        background: white;
        padding: 12px 20px;
        border-radius: 28px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        text-decoration: none;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .fab-action:hover {
        transform: translateX(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }
    
    .fab-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(2px);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
        z-index: 999;
    }
    
    .fab-backdrop.active {
        opacity: 1;
        pointer-events: all;
    }
    
    /* Hide on desktop if needed */
    @media (min-width: 1024px) {
        .fab-container {
            bottom: 30px;
            right: 30px;
        }
    }
</style>

<!-- FAB Backdrop -->
<div class="fab-backdrop" id="fabBackdrop"></div>

<!-- FAB Container -->
<div class="fab-container">
    <button class="fab-main" id="fabMain" aria-label="Quick Actions">
        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
    </button>
    
    <div class="fab-menu" id="fabMenu">
        <a href="complaint-concern-form.php" class="fab-action">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Report Complaint
        </a>
        
        <a href="lost-item-form.php" class="fab-action">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Report Lost Item
        </a>
        
        <a href="notifications.php" class="fab-action">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            Notifications
        </a>
    </div>
</div>

<script>
    // FAB Controller
    (function() {
        const fabMain = document.getElementById('fabMain');
        const fabMenu = document.getElementById('fabMenu');
        const fabBackdrop = document.getElementById('fabBackdrop');
        
        if (fabMain && fabMenu && fabBackdrop) {
            fabMain.addEventListener('click', function() {
                fabMain.classList.toggle('active');
                fabMenu.classList.toggle('active');
                fabBackdrop.classList.toggle('active');
            });
            
            fabBackdrop.addEventListener('click', function() {
                fabMain.classList.remove('active');
                fabMenu.classList.remove('active');
                fabBackdrop.classList.remove('active');
            });
        }
    })();
</script>
