<?php

/**
 * The snippet loader class
 *
 * Responsible for loading and executing PHP files from the code-snippets directory.
 *
 * @link       https://www.mattcromwell.com
 * @since      1.0.0
 *
 * @package    Mc_Functionality
 * @subpackage Mc_Functionality/includes
 */

/**
 * The snippet loader class.
 *
 * Loads and executes PHP files from the code-snippets directory safely.
 *
 * @since      1.0.0
 * @package    Mc_Functionality
 * @subpackage Mc_Functionality/includes
 * @author     Matt Cromwell <info@mattcromwell.com>
 */
class Mc_Functionality_Snippet_Loader {

	/**
	 * The path to the code snippets directory.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $snippets_dir    The path to the code snippets directory.
	 */
	private $snippets_dir;

	/**
	 * Initialize the snippet loader.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->snippets_dir = MC_FUNCTIONALITY_SNIPPETS_DIR;
	}

	/**
	 * Load all PHP files from the code-snippets directory.
	 *
	 * Safely loads and executes all .php files found in the code-snippets directory
	 * using require_once to prevent duplicate loading.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function load_snippets() {
		// Check if the snippets directory exists
		if ( ! is_dir( $this->snippets_dir ) ) {
			return;
		}

		// Get all PHP files from the snippets directory
		$php_files = glob( $this->snippets_dir . '/*.php' );

		// If no PHP files found, return early
		if ( empty( $php_files ) ) {
			return;
		}

		// Load each PHP file safely
		foreach ( $php_files as $file ) {
			$this->load_snippet_file( $file );
		}
	}

	/**
	 * Load a single snippet file safely.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $file_path    The path to the PHP file to load.
	 */
	private function load_snippet_file( $file_path ) {
		// Validate file path
		if ( ! $this->is_valid_snippet_file( $file_path ) ) {
			return;
		}

		// Load the file safely
		try {
			require_once $file_path;
		} catch ( Exception $e ) {
			// Log error if WP_DEBUG is enabled
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'MC Functionality: Error loading snippet file: ' . $file_path . ' - ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Validate if a file is a safe snippet file to load.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string    $file_path    The path to the file to validate.
	 * @return   bool                    True if the file is valid, false otherwise.
	 */
	private function is_valid_snippet_file( $file_path ) {
		// Check if file exists and is readable
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return false;
		}

		// Check if file is within the snippets directory (security check)
		$real_file_path = realpath( $file_path );
		$real_snippets_dir = realpath( $this->snippets_dir );
		
		if ( $real_file_path === false || $real_snippets_dir === false ) {
			return false;
		}

		// Ensure the file is within the snippets directory
		if ( strpos( $real_file_path, $real_snippets_dir ) !== 0 ) {
			return false;
		}

		// Check if file has .php extension
		if ( pathinfo( $file_path, PATHINFO_EXTENSION ) !== 'php' ) {
			return false;
		}

		// Skip index.php files
		if ( basename( $file_path ) === 'index.php' ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the list of loaded snippet files.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   array    Array of loaded snippet file paths.
	 */
	public function get_loaded_snippets() {
		if ( ! is_dir( $this->snippets_dir ) ) {
			return array();
		}

		$php_files = glob( $this->snippets_dir . '/*.php' );
		$valid_files = array();

		foreach ( $php_files as $file ) {
			if ( $this->is_valid_snippet_file( $file ) ) {
				$valid_files[] = $file;
			}
		}

		return $valid_files;
	}

}