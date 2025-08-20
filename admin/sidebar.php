<?php
if (!defined('ABSPATH')) exit;

$current_page = isset($_GET['page']) ? $_GET['page'] : '';
$company_name = get_option('ib_company_name', 'Institut Booking');
$logo_url = get_option('ib_logo_url', '');
$open_categories = ($current_page === 'institut-booking-categories') ? ' open' : '';
?>

<div class="ib-sidebar custom-sidebar">
    <div class="ib-sidebar-logo custom-sidebar-logo">
        <?php if (!empty($logo_url)): ?>
            <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($company_name); ?>">
        <?php else: ?>
            <h2 style="margin:0;color:#e9aebc;font-size:1.5rem;font-weight:700;letter-spacing:0.01em;"> <?php echo esc_html($company_name); ?> </h2>
        <?php endif; ?>
    </div>
    <nav class="ib-sidebar-nav custom-sidebar-nav">
        <a href="<?php echo admin_url('admin.php?page=institut-booking'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-dashboard"></span>
            <span>Tableau de bord</span>
        </a>
 
        <a href="<?php echo admin_url('admin.php?page=institut-booking-calendar'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-calendar' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-calendar"></span>
            <span>Calendrier</span>
        </a>




 <a href="<?php echo admin_url('admin.php?page=institut-booking-bookings'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-bookings' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-calendar-alt"></span>
            <span>Réservations</span>
        </a>
 <a href="<?php echo admin_url('admin.php?page=institut-booking-clients'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-clients' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-admin-users"></span>
            <span>Clientes</span>
        </a>



        <div class="ib-sidebar-group<?php echo $open_categories; ?>">
            <a href="<?php echo admin_url('admin.php?page=institut-booking-services'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-services' ? 'active' : ''; ?> ib-has-submenu">
                <span class="dashicons dashicons-admin-tools"></span>
                <span>Services</span>
                <span class="ib-submenu-arrow">&#9662;</span>
            </a>
            <div class="ib-sidebar-submenu">
                <a href="<?php echo admin_url('admin.php?page=institut-booking-categories'); ?>" class="ib-sidebar-link custom-sidebar-link ib-sidebar-sub <?php echo $current_page === 'institut-booking-categories' ? 'active' : ''; ?>">
                    <span class="dashicons dashicons-category"></span>
                    <span>Catégories</span>
                </a>
            </div>
        </div>
        <a href="<?php echo admin_url('admin.php?page=institut-booking-employees'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-employees' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-groups"></span>
            <span>Praticiennes</span>
        </a>
       
       
      
        <div class="custom-sidebar-section-title">CONFIGURATION</div>
        <a href="<?php echo admin_url('admin.php?page=institut-booking-extras'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-extras' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-plus-alt"></span>
            <span>Extras</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=institut-booking-coupons'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-coupons' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-tickets-alt"></span>
            <span>Coupons</span>
        </a>
        <div class="custom-sidebar-section-title">COMMUNICATION</div>
        <a href="<?php echo admin_url('admin.php?page=institut-booking-notifications'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-notifications' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-email"></span>
            <span>Notifications</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=institut-booking-sms'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-sms' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-phone"></span>
            <span>SMS</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=institut-booking-feedback'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-feedback' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-star-filled"></span>
            <span>Avis</span>
        </a>
        <div class="custom-sidebar-section-title">SYSTÈME</div>
        <a href="<?php echo admin_url('admin.php?page=institut-booking-settings'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-settings' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-admin-settings"></span>
            <span>Réglages</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=institut-booking-logs'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-logs' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-list-view"></span>
            <span>Logs</span>
        </a>
        <a href="<?php echo admin_url('admin.php?page=institut-booking-analytics'); ?>" class="ib-sidebar-link custom-sidebar-link <?php echo $current_page === 'institut-booking-analytics' ? 'active' : ''; ?>">
            <span class="dashicons dashicons-chart-bar"></span>
            <span>Analytics</span>
    </a>
  </nav>
    <div style="margin-top:auto;padding:1.5rem 1rem 1rem 1rem;">
        <div style="font-size:0.75rem;color:#bdbdbd;margin-bottom:0.5rem;">Version</div>
        <div style="font-weight:600;color:#22223b;">2.0.0</div>
    </div>
</div>

<style>
.custom-sidebar {
    background: #fff !important;
    color: #22223b !important;
    border-right: 1px solid #f1f5f9;
    box-shadow: none !important;
    min-height: 100vh;
    padding: 0;
}
.custom-sidebar-logo {
    padding: 2.2rem 1.5rem 1.5rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    background: #fff;
    text-align: left;
}
.custom-sidebar-logo img {
    max-height: 40px;
    width: auto;
    display: block;
}
.custom-sidebar-nav {
    padding: 1.2rem 0 0.5rem 0;
}
.custom-sidebar-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.85rem 1.7rem;
    color: #22223b !important;
    text-decoration: none;
    font-weight: 500;
    font-size: 1.08rem;
    border-radius: 8px;
    margin-bottom: 0.15rem;
    transition: background 0.18s, color 0.18s;
    position: relative;
    background: none;
}
.custom-sidebar-link:hover {
    background: #fbeff2 !important;
    color: #e9aebc !important;
}
.custom-sidebar-link.active {
    background: #fbeff2 !important;
    color: #e9aebc !important;
    font-weight: 700;
}
.custom-sidebar-link .dashicons {
    font-size: 1.25rem;
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
    color: #bdbdbd;
}
.custom-sidebar-section-title {
    font-size: 0.78rem;
    font-weight: 700;
    color: #bdbdbd;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin: 1.2rem 0 0.2rem 1.7rem;
    padding-top: 0.7rem;
    padding-bottom: 0.2rem;
}
@media (max-width: 1024px) {
    .custom-sidebar {
        width: 100vw;
        min-width: 0;
        position: relative;
        border-right: none;
    }
}
.ib-sidebar-group {
    position: relative;
}
.ib-sidebar-group .ib-sidebar-submenu {
    display: none;
    position: static;
    margin-left: 0;
    background: none;
    box-shadow: none;
    padding: 0;
}
.ib-sidebar-group:hover .ib-sidebar-submenu,
.ib-sidebar-group:focus-within .ib-sidebar-submenu,
.ib-sidebar-group.open .ib-sidebar-submenu {
    display: block;
}
.ib-sidebar-group .ib-sidebar-link.ib-has-submenu {
    position: relative;
    cursor: pointer;
}
.ib-submenu-arrow {
    margin-left: auto;
    font-size: 0.9em;
    color: #bfa2c7;
    transition: transform 0.18s;
}
.ib-sidebar-group:hover .ib-submenu-arrow,
.ib-sidebar-group:focus-within .ib-submenu-arrow {
    transform: rotate(180deg);
}
.ib-sidebar-sub {
    background: none !important;
    color: #bfa2c7 !important;
    font-weight: 500;
    border-radius: 6px;
    padding-left: 3.2rem !important;
    font-size: 0.98em;
    opacity: 0.85;
}
.ib-sidebar-sub.active {
    background: #fbeff2 !important;
    color: #e9aebc !important;
    font-weight: 700;
}
</style>
