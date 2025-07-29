<?php
/**
 * Overview Tab Component
 *
 * @package    Mc_Functionality
 * @subpackage Mc_Functionality/admin/partials/tabs
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

<div class="mc-functionality-overview">
	<h3>Site Functions Overview</h3>
	<p>This plugin provides a file-based code snippet system for WordPress. Instead of storing snippets in the database, this plugin loads and executes PHP files from a specific directory within the plugin.</p>
	
	<div class="mc-overview-features">
		<h4>Key Features</h4>
		<ul>
			<li><strong>File-based Snippets:</strong> Write PHP code directly in files</li>
			<li><strong>Automatic Loading:</strong> Snippets are loaded early in the WordPress lifecycle</li>
			<li><strong>Enable/Disable:</strong> Toggle snippets on and off without deleting files</li>
			<li><strong>In-browser Editor:</strong> Edit snippets directly from the admin interface</li>
			<li><strong>Security:</strong> Built-in protection against path traversal and malicious files</li>
		</ul>
	</div>
</div>

<div class="mc-functionality-usage">
	<h3>How to Use</h3>
	<div class="mc-usage-steps">
		<ol>
			<li><strong>Create PHP files</strong> in the <code><?php echo esc_html( MC_FUNCTIONALITY_SNIPPETS_DIR ); ?></code> directory</li>
			<li><strong>Write WordPress code</strong> directly in the PHP files (shortcodes, hooks, filters, etc.)</li>
			<li><strong>Save the files</strong> - they will be automatically loaded on the next page load</li>
		</ol>
	</div>

	<h4>Example Snippet</h4>
	<pre><code>&lt;?php
// Add a custom shortcode
add_shortcode( 'my_shortcode', function( $atts ) {
    return '&lt;div&gt;My custom content&lt;/div&gt;';
} );

// Hook into WordPress actions
add_action( 'wp_footer', function() {
    echo '&lt;!-- Custom footer content --&gt;';
} );
?&gt;</code></pre>
</div>

<div class="mc-functionality-info">
	<h3>Plugin Information</h3>
	<table class="form-table">
		<tr>
			<th scope="row">Plugin Version</th>
			<td><?php echo esc_html( MC_FUNCTIONALITY_VERSION ); ?></td>
		</tr>
		<tr>
			<th scope="row">Snippets Directory</th>
			<td><code><?php echo esc_html( MC_FUNCTIONALITY_SNIPPETS_DIR ); ?></code></td>
		</tr>
		<tr>
			<th scope="row">Loading Priority</th>
			<td>Priority 5 on <code>plugins_loaded</code> hook</td>
		</tr>
	</table>
</div>

<div class="mc-functionality-security">
	<h3>Security Features</h3>
	<ul>
		<li>✅ Files are validated before loading</li>
		<li>✅ Only files within the snippets directory are allowed</li>
		<li>✅ Path traversal protection</li>
		<li>✅ Only .php files are loaded</li>
		<li>✅ Errors are logged when WP_DEBUG is enabled</li>
		<li>✅ Files are loaded with <code>require_once</code> to prevent duplicates</li>
	</ul>
</div>