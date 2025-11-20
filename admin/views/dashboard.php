<?php
/**
 * FILE: admin/views/dashboard.php
 * Main dashboard template with tabs.
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// $active_tab is passed from display_plugin_dashboard()
?>
<div class="wrap blogshq-admin-wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<h2 class="nav-tab-wrapper">
		<a href="?page=blogshq&tab=logos" 
		   class="nav-tab <?php echo $active_tab === 'logos' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Category Logos', 'blogshq' ); ?>
		</a>
		<a href="?page=blogshq&tab=toc" 
		   class="nav-tab <?php echo $active_tab === 'toc' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'Table of Contents', 'blogshq' ); ?>
		</a>
		<a href="?page=blogshq&tab=faq" 
		   class="nav-tab <?php echo $active_tab === 'faq' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'FAQ Block', 'blogshq' ); ?>
		</a>
		<a href="?page=blogshq&tab=ai-share" 
		   class="nav-tab <?php echo $active_tab === 'ai-share' ? 'nav-tab-active' : ''; ?>">
			<?php esc_html_e( 'AI Share', 'blogshq' ); ?>
		</a>
	</h2>

	<div class="blogshq-tab-content">
		<?php
		// Load the appropriate tab content
		switch ( $active_tab ) {
			case 'logos':
				blogshq_get_template( 'logos' );
				break;
			case 'toc':
				blogshq_get_template( 'toc' );
				break;
			case 'faq':
				blogshq_get_template( 'faq' );
				break;
			case 'ai-share':
				blogshq_get_template( 'ai-share' );
				break;
		}
		?>
	</div>
</div>
