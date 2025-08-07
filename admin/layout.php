<?php
$company_name = get_option('ib_company_name', 'Institut Booking');
?>
<div id="ib-app" class="ib-app">
    <!-- Header principal avec bouton hamburger et notifications -->
    <div class="ib-header">
        <div class="ib-header-left">
            <button class="ib-menu-toggle" onclick="toggleSidebar()">
                <span class="dashicons dashicons-menu"></span>
            </button>
            <h1 class="ib-page-title"><?php echo esc_html($company_name); ?></h1>
        </div>
        <div class="ib-header-right">
            <!-- Nouvelle cloche de notifications moderne -->
            <div id="ib-notif-bell" class="ib-notif-bell ib-notif-bell-refonte">
                <button class="ib-notif-bell-btn" aria-label="Notifications" onclick="toggleNotificationPanel()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="20" height="20">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                        <path d="m13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span id="ib-notif-badge" class="ib-notif-badge ib-notif-badge-refonte" style="display:none;">0</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Overlay pour mobile -->
    <div class="ib-sidebar-overlay" onclick="closeSidebar()"></div>
    
    <!-- Sidebar -->
    <?php require_once(IB_PLUGIN_DIR . 'admin/sidebar.php'); ?>
    
    <!-- Contenu principal -->
    <div class="ib-main-content">
        <div class="ib-main-inner">
            <main class="ib-content">
                <?php if (isset($GLOBALS['ib_page_content'])) echo $GLOBALS['ib_page_content']; ?>
            </main>
        </div>
    </div>
</div>

<style>
/* Layout moderne */
#ib-app, .ib-app,
.ib-main-content, .ib-main-inner, .ib-content {
    display: block !important;
    min-height: unset !important;
    height: auto !important;
    flex: unset !important;
    align-items: unset !important;
    justify-content: unset !important;
    background: var(--bg-light);
}

.ib-header, .ib-content {
    width: 100%;
}

.ib-header {
    background: var(--bg);
    border-bottom: 1px solid var(--border);
    padding: 1rem 0 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow);
    position: sticky;
    top: 0;
    z-index: 100;
}

.ib-header-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    min-width: 0;
}

.ib-menu-toggle {
    display: none;
    background: none;
    border: none;
    color: #e9aebc;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.8rem;
    border-radius: 8px;
    transition: all 0.2s ease;
    margin-right: 1rem;
}

.ib-menu-toggle:hover {
    background: #fbeff3;
    color: #b95c8a;
    transform: scale(1.05);
}

.ib-page-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.ib-header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-right: 0;
}

.ib-user-menu {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--bg-light);
    border-radius: var(--radius);
    color: var(--text);
    font-weight: 500;
    font-size: 0.9rem;
}

.ib-content {
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
    padding: 0 !important;
    flex: 1;
    overflow-y: auto;
}

.ib-notif-bell {
    margin-right: 4rem;
    margin-left: 0;
    align-items: center;
    display: flex;
}

/* Styles pour la nouvelle cloche moderne */
.ib-notif-bell-refonte .ib-notif-bell-btn {
    position: relative;
    background: none;
    border: none;
    color: #e9aebc;
    cursor: pointer;
    padding: 0.75rem;
    border-radius: 12px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ib-notif-bell-refonte .ib-notif-bell-btn:hover {
    background: #fbeff3;
    color: #b95c8a;
    transform: scale(1.05);
}

.ib-notif-badge-refonte {
    position: absolute;
    top: 4px;
    right: 4px;
    background: #ef4444;
    color: white;
    font-size: 0.75rem;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 18px;
    text-align: center;
    line-height: 1.2;
}

/* Responsive */
@media (max-width: 1024px) {
    .ib-main-content {
        margin-left: 0;
    }
    .ib-main-inner {
        max-width: 100%;
    }
    
    .ib-menu-toggle {
        display: block;
    }
    
    .ib-content {
        padding: 1rem;
    }
    
    .ib-header {
        padding: 1rem;
    }
    
    /* Sidebar mobile */
    .ib-sidebar {
        transform: translateX(-100%);
    }
    
    .ib-sidebar.open {
        transform: translateX(0);
    }
    
    /* Overlay pour fermer la sidebar */
    .ib-sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }
    
    .ib-sidebar-overlay.active {
        display: block;
    }
}

