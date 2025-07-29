<?php
/**
 * Snippet Editor Modal Component
 *
 * @package    Mc_Functionality
 * @subpackage Mc_Functionality/admin/partials/components
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>

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
			<!-- Error Message Area -->
			<div id="mc-modal-error" class="mc-error-message" style="display: none;">
				<div class="mc-error-content">
					<span class="dashicons dashicons-warning"></span>
					<span id="mc-error-text"></span>
				</div>
				<button type="button" class="mc-error-close" aria-label="Close error message">
					<span class="dashicons dashicons-no-alt"></span>
				</button>
			</div>
			
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
			<div id="mc-editor-screen" class="mc-modal-screen">
				<div class="mc-editor-container">
					<textarea id="mc-snippet-editor"></textarea>
				</div>
				
				<!-- Snippet Settings -->
				<div class="mc-snippet-settings">
					<div class="mc-settings-row">
						<div class="mc-setting-group">
							<label for="mc-run-context">Run this snippet...</label>
							<select id="mc-run-context" class="regular-text">
								<option value="everywhere">Everywhere (default)</option>
								<option value="admin-only">Only in admin area</option>
								<option value="frontend-only">Only on the front-end</option>
								<option value="once">Only once</option>
							</select>
						</div>
						
						<div class="mc-setting-group">
							<label for="mc-execution-priority">Execution Priority</label>
							<input type="number" id="mc-execution-priority" class="small-text" value="10" min="1" max="100">
							<p class="description">Lower numbers = higher priority (default: 10)</p>
						</div>
					</div>
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