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
	 * Load all PHP snippets from the snippets directory.
	 *
	 * @since    1.0.0
	 */
	public function load_snippets() {
		if ( ! is_dir( $this->snippets_dir ) ) {
			return;
		}

		// Get all .php files (excluding .php.disabled files)
		$php_files = glob( $this->snippets_dir . '/*.php' );
		
		if ( empty( $php_files ) ) {
			return;
		}

		foreach ( $php_files as $file_path ) {
			// Skip if this is a disabled file
			if ( $this->is_disabled_file( $file_path ) ) {
				continue;
			}
			
			// Skip index.php (security file, not a snippet)
			if ( basename( $file_path ) === 'index.php' ) {
				continue;
			}
			
			if ( $this->is_valid_snippet_file( $file_path ) ) {
				$this->load_snippet_file( $file_path );
			}
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

		// Load the file safely with comprehensive error handling
		try {
			// Set up error handling to catch fatal errors
			set_error_handler( array( $this, 'handle_snippet_error' ) );
			
			// Load the file
			require_once $file_path;
			
			// Restore error handler
			restore_error_handler();
			
		} catch ( Error $e ) {
			// Catch fatal errors (PHP 7+)
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'MC Functionality: Fatal error loading snippet file: ' . $file_path . ' - ' . $e->getMessage() . ' on line ' . $e->getLine() );
			}
			restore_error_handler();
		} catch ( Exception $e ) {
			// Catch regular exceptions
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'MC Functionality: Exception loading snippet file: ' . $file_path . ' - ' . $e->getMessage() );
			}
			restore_error_handler();
		}
	}

	/**
	 * Handle errors during snippet loading.
	 *
	 * @since    1.0.0
	 * @param    int    $errno      Error level.
	 * @param    string $errstr     Error message.
	 * @param    string $errfile    File where error occurred.
	 * @param    int    $errline    Line number where error occurred.
	 * @return   bool               True to prevent default error handler.
	 */
	public function handle_snippet_error( $errno, $errstr, $errfile, $errline ) {
		// Only handle fatal errors
		if ( $errno === E_ERROR || $errno === E_PARSE || $errno === E_CORE_ERROR || $errno === E_COMPILE_ERROR ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'MC Functionality: Fatal error in snippet file: ' . $errfile . ' - ' . $errstr . ' on line ' . $errline );
			}
			return true; // Prevent default error handler
		}
		
		return false; // Let default error handler handle other errors
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
	 * Check if a file is disabled (has .disabled extension).
	 *
	 * @since    1.0.0
	 * @param    string $file_path The file path to check.
	 * @return   bool   True if file is disabled, false otherwise.
	 */
	private function is_disabled_file( $file_path ) {
		return file_exists( $file_path . '.disabled' );
	}

	/**
	 * Get the enabled/disabled status of a snippet file.
	 *
	 * @since    1.0.0
	 * @param    string $filename The filename to check.
	 * @return   bool   True if enabled, false if disabled.
	 */
	public function is_snippet_enabled( $filename ) {
		$file_path = $this->snippets_dir . '/' . $filename;
		return ! $this->is_disabled_file( $file_path );
	}

	/**
	 * Enable a snippet by removing the .disabled extension.
	 *
	 * @since    1.0.0
	 * @param    string $filename The filename to enable.
	 * @return   bool   True on success, false on failure.
	 */
	public function enable_snippet( $filename ) {
		$file_path = $this->snippets_dir . '/' . $filename;
		$disabled_path = $file_path . '.disabled';
		
		error_log( 'MC Functionality: Enable snippet - File path: ' . $file_path );
		error_log( 'MC Functionality: Enable snippet - Disabled path: ' . $disabled_path );
		
		// Check if disabled file exists
		if ( ! file_exists( $disabled_path ) ) {
			error_log( 'MC Functionality: Enable snippet - Disabled file does not exist, already enabled' );
			return true; // Already enabled
		}
		
		// Validate the disabled file is within snippets directory
		$real_disabled_path = realpath( $disabled_path );
		$real_snippets_dir = realpath( $this->snippets_dir );
		
		if ( $real_disabled_path === false || $real_snippets_dir === false || strpos( $real_disabled_path, $real_snippets_dir ) !== 0 ) {
			error_log( 'MC Functionality: Enable snippet - Path validation failed' );
			return false;
		}
		
		// Remove .disabled extension
		$result = rename( $disabled_path, $file_path );
		error_log( 'MC Functionality: Enable snippet - Rename result: ' . ( $result ? 'true' : 'false' ) );
		return $result;
	}

	/**
	 * Disable a snippet by adding the .disabled extension.
	 *
	 * @since    1.0.0
	 * @param    string $filename The filename to disable.
	 * @return   bool   True on success, false on failure.
	 */
	public function disable_snippet( $filename ) {
		$file_path = $this->snippets_dir . '/' . $filename;
		$disabled_path = $file_path . '.disabled';
		
		error_log( 'MC Functionality: Disable snippet - File path: ' . $file_path );
		error_log( 'MC Functionality: Disable snippet - Disabled path: ' . $disabled_path );
		
		// Check if already disabled
		if ( file_exists( $disabled_path ) ) {
			error_log( 'MC Functionality: Disable snippet - Already disabled' );
			return true; // Already disabled
		}
		
		// Validate the file exists and is within snippets directory
		if ( ! $this->is_valid_snippet_file( $file_path ) ) {
			error_log( 'MC Functionality: Disable snippet - Invalid file validation failed' );
			return false;
		}
		
		// Add .disabled extension
		$result = rename( $file_path, $disabled_path );
		error_log( 'MC Functionality: Disable snippet - Rename result: ' . ( $result ? 'true' : 'false' ) );
		return $result;
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

	/**
	 * Get all snippets (enabled and disabled) for admin display.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @return   array    Array of snippet data for admin display.
	 */
	public function get_all_snippets() {
		if ( ! is_dir( $this->snippets_dir ) ) {
			return array();
		}

		$all_snippets = array();
		
		// Get all .php files (enabled snippets)
		$php_files = glob( $this->snippets_dir . '/*.php' );
		foreach ( $php_files as $file_path ) {
			$filename = basename( $file_path );
			
			// Skip index.php
			if ( $filename === 'index.php' ) {
				continue;
			}
			
			// Skip if this is a disabled file
			if ( $this->is_disabled_file( $file_path ) ) {
				continue;
			}
			
			if ( $this->is_valid_snippet_file( $file_path ) ) {
				$all_snippets[] = array(
					'filename' => $filename,
					'path' => $file_path,
					'enabled' => true,
					'status' => 'enabled'
				);
			}
		}
		
		// Get all .php.disabled files (disabled snippets)
		$disabled_files = glob( $this->snippets_dir . '/*.php.disabled' );
		foreach ( $disabled_files as $file_path ) {
			$filename = basename( $file_path, '.disabled' );
			
			// Skip index.php
			if ( $filename === 'index.php' ) {
				continue;
			}
			
			$all_snippets[] = array(
				'filename' => $filename,
				'path' => $file_path,
				'enabled' => false,
				'status' => 'disabled'
			);
		}
		
		return $all_snippets;
	}

}