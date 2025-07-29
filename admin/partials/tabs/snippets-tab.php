<?php
/**
 * Snippets Tab Component
 *
 * @package    Mc_Functionality
 * @subpackage Mc_Functionality/admin/partials/tabs
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Include components
require_once plugin_dir_path( __FILE__ ) . '../components/snippets-table.php';
?>

<!-- Snippets Tab Content -->
<div class="mc-functionality-snippets-status">
	<h3>Loaded Snippets</h3>
	<?php mc_render_snippets_table( $all_snippets ); ?>
	
	<div class="mc-add-snippet-section">
		<button type="button" class="button button-primary" id="mc-add-snippet-btn">
			<span class="dashicons dashicons-plus-alt2"></span>
			Add Snippet
		</button>
	</div>
</div>