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
			}
		}

		// Show modal and load snippet
		$(document).on('click', '.mc-edit-snippet', function(e) {
			e.preventDefault();
			
			var filename = $(this).data('filename');
			currentFilename = filename;
			
			// Show modal
			$('#mc-snippet-editor-modal').show();
			$('#mc-editor-title').text('Edit: ' + filename);
			
			// Initialize editor
			initCodeEditor();
			
			// Load snippet content
			loadSnippetContent(filename);
		});

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
			if (codeEditor) {
				codeEditor.codemirror.setValue('');
			}
			currentFilename = null;
		}
	});

})( jQuery );
