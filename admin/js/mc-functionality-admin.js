(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	// Snippet Editor functionality
	$(function() {
		var codeEditor = null;
		var currentFilename = null;
		var isNewSnippet = false;

		// Initialize CodeMirror editor
		function initCodeEditor() {
			if (codeEditor) {
				codeEditor.toTextArea();
			}

			var textarea = document.getElementById('mc-snippet-editor');
			if (textarea) {
				codeEditor = wp.codeEditor.initialize(textarea, {
					codemirror: {
						mode: 'php',
						lineNumbers: true,
						lineWrapping: true,
						indentUnit: 4,
						tabSize: 4,
						indentWithTabs: true,
						theme: 'default',
						extraKeys: {
							"Tab": function(cm) {
								cm.replaceSelection("    ", "end");
							}
						}
					}
				});
				
				// Make first line read-only after editor is initialized
				setTimeout(function() {
					makeFirstLineReadOnly();
				}, 100);
			}
		}

		// Make the first line (<?php) read-only
		function makeFirstLineReadOnly() {
			if (codeEditor && codeEditor.codemirror) {
				// Mark first line as read-only
				codeEditor.codemirror.markText(
					{line: 0, ch: 0},
					{line: 0, ch: 5},
					{
						readOnly: true,
						css: "background-color: #f0f0f0; color: #666;"
					}
				);
				
				// Prevent editing of first line
				codeEditor.codemirror.on('beforeChange', function(cm, change) {
					if (change.from.line === 0) {
						change.cancel();
					}
				});
			}
		}

		// Show modal for new snippet
		$(document).on('click', '#mc-add-snippet-btn', function(e) {
			e.preventDefault();
			
			isNewSnippet = true;
			currentFilename = null;
			
			// Show modal with new snippet form
			$('#mc-snippet-editor-modal').show();
			$('#mc-editor-title').text('Create New Snippet');
			$('#mc-new-snippet-form').addClass('active');
			$('#mc-editor-screen').removeClass('active').hide();
			$('#mc-create-snippet').show();
			$('#mc-save-snippet').hide();
			
			// Clear form
			$('#mc-snippet-name').val('');
			$('#mc-snippet-description').val('');
			$('#mc-generated-filename').text('-');
			
			// Focus on name field
			$('#mc-snippet-name').focus();
		});

		// Show modal and load snippet
		$(document).on('click', '.mc-edit-snippet', function(e) {
			e.preventDefault();
			
			var filename = $(this).data('filename');
			currentFilename = filename;
			isNewSnippet = false;
			
			// Show modal with editor
			$('#mc-snippet-editor-modal').show();
			$('#mc-editor-title').text('Edit: ' + filename);
			$('#mc-new-snippet-form').removeClass('active').hide();
			$('#mc-editor-screen').addClass('active').show();
			$('#mc-create-snippet').hide();
			$('#mc-save-snippet').show();
			
			// Initialize editor
			initCodeEditor();
			
			// Load snippet content
			loadSnippetContent(filename);
		});

		// Real-time filename preview
		$(document).on('input', '#mc-snippet-name', function() {
			var name = $(this).val();
			if (name) {
				var filename = generateFilename(name);
				$('#mc-generated-filename').text(filename);
			} else {
				$('#mc-generated-filename').text('-');
			}
		});

		// Generate filename from name
		function generateFilename(name) {
			var filename = name.toLowerCase();
			filename = filename.replace(/[^a-z0-9\s-]/g, '');
			filename = filename.replace(/[\s-]+/g, '-');
			filename = filename.replace(/^-+|-+$/g, '');
			if (!filename.endsWith('.php')) {
				filename += '.php';
			}
			return filename;
		}

		// Create new snippet
		$(document).on('click', '#mc-create-snippet', function() {
			var name = $('#mc-snippet-name').val().trim();
			var description = $('#mc-snippet-description').val().trim();
			
			if (!name) {
				alert('Please enter a snippet name.');
				$('#mc-snippet-name').focus();
				return;
			}
			
			createNewSnippet(name, description);
		});

		// Create new snippet via AJAX
		function createNewSnippet(name, description) {
			$('#mc-create-snippet').prop('disabled', true).text('Creating...');
			
			$.ajax({
				url: mc_functionality_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'mc_functionality_create_snippet',
					name: name,
					description: description,
					nonce: mc_functionality_ajax.nonce
				},
				success: function(response) {
					$('#mc-create-snippet').prop('disabled', false).text('Create Snippet');
					
					if (response.success) {
						// Slide to editor screen
						slideToEditor(response.data.content, response.data.filename);
					} else {
						alert('Error creating snippet: ' + response.data);
					}
				},
				error: function() {
					$('#mc-create-snippet').prop('disabled', false).text('Create Snippet');
					alert('Error creating snippet. Please try again.');
				}
			});
		}

		// Slide transition to editor
		function slideToEditor(content, filename) {
			currentFilename = filename;
			
			// Initialize editor if not already done
			if (!codeEditor) {
				initCodeEditor();
			}
			
			// Set content
			codeEditor.codemirror.setValue(content);
			codeEditor.codemirror.refresh();
			
			// Slide transition
			$('#mc-new-snippet-form').addClass('slide-out');
			setTimeout(function() {
				$('#mc-new-snippet-form').removeClass('active slide-out').hide();
				$('#mc-editor-screen').addClass('active slide-in').show();
				$('#mc-create-snippet').hide();
				$('#mc-save-snippet').show();
				$('#mc-editor-title').text('Edit: ' + filename);
				
				// Focus on editor
				codeEditor.codemirror.focus();
			}, 300);
		}

		// Close modal
		$(document).on('click', '.mc-modal-close', function() {
			closeModal();
		});

		// Close modal on escape key
		$(document).on('keydown', function(e) {
			if (e.keyCode === 27 && $('#mc-snippet-editor-modal').is(':visible')) {
				closeModal();
			}
		});

		// Close modal on background click
		$(document).on('click', '.mc-modal-overlay', function(e) {
			if (e.target === this) {
				closeModal();
			}
		});

		// Save snippet
		$(document).on('click', '#mc-save-snippet', function() {
			if (currentFilename && codeEditor) {
				saveSnippetContent(currentFilename, codeEditor.codemirror.getValue());
			}
		});

		// Load snippet content via AJAX
		function loadSnippetContent(filename) {
			$('.mc-modal-content').addClass('mc-loading');
			
			$.ajax({
				url: mc_functionality_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'mc_functionality_get_snippet',
					filename: filename,
					nonce: mc_functionality_ajax.nonce
				},
				success: function(response) {
					$('.mc-modal-content').removeClass('mc-loading');
					
					if (response.success) {
						codeEditor.codemirror.setValue(response.data.content);
						codeEditor.codemirror.refresh();
					} else {
						alert('Error loading snippet: ' + response.data);
						closeModal();
					}
				},
				error: function() {
					$('.mc-modal-content').removeClass('mc-loading');
					alert('Error loading snippet. Please try again.');
					closeModal();
				}
			});
		}

		// Save snippet content via AJAX
		function saveSnippetContent(filename, content) {
			$('#mc-save-snippet').prop('disabled', true).text('Saving...');
			
			$.ajax({
				url: mc_functionality_ajax.ajax_url,
				type: 'POST',
				data: {
					action: 'mc_functionality_save_snippet',
					filename: filename,
					content: content,
					nonce: mc_functionality_ajax.nonce
				},
				success: function(response) {
					$('#mc-save-snippet').prop('disabled', false).text('Save Changes');
					
					if (response.success) {
						alert('Snippet saved successfully!');
						closeModal();
						// Optionally reload the page to show updated content
						location.reload();
					} else {
						alert('Error saving snippet: ' + response.data);
					}
				},
				error: function() {
					$('#mc-save-snippet').prop('disabled', false).text('Save Changes');
					alert('Error saving snippet. Please try again.');
				}
			});
		}

		// Close modal function
		function closeModal() {
			$('#mc-snippet-editor-modal').hide();
			
			// Reset modal state
			$('#mc-new-snippet-form').removeClass('active slide-out slide-in').hide();
			$('#mc-editor-screen').removeClass('active slide-out slide-in').hide();
			$('#mc-create-snippet').hide();
			$('#mc-save-snippet').show();
			
			if (codeEditor) {
				codeEditor.codemirror.setValue('');
			}
			
			currentFilename = null;
			isNewSnippet = false;
		}
	});

})( jQuery );
