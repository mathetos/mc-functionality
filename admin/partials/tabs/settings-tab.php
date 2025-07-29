<?php
/**
 * Settings Tab Component
 *
 * @package    Mc_Functionality
 * @subpackage Mc_Functionality/admin/partials/tabs
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<!-- Settings Tab Content -->
<div class="mc-functionality-settings">
	<h3>Plugin Settings</h3>
	
	<form method="post" action="options.php">
		<?php settings_fields( 'mc_functionality_settings' ); ?>
		<?php do_settings_sections( 'mc_functionality_settings' ); ?>
		
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="mc_codemirror_theme">CodeMirror Theme</label>
				</th>
				<td>
					<select name="mc_codemirror_theme" id="mc_codemirror_theme">
						<option value="default" <?php selected( get_option( 'mc_codemirror_theme', 'default' ), 'default' ); ?>>Default</option>
						<option value="darcula" <?php selected( get_option( 'mc_codemirror_theme', 'default' ), 'darcula' ); ?>>Darcula</option>
						<option value="eclipse" <?php selected( get_option( 'mc_codemirror_theme', 'default' ), 'eclipse' ); ?>>Eclipse</option>
						<option value="material" <?php selected( get_option( 'mc_codemirror_theme', 'default' ), 'material' ); ?>>Material</option>
						<option value="mdn-like" <?php selected( get_option( 'mc_codemirror_theme', 'default' ), 'mdn-like' ); ?>>MDN-like</option>
						<option value="monokai" <?php selected( get_option( 'mc_codemirror_theme', 'default' ), 'monokai' ); ?>>Monokai</option>
					</select>
					<p class="description">Choose the theme for the code editor. Changes will apply to new editing sessions.</p>
				</td>
			</tr>
		</table>
		
		<?php submit_button( 'Save Settings' ); ?>
	</form>
	
	<!-- Environment Information -->
	<div class="mc-environment-info">
		<h3>PHP Environment</h3>
		<p>Current PHP configuration for snippet execution:</p>
		
		<table class="form-table">
			<tr>
				<th scope="row">Memory Limit</th>
				<td>
					<code><?php echo esc_html( ini_get( 'memory_limit' ) ); ?></code>
					<?php 
					$memory_limit = ini_get( 'memory_limit' );
					$memory_bytes = 0;
					
					// Parse memory limit to bytes for comparison
					$unit = strtolower( substr( $memory_limit, -1 ) );
					$value = intval( $memory_limit );
					
					switch ( $unit ) {
						case 'g':
							$memory_bytes = $value * 1024 * 1024 * 1024;
							break;
						case 'm':
							$memory_bytes = $value * 1024 * 1024;
							break;
						case 'k':
							$memory_bytes = $value * 1024;
							break;
						default:
							$memory_bytes = $value;
					}
					
					if ( $memory_bytes < 64 * 1024 * 1024 ) { // Less than 64MB
						echo '<br><span class="dashicons dashicons-warning" style="color: #dba617;"></span> <em>Consider increasing for better snippet performance</em>';
					} else {
						echo '<br><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <em>Good for snippet execution</em>';
					}
					?>
				</td>
			</tr>
			<tr>
				<th scope="row">Max Execution Time</th>
				<td>
					<code><?php echo esc_html( ini_get( 'max_execution_time' ) ); ?> seconds</code>
					<?php 
					$max_execution_time = ini_get( 'max_execution_time' );
					if ( $max_execution_time > 0 && $max_execution_time < 30 ) {
						echo '<br><span class="dashicons dashicons-warning" style="color: #dba617;"></span> <em>Consider increasing for complex snippets</em>';
					} else {
						echo '<br><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <em>Good for snippet execution</em>';
					}
					?>
				</td>
			</tr>
			<tr>
				<th scope="row">PHP Version</th>
				<td>
					<code><?php echo esc_html( PHP_VERSION ); ?></code>
					<?php 
					if ( version_compare( PHP_VERSION, '7.4', '>=' ) ) {
						echo '<br><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> <em>Good for modern PHP features</em>';
					} else {
						echo '<br><span class="dashicons dashicons-warning" style="color: #dba617;"></span> <em>Consider upgrading for better performance</em>';
					}
					?>
				</td>
			</tr>
		</table>
		
		<p class="description">
			<strong>Note:</strong> These settings affect how much memory and time your snippets can use. 
			The validation system will warn you if your code might exceed these limits.
		</p>
	</div>
</div>