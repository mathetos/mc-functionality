<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.mattcromwell.com
 * @since      1.0.0
 *
 * @package    Mc_Functionality
 * @subpackage Mc_Functionality/admin/partials
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Get the snippet loader to show loaded snippets
$snippet_loader = new Mc_Functionality_Snippet_Loader();
$all_snippets = $snippet_loader->get_all_snippets();
?>

<div class="wrap">
	<h1>Site Functions</h1>
	
	<?php require_once plugin_dir_path( __FILE__ ) . 'components/tab-navigation.php'; ?>

	<div class="mc-functionality-admin-content">
		<?php
		// Get current tab
		$current_tab = mc_get_current_tab();
		
		// Load appropriate tab content
		switch ( $current_tab ) {
			case 'snippets':
				require_once plugin_dir_path( __FILE__ ) . 'tabs/snippets-tab.php';
				break;
			case 'overview':
				require_once plugin_dir_path( __FILE__ ) . 'tabs/overview-tab.php';
				break;
			case 'settings':
				require_once plugin_dir_path( __FILE__ ) . 'tabs/settings-tab.php';
				break;
			default:
				require_once plugin_dir_path( __FILE__ ) . 'tabs/snippets-tab.php';
				break;
		}
		?>
	</div>
</div>

<?php 
// Include modal only on snippets tab
if ( $current_tab === 'snippets' ) {
	require_once plugin_dir_path( __FILE__ ) . 'components/snippet-editor-modal.php';
}
?>
