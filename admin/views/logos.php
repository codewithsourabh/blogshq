<?php
/**
 * FILE: admin/views/logos.php
 * Template for category logos settings.
 */
?>

<div class="blogshq-settings-section">
<?php
if ( class_exists( 'BlogsHQ_Logos' ) ) {
    $logos = new BlogsHQ_Logos();
    $logos->render_admin_page();
} else {
    echo '<div class="notice notice-error"><p>BlogsHQ_Logos class not found. Please check your plugin installation.</p></div>';
}
?>
</div>
