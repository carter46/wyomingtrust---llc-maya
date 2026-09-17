<?php
/**
 * Admin impersonation banner for user dashboard.
 */
if (empty($_SESSION['admin_impersonating'])) {
    return;
}

$userName = htmlspecialchars((string) ($_SESSION['user_name'] ?? 'User'), ENT_QUOTES, 'UTF-8');
$adminLabel = htmlspecialchars((string) ($_SESSION['admin_original_email'] ?? $_SESSION['admin_email'] ?? 'Admin'), ENT_QUOTES, 'UTF-8');
$switchBackUrl = htmlspecialchars('../../api/admin/stop-impersonating.php', ENT_QUOTES, 'UTF-8');
?>
<div id="adminImpersonationBanner" class="admin-impersonation-banner" role="status" aria-live="polite">
    <div class="admin-impersonation-banner__inner">
        <div class="admin-impersonation-banner__text">
            <span>You are viewing as <strong><?php echo $userName; ?></strong> (Admin: <?php echo $adminLabel; ?>)</span>
        </div>
        <a class="admin-impersonation-banner__btn" href="<?php echo $switchBackUrl; ?>">Switch Back to Admin</a>
    </div>
</div>
<style>
:root { --admin-impersonation-banner-h: 48px; }
.admin-impersonation-banner {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 30000;
    width: 100%;
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    color: #fff;
    padding: 10px 16px;
    box-sizing: border-box;
}
.admin-impersonation-banner__inner {
    max-width: 1280px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.admin-impersonation-banner__text {
    font-size: 14px;
    line-height: 1.4;
}
.admin-impersonation-banner__btn {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    border-radius: 8px;
    background: #fff;
    color: #92400e;
    font-weight: 700;
    font-size: 13px;
    text-decoration: none;
}
.admin-impersonation-banner__btn:hover { background: #fffbeb; }
body.has-admin-impersonation-banner {
    padding-top: var(--admin-impersonation-banner-h) !important;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.body.classList.add('has-admin-impersonation-banner');
    var banner = document.getElementById('adminImpersonationBanner');
    if (banner) {
        document.documentElement.style.setProperty('--admin-impersonation-banner-h', banner.offsetHeight + 'px');
    }
});
</script>
