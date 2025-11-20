<?php
/**
 * FILE: admin/views/toc.php
 * Template for TOC settings.
 */
?>

<div class="blogshq-settings-section">
	<?php
if ( class_exists( 'BlogsHQ_TOC' ) ) {
    $toc = new BlogsHQ_TOC();
    $toc->render_admin_page();
} else {
    echo '<div class="notice notice-error"><p>BlogsHQ_TOC class not found. Please check your plugin installation.</p></div>';
}
?>
</div>