@media (max-width: 768px) {
    .ib-content {
        padding: 0.5rem;
    }
    
    .ib-page-title {
        font-size: 1.25rem;
    }
}

@media (max-width: 900px) {
    .ib-main-content, .ib-content {
        margin-left: 0 !important;
        padding-left: 0.5rem !important;
        padding-right: 1rem !important;
    }
}

/* Animation pour la sidebar */
@keyframes slideIn {
    from {
        transform: translateX(-100%);
    }
    to {
        transform: translateX(0);
    }
}

.ib-sidebar.open {
    animation: slideIn 0.3s ease;
}

.ib-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 240px;
    height: 100vh;
    background: #fff;
    border-right: 1px solid #f1f5f9;
    z-index: 1000;
    overflow-y: auto;
    transition: transform 0.3s ease;
}

.ib-sidebar, .custom-sidebar {
    width: 270px !important;
    min-width: 270px !important;
}

.ib-main-content {
    margin-left: 270px;
    padding: 0;
    background: transparent;
    min-height: 100vh;
    box-sizing: border-box;
    width: auto;
    display: block;
}

body {
    overflow-x: hidden !important;
}
</style>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.ib-sidebar');
    const overlay = document.querySelector('.ib-sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.toggle('open');
        if (overlay) {
            overlay.classList.toggle('active');
        }
    }
}

function closeSidebar() {
    const sidebar = document.querySelector('.ib-sidebar');
    const overlay = document.querySelector('.ib-sidebar-overlay');
    
    if (sidebar) {
        sidebar.classList.remove('open');
    }
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// Fermer la sidebar en cliquant à l'extérieur sur mobile
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 1024) {
        const sidebar = document.querySelector('.ib-sidebar');
        const menuToggle = document.querySelector('.ib-menu-toggle');
        
        if (sidebar && menuToggle && !sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
            closeSidebar();
        }
    }
});

// Fermer la sidebar quand on redimensionne l'écran
window.addEventListener('resize', function() {
    if (window.innerWidth > 1024) {
        const sidebar = document.querySelector('.ib-sidebar');
        const overlay = document.querySelector('.ib-sidebar-overlay');
        
        if (sidebar) {
            sidebar.classList.remove('open');
        }
        if (overlay) {
            overlay.classList.remove('active');
        }
    }
});

// Fonction pour ouvrir/fermer le panneau de notifications moderne
function toggleNotificationPanel() {
    if (typeof NotificationRefonte !== 'undefined') {
        NotificationRefonte.togglePanel();
    } else {
        console.warn('NotificationRefonte non chargé');
    }
}
</script>

<!-- Assets du nouveau système de notifications -->
<link rel="stylesheet" href="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/css/ib-notif-refonte.css'; ?>?v=3.0.0">
<script src="<?php echo plugin_dir_url(dirname(__FILE__)) . 'assets/js/ib-notif-refonte.js'; ?>?v=3.0.0"></script>

<script>
// Initialisation du nouveau système de notifications
document.addEventListener('DOMContentLoaded', function() {
    if (typeof NotificationRefonte !== 'undefined') {
        NotificationRefonte.init({
            ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('ib_notifications_nonce'); ?>',
            autoRefresh: <?php echo get_option('ib_notif_auto_refresh', true) ? 'true' : 'false'; ?>,
            refreshInterval: <?php echo get_option('ib_notif_refresh_interval', 30000); ?>
        });

        // Charger le compteur initial
        NotificationRefonte.updateBadge();
    }
});
</script>

<?php
// Inclure le nouveau panneau de notifications moderne
$notifications_all = [];
$unread_count = 0;

