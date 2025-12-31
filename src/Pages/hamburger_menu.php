<?php
?>

<style>
.hamburger-btn {
    display: none;
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 1001;
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    color: white;
    border: none;
    width: 50px;
    height: 50px;
    border-radius: 12px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4);
    transition: all 0.3s ease;
}

.hamburger-btn:hover {
    background: linear-gradient(135deg, #334155 0%, #1e293b 100%);
    transform: scale(1.08) rotate(5deg);
    box-shadow: 0 6px 20px rgba(0,0,0,0.5);
}

.hamburger-btn:active {
    transform: scale(0.95);
}

.hamburger-btn i {
    font-size: 1.5rem;
    transition: transform 0.3s ease;
}

.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    z-index: 999;
    backdrop-filter: blur(3px);
    animation: fadeIn 0.3s ease;
}

.sidebar-overlay.active {
    display: block;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.sidebar {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

@media (max-width: 768px) {
    .hamburger-btn {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .sidebar {
        transform: translateX(-100%) !important;
    }
    
    .sidebar.active {
        transform: translateX(0) !important;
        box-shadow: 8px 0 30px rgba(0,0,0,0.5);
    }
    
    .main-content {
        margin-left: 0 !important;
        width: 100% !important;
        padding: 1rem !important;
        padding-top: 5rem !important;
    }
    
    .page-header {
        margin-top: 1rem;
    }
}

@media (max-width: 820px) and (orientation: portrait) {
    .hamburger-btn {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .sidebar {
        transform: translateX(-100%) !important;
    }
    
    .sidebar.active {
        transform: translateX(0) !important;
    }
    
    .main-content {
        margin-left: 0 !important;
        width: 100% !important;
    }
}
</style>

<button class="hamburger-btn" id="hamburgerBtn" aria-label="Abrir menú">
    <i class="bi bi-list"></i>
</button>

<div class="sidebar-overlay" id="sidebarOverlay"></div>