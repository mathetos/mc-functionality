<?php
/**
 * Sample Code Snippet
 * 
 * This is an example of how to create code snippets for the MC Functionality plugin.
 * Place your PHP code here and it will be automatically loaded and executed.
 * 
 * @package Mc_Functionality
 */

// Example: Add a custom shortcode
add_shortcode( 'mc_sample', function( $atts ) {
	$atts = shortcode_atts( array(
		'message' => 'Hello from MC Functionality! 🥰'
	), $atts );
	
	return '<div class="mc-sample-snippet">' . esc_html( $atts['message'] ) . '</div>';
} );

// Example: Add a custom action hook
add_action( 'wp_footer', function() {
	echo '<!-- MC Functionality snippet loaded successfully -->';
} );

// Example: Modify the excerpt length
add_filter( 'excerpt_length', function( $length ) {
	return 25; // Show only 25 words in excerpts
} );