// Vérifier si la classe IB_Notifications existe
if (class_exists('IB_Notifications')) {
    $notifications_all = IB_Notifications::get_recent('admin', 50);
    $notifications_unread = IB_Notifications::get_unread('admin');
    $unread_count = count($notifications_unread);
}

// Compteurs par type pour les onglets
$bookings_count = 0;
$emails_count = 0;
$archived_count = 0;

foreach ($notifications_all as $notif) {
    if (in_array($notif->type, ['booking_new', 'booking_confirmed', 'booking_cancelled'])) {
        $bookings_count++;
    } elseif ($notif->type === 'email') {
        $emails_count++;
    }
}
?>

<!-- Panneau de notifications moderne -->
<div class="ib-notif-refonte" id="ib-notif-refonte">
    <!-- Panneau principal -->
    <div class="ib-notif-panel-refonte" id="ib-notif-panel-refonte">

        <!-- Header moderne avec onglets -->
        <div class="ib-notif-header-refonte">
            <div class="ib-notif-header-top">
                <h2 class="ib-notif-title-refonte">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="20" height="20">
                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
                        <path d="m13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    Notifications
                </h2>
                <button class="ib-notif-close-refonte" id="ib-notif-close" aria-label="Fermer">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>

            <!-- Système d'onglets moderne -->
            <div class="ib-notif-tabs-refonte">
                <button class="ib-notif-tab-refonte active" data-tab="all">
                    📆 Toutes
                    <span class="tab-count"><?php echo count($notifications_all); ?></span>
                </button>
                <button class="ib-notif-tab-refonte" data-tab="bookings">
                    📅 Réservations
                    <span class="tab-count"><?php echo $bookings_count; ?></span>
                </button>
                <button class="ib-notif-tab-refonte" data-tab="emails">
                    📩 Emails
                    <span class="tab-count"><?php echo $emails_count; ?></span>
                </button>
                <button class="ib-notif-tab-refonte" data-tab="archived">
                    📂 Archivées
                    <span class="tab-count"><?php echo $archived_count; ?></span>
                </button>
            </div>

            <!-- Barre de recherche moderne -->
            <div class="ib-notif-search-refonte">
                <div class="ib-notif-search-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="18" height="18">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>
                </div>
                <input type="text"
                       class="ib-notif-search-input-refonte"
                       placeholder="Rechercher un client, service..."
                       id="ib-notif-search-input"
                       autocomplete="off">
            </div>
        </div>

        <!-- Zone de contenu avec scroll personnalisé -->
        <div class="ib-notif-content-refonte" id="ib-notif-content">
            <!-- Le contenu sera chargé dynamiquement par JavaScript -->
            <div class="ib-notif-loading" id="ib-notif-loading">
                <div class="ib-notif-spinner"></div>
            </div>
        </div>
    </div>

    <!-- Overlay moderne -->
    <div class="ib-notif-overlay-refonte" id="ib-notif-overlay"></div>

    <!-- Barre d'actions flottante pour sélection multiple -->
    <div class="ib-notif-selection-bar" id="ib-notif-selection-bar">
        <div class="ib-notif-selection-count">
            <span id="ib-notif-selection-count">0</span> sélectionnée(s)
        </div>
        <div class="ib-notif-selection-actions">
            <button class="ib-notif-selection-btn" id="ib-notif-mark-read">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16">
                    <polyline points="20,6 9,17 4,12"/>
                </svg>
                Marquer comme lu
            </button>
            <button class="ib-notif-selection-btn" id="ib-notif-archive">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16">
                    <polyline points="21,8 21,21 3,21 3,8"/>
                    <rect x="1" y="3" width="22" height="5"/>
                    <line x1="10" y1="12" x2="14" y2="12"/>
                </svg>
                Archiver
            </button>
            <button class="ib-notif-selection-btn" id="ib-notif-delete">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="16" height="16">
                    <polyline points="3,6 5,6 21,6"/>
                    <path d="m19 6v14a2 2 0 0 1-2 2h-10a2 2 0 0 1-2-2v-14m3 0v-2a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                </svg>
                Supprimer
            </button>
        </div>
    </div>
</div>