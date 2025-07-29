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
									<td>
										<a href="#" class="mc-edit-snippet" data-filename="<?php echo esc_attr( basename( $snippet_file ) ); ?>">
											<strong><?php echo esc_html( basename( $snippet_file ) ); ?></strong>
										</a>
									</td>
									<td><code><?php echo esc_html( $snippet_file ); ?></code></td>
									<td><span class="dashicons dashicons-yes-alt" style="color: #46b450;"></span> Loaded</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
			
			<div class="mc-add-snippet-section">
				<button type="button" class="button button-primary" id="mc-add-snippet-btn">
					<span class="dashicons dashicons-plus-alt2"></span>
					Add Snippet
				</button>
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
	</div>
</div>

<!-- Snippet Editor Modal -->
<div id="mc-snippet-editor-modal" class="mc-modal-overlay" style="display: none;">
	<div class="mc-modal-content">
		<div class="mc-modal-header">
			<h2 id="mc-editor-title">Edit Snippet</h2>
			<button type="button" class="mc-modal-close" aria-label="Close editor">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
		</div>
		<div class="mc-modal-body">
			<!-- New Snippet Form Screen -->
			<div id="mc-new-snippet-form" class="mc-modal-screen">
				<div class="mc-form-group">
					<label for="mc-snippet-name">Snippet Name</label>
					<input type="text" id="mc-snippet-name" class="regular-text" placeholder="e.g., Custom Shortcodes">
					<p class="description">Enter a descriptive name for your snippet. The filename will be auto-generated.</p>
				</div>
				<div class="mc-form-group">
					<label for="mc-snippet-description">Description</label>
					<textarea id="mc-snippet-description" class="large-text" rows="3" placeholder="Describe what this snippet does..."></textarea>
					<p class="description">Briefly describe the purpose of this snippet.</p>
				</div>
				<div class="mc-form-group">
					<label>Generated Filename</label>
					<div id="mc-generated-filename" class="mc-filename-preview">-</div>
				</div>
			</div>
			
			<!-- Editor Screen -->
			<div id="mc-editor-screen" class="mc-modal-screen" style="display: none;">
				<div class="mc-editor-container">
					<textarea id="mc-snippet-editor"></textarea>
				</div>
			</div>
		</div>
		<div class="mc-modal-footer">
			<button type="button" class="button button-secondary mc-modal-close">Cancel</button>
			<button type="button" class="button button-primary" id="mc-create-snippet" style="display: none;">Create Snippet</button>
			<button type="button" class="button button-primary" id="mc-save-snippet">Save Changes</button>
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

/* Modal Styles */
.mc-modal-overlay {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.7);
	z-index: 100000;
	display: flex;
	align-items: center;
	justify-content: center;
}

.mc-modal-content {
	background: #fff;
	border-radius: 4px;
	box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
	max-width: 90vw;
	max-height: 90vh;
	width: 800px;
	display: flex;
	flex-direction: column;
}

.mc-modal-header {
	padding: 20px 20px 0 20px;
	display: flex;
	justify-content: space-between;
	align-items: center;
	border-bottom: 1px solid #ddd;
	padding-bottom: 15px;
}

.mc-modal-header h2 {
	margin: 0;
	font-size: 18px;
}

.mc-modal-close {
	background: none;
	border: none;
	cursor: pointer;
	padding: 5px;
	color: #666;
	font-size: 20px;
}

.mc-modal-close:hover {
	color: #000;
}

.mc-modal-body {
	flex: 1;
	padding: 20px;
	overflow: hidden;
}

.mc-modal-screen {
	display: none; /* Hide all screens by default */
}

.mc-modal-screen.active {
	display: block;
}

/* Form Styles */
.mc-form-group {
	margin-bottom: 20px;
}

.mc-form-group label {
	display: block;
	font-weight: 600;
	margin-bottom: 5px;
}

.mc-form-group input,
.mc-form-group textarea {
	width: 100%;
}

.mc-form-group .description {
	margin-top: 5px;
	color: #666;
	font-style: italic;
}

.mc-filename-preview {
	background: #f1f1f1;
	padding: 8px 12px;
	border: 1px solid #ddd;
	border-radius: 4px;
	font-family: 'Courier New', monospace;
	font-size: 13px;
	color: #333;
}

/* Slide Transition */
.mc-modal-screen {
	transition: transform 0.3s ease-in-out;
}

.mc-modal-screen.slide-out {
	transform: translateX(-100%);
}

.mc-modal-screen.slide-in {
	transform: translateX(0);
}

/* Add Snippet Button */
.mc-add-snippet-section {
	margin-top: 20px;
	padding-top: 20px;
	border-top: 1px solid #ddd;
}

.mc-add-snippet-section .button {
	display: inline-flex;
	align-items: center;
	gap: 5px;
}

.mc-add-snippet-section .dashicons {
	font-size: 16px;
	width: 16px;
	height: 16px;
}

.mc-editor-container {
	height: 500px;
	border: 1px solid #ddd;
	border-radius: 4px;
}

.mc-editor-container .CodeMirror {
	height: 100%;
	font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
	font-size: 13px;
}

.mc-modal-footer {
	padding: 15px 20px 20px 20px;
	display: flex;
	justify-content: flex-end;
	gap: 10px;
	border-top: 1px solid #ddd;
}

/* Clickable snippet links */
.mc-edit-snippet {
	color: #0073aa;
	text-decoration: none;
}

.mc-edit-snippet:hover {
	color: #005177;
	text-decoration: underline;
}

/* Loading state */
.mc-loading {
	opacity: 0.6;
	pointer-events: none;
}

.mc-loading::after {
	content: '';
	position: absolute;
	top: 50%;
	left: 50%;
	width: 20px;
	height: 20px;
	margin: -10px 0 0 -10px;
	border: 2px solid #f3f3f3;
	border-top: 2px solid #0073aa;
	border-radius: 50%;
	animation: spin 1s linear infinite;
}

@keyframes spin {
	0% { transform: rotate(0deg); }
	100% { transform: rotate(360deg); }
}
</style>
