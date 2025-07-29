<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.mattcromwell.com
 * @since      1.0.0
 *
 * @package    Mc_Functionality
 * @subpackage Mc_Functionality/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Mc_Functionality
 * @subpackage Mc_Functionality/admin
 * @author     Matt Cromwell <info@mattcromwell.com>
 */
class Mc_Functionality_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the admin menu.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_plugins_page(
			'Site Functions', // Page title
			'Site Functions', // Menu title
			'manage_options', // Capability required
			'mc-functionality', // Menu slug
			array( $this, 'display_admin_page' ) // Callback function
		);
	}

	/**
	 * Display the admin page content.
	 *
	 * @since    1.0.0
	 */
	public function display_admin_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/mc-functionality-admin-display.php';
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mc_Functionality_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mc_Functionality_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/mc-functionality-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Mc_Functionality_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Mc_Functionality_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/mc-functionality-admin.js', array( 'jquery' ), $this->version, false );

		// Enqueue WordPress code editor for snippet editing
		wp_enqueue_code_editor( array( 'type' => 'text/x-php' ) );

		// Localize script for AJAX
		wp_localize_script( $this->plugin_name, 'mc_functionality_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'mc_functionality_editor_nonce' ),
		) );

	}

	/**
	 * Register AJAX handlers for snippet editor.
	 *
	 * @since    1.0.0
	 */
	public function register_ajax_handlers() {
		add_action( 'wp_ajax_mc_functionality_get_snippet', array( $this, 'ajax_get_snippet' ) );
		add_action( 'wp_ajax_mc_functionality_save_snippet', array( $this, 'ajax_save_snippet' ) );
		add_action( 'wp_ajax_mc_functionality_create_snippet', array( $this, 'ajax_create_snippet' ) );
	}

	/**
	 * AJAX handler to get snippet content.
	 *
	 * @since    1.0.0
	 */
	public function ajax_get_snippet() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'mc_functionality_editor_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$filename = sanitize_text_field( $_POST['filename'] );
		
		// Validate filename
		if ( empty( $filename ) || ! preg_match( '/^[a-zA-Z0-9\-_\.]+\.php$/', $filename ) ) {
			wp_send_json_error( 'Invalid filename' );
		}

		$file_path = MC_FUNCTIONALITY_SNIPPETS_DIR . '/' . $filename;

		// Security check: ensure file is within snippets directory
		$real_file_path = realpath( $file_path );
		$real_snippets_dir = realpath( MC_FUNCTIONALITY_SNIPPETS_DIR );
		
		if ( $real_file_path === false || $real_snippets_dir === false || strpos( $real_file_path, $real_snippets_dir ) !== 0 ) {
			wp_send_json_error( 'File not found or access denied' );
		}

		// Check if file exists and is readable
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			wp_send_json_error( 'File not found or not readable' );
		}

		// Read file content
		$content = file_get_contents( $file_path );
		if ( $content === false ) {
			wp_send_json_error( 'Unable to read file' );
		}

		wp_send_json_success( array(
			'content' => $content,
			'filename' => $filename,
		) );
	}

	/**
	 * AJAX handler to save snippet content.
	 *
	 * @since    1.0.0
	 */
	public function ajax_save_snippet() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'mc_functionality_editor_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$filename = sanitize_text_field( $_POST['filename'] );
		$content = wp_unslash( $_POST['content'] );
		
		// Validate filename
		if ( empty( $filename ) || ! preg_match( '/^[a-zA-Z0-9\-_\.]+\.php$/', $filename ) ) {
			wp_send_json_error( 'Invalid filename' );
		}

		$file_path = MC_FUNCTIONALITY_SNIPPETS_DIR . '/' . $filename;

		// Security check: ensure file is within snippets directory
		$real_file_path = realpath( dirname( $file_path ) );
		$real_snippets_dir = realpath( MC_FUNCTIONALITY_SNIPPETS_DIR );
		
		if ( $real_file_path === false || $real_snippets_dir === false || strpos( $real_file_path, $real_snippets_dir ) !== 0 ) {
			wp_send_json_error( 'File not found or access denied' );
		}

		// Write file content
		$result = file_put_contents( $file_path, $content );
		if ( $result === false ) {
			wp_send_json_error( 'Unable to save file' );
		}

		wp_send_json_success( array(
			'message' => 'File saved successfully',
			'filename' => $filename,
		) );
	}

	/**
	 * AJAX handler to create a new snippet file.
	 *
	 * @since    1.0.0
	 */
	public function ajax_create_snippet() {
		// Verify nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], 'mc_functionality_editor_nonce' ) ) {
			wp_die( 'Security check failed' );
		}

		// Check permissions
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Insufficient permissions' );
		}

		$name = sanitize_text_field( $_POST['name'] );
		$description = sanitize_textarea_field( $_POST['description'] );
		
		// Validate name
		if ( empty( $name ) ) {
			wp_send_json_error( 'Snippet name is required' );
		}

		// Generate filename from name
		$filename = $this->generate_filename_from_name( $name );
		
		// Check if file already exists
		$file_path = MC_FUNCTIONALITY_SNIPPETS_DIR . '/' . $filename;
		if ( file_exists( $file_path ) ) {
			wp_send_json_error( 'A snippet with this name already exists' );
		}

		// Generate initial content
		$content = $this->generate_snippet_template( $name, $description );

		// Write file content
		$result = file_put_contents( $file_path, $content );
		if ( $result === false ) {
			wp_send_json_error( 'Unable to create file' );
		}

		wp_send_json_success( array(
			'message' => 'Snippet created successfully',
			'filename' => $filename,
			'content' => $content,
		) );
	}

	/**
	 * Generate a filename from a snippet name.
	 *
	 * @since    1.0.0
	 * @param    string    $name    The snippet name.
	 * @return   string              The generated filename.
	 */
	private function generate_filename_from_name( $name ) {
		// Convert to lowercase
		$filename = strtolower( $name );
		
		// Replace spaces and special characters with dashes
		$filename = preg_replace( '/[^a-z0-9\s-]/', '', $filename );
		$filename = preg_replace( '/[\s-]+/', '-', $filename );
		$filename = trim( $filename, '-' );
		
		// Ensure it ends with .php
		if ( ! str_ends_with( $filename, '.php' ) ) {
			$filename .= '.php';
		}
		
		return $filename;
	}

	/**
	 * Generate initial snippet template content.
	 *
	 * @since    1.0.0
	 * @param    string    $name         The snippet name.
	 * @param    string    $description  The snippet description.
	 * @return   string                  The template content.
	 */
	private function generate_snippet_template( $name, $description ) {
		$template = "<?php\n";
		$template .= "/**\n";
		$template .= " * " . esc_html( $name ) . "\n";
		$template .= " * \n";
		$template .= " * " . esc_html( $description ) . "\n";
		$template .= " * \n";
		$template .= " * @package Mc_Functionality\n";
		$template .= " */\n\n";
		$template .= "// Your code here\n";
		
		return $template;
	}

}
