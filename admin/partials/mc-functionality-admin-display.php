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
$loaded_snippets = $snippet_loader->get_loaded_snippets();
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<div class="mc-functionality-admin-content">
		<div class="mc-functionality-overview">
			<h2>Site Functions Overview</h2>
			<p>This plugin provides a file-based code snippet system for WordPress. PHP files placed in the <code>/code-snippets/</code> directory are automatically loaded and executed.</p>
		</div>

		<div class="mc-functionality-snippets-status">
			<h3>Loaded Snippets</h3>
			<?php if ( empty( $loaded_snippets ) ) : ?>
				<div class="notice notice-info">
					<p><strong>No snippets found.</strong> Create PHP files in the <code><?php echo esc_html( MC_FUNCTIONALITY_SNIPPETS_DIR ); ?></code> directory to get started.</p>
				</div>
			<?php else : ?>
				<div class="mc-snippets-list">
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th>File Name</th>
								<th>Path</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $loaded_snippets as $snippet_file ) : ?>
								<tr>
									<td><strong><?php echo esc_html( basename( $snippet_file ) ); ?></strong></td>
									<td><code><?php echo esc_html( $snippet_file ); ?></code></td>
									<td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> Loaded</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
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
	</div>
</div>

<style>
.mc-functionality-admin-content {
	max-width: 1200px;
}

.mc-functionality-overview,
.mc-functionality-snippets-status,
.mc-functionality-usage,
.mc-functionality-info,
.mc-functionality-security {
	margin-bottom: 30px;
	padding: 20px;
	background: #fff;
	border: 1px solid #ccd0d4;
	border-radius: 4px;
}

.mc-functionality-overview h2,
.mc-functionality-snippets-status h3,
.mc-functionality-usage h3,
.mc-functionality-info h3,
.mc-functionality-security h3 {
	margin-top: 0;
	color: #23282d;
}

.mc-usage-steps ol {
	margin-left: 20px;
}

.mc-usage-steps li {
	margin-bottom: 10px;
}

.mc-functionality-usage pre {
	background: #f1f1f1;
	padding: 15px;
	border-radius: 4px;
	overflow-x: auto;
}

.mc-functionality-usage code {
	font-family: 'Courier New', monospace;
	font-size: 13px;
}

.mc-functionality-security ul {
	list-style: none;
	padding: 0;
}

.mc-functionality-security li {
	margin-bottom: 8px;
	padding-left: 0;
}

.mc-snippets-list table {
	margin-top: 10px;
}

.mc-snippets-list th {
	font-weight: 600;
}
</style>
