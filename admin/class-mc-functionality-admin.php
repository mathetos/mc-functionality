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
		// Get all snippets with their status
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-mc-functionality-snippet-loader.php';
		$snippet_loader = new Mc_Functionality_Snippet_Loader();
		
		// Get all .php files (both enabled and disabled)
		$all_snippets = array();
		$snippets_dir = MC_FUNCTIONALITY_SNIPPETS_DIR;
		
		if ( is_dir( $snippets_dir ) ) {
			$php_files = glob( $snippets_dir . '/*.php' );
			$disabled_files = glob( $snippets_dir . '/*.php.disabled' );
			
			// Process enabled files
			foreach ( $php_files as $file_path ) {
				$filename = basename( $file_path );
				
				// Skip index.php (security file, not a snippet)
				if ( $filename === 'index.php' ) {
					continue;
				}
				
				$all_snippets[] = array(
					'filename' => $filename,
					'path' => $file_path,
					'enabled' => true,
					'status' => 'enabled'
				);
			}
			
			// Process disabled files
			foreach ( $disabled_files as $file_path ) {
				$filename = str_replace( '.disabled', '', basename( $file_path ) );
				
				// Skip index.php (security file, not a snippet)
				if ( $filename === 'index.php' ) {
					continue;
				}
				
				$all_snippets[] = array(
					'filename' => $filename,
					'path' => str_replace( '.disabled', '', $file_path ),
					'enabled' => false,
					'status' => 'disabled'
				);
			}
		}
		
		include plugin_dir_path( __FILE__ ) . 'partials/mc-functionality-admin-display.php';
	}

	/**
	 * Add plugin action links to the plugin list.
	 *
	 * @since    1.0.0
	 * @param    array $links The existing plugin action links.
	 * @return   array The modified plugin action links.
	 */
	public function add_plugin_action_links( $links ) {
		// Add "Site Functions" link with icon
		$settings_link = '<a href="' . admin_url( 'plugins.php?page=mc-functionality' ) . '">' . __( 'Site Functions', 'mc-functionality' ) . '</a>';
		
		// Insert the link at the beginning of the array
		array_unshift( $links, $settings_link );
		
		return $links;
	}

	/**
	 * Check PHP code for fatal errors using comprehensive validation.
	 *
	 * @since    1.0.0
	 * @param    string    $php_code    The PHP code to validate.
	 * @return   true|WP_Error    True if valid, WP_Error if error.
	 */
	/**
	 * Simple PHP validation using built-in PHP tools.
	 *
	 * @since    1.0.0
	 * @param    string    $php_code    The PHP code to validate.
	 * @return   true|WP_Error    True if valid, WP_Error if error.
	 */
	private function validate_php_code( $php_code ) {
		// Basic content validation
		if ( empty( trim( $php_code ) ) ) {
			return new WP_Error( 'empty_content', 'Snippet content cannot be empty.' );
		}

		// Check for dangerous functions
		$dangerous_functions = array( 'eval', 'exec', 'system', 'shell_exec', 'passthru' );
		foreach ( $dangerous_functions as $func ) {
			if ( strpos( $php_code, $func . '(' ) !== false ) {
				return new WP_Error( 'dangerous_function', "The use of '$func()' function is not allowed for security reasons." );
			}
		}

		// Add <?php tag if not present
		if ( strpos( trim( $php_code ), '<?php' ) !== 0 ) {
			$php_code = "<?php\n" . $php_code;
		}

		// Use PHP's built-in tokenizer for syntax validation
		$tokens = token_get_all( $php_code );
		if ( $tokens === false ) {
			return new WP_Error( 'syntax_error', 'Unable to parse PHP code. Please check for syntax errors like missing semicolons, brackets, or quotes.' );
		}

		// Check for obvious fatal error patterns (only exact matches to avoid false positives)
		$dangerous_patterns = array(
			'undefined_function_name',
			'undefined_class_name',
			'undefined_constant',
		);
		
		foreach ( $dangerous_patterns as $pattern ) {
			if ( strpos( $php_code, $pattern ) !== false ) {
				return new WP_Error( 'potential_fatal_error', "Code contains potentially undefined function/class: $pattern" );
			}
		}
		
		// Add debug logging to see if we reach the runtime test
		error_log( 'MC Functionality: validate_php_code() - Static checks passed, proceeding to runtime test' );

		// Test for runtime fatal errors by executing the code in a safe environment
		error_log( 'MC Functionality: About to call test_runtime_execution()' );
		$runtime_error = $this->test_runtime_execution( $php_code );
		error_log( 'MC Functionality: test_runtime_execution() returned: ' . ( is_wp_error( $runtime_error ) ? 'ERROR: ' . $runtime_error->get_error_message() : 'SUCCESS' ) );
		
		if ( is_wp_error( $runtime_error ) ) {
			return $runtime_error;
		}

		return true;
	}

	/**
	 * Test code execution in a safe environment to catch runtime fatal errors.
	 *
	 * @since    1.0.0
	 * @param    string    $php_code    The PHP code to test.
	 * @return   true|WP_Error    True if valid, WP_Error if error.
	 */
	private function test_runtime_execution( $php_code ) {
		error_log( 'MC Functionality: Testing runtime execution...' );
		
		// Get list of functions defined in this snippet
		$defined_functions = array();
		if ( preg_match_all( '/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $php_code, $matches ) ) {
			$defined_functions = $matches[1];
			error_log( 'MC Functionality: Functions defined in snippet: ' . implode( ', ', $defined_functions ) );
		}
		
		// Extract function calls from the code
		if ( preg_match_all( '/([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/', $php_code, $matches ) ) {
			$function_calls = array_unique( $matches[1] );
			error_log( 'MC Functionality: Found function calls: ' . implode( ', ', $function_calls ) );
			
			// List of built-in PHP functions and WordPress functions that are safe
			$safe_functions = array(
				// PHP built-in functions
				'echo', 'print', 'var_dump', 'print_r', 'isset', 'empty', 'is_array', 'is_string', 'is_numeric',
				'strlen', 'strpos', 'str_replace', 'substr', 'trim', 'explode', 'implode', 'array_merge',
				'count', 'sizeof', 'array_keys', 'array_values', 'in_array', 'array_search',
				'date', 'time', 'strtotime', 'date_format', 'microtime',
				'file_exists', 'is_file', 'is_dir', 'file_get_contents', 'file_put_contents',
				'json_encode', 'json_decode', 'serialize', 'unserialize',
				'add_action', 'add_filter', 'apply_filters', 'do_action', 'wp_enqueue_script', 'wp_enqueue_style',
				'get_option', 'update_option', 'delete_option', 'get_post_meta', 'update_post_meta',
				'wp_insert_post', 'wp_update_post', 'wp_delete_post', 'get_posts', 'get_pages',
				'wp_query', 'get_the_title', 'get_the_content', 'get_the_excerpt',
				'esc_html', 'esc_attr', 'esc_url', 'sanitize_text_field', 'wp_kses_post',
				'current_user_can', 'is_user_logged_in', 'wp_get_current_user',
				'admin_url', 'site_url', 'home_url', 'get_template_directory', 'get_stylesheet_directory',
				'wp_upload_dir', 'wp_mail', 'wp_redirect', 'wp_die', 'wp_send_json_success', 'wp_send_json_error',
				// Add more WordPress functions as needed
			);
			
			foreach ( $function_calls as $function ) {
				// Skip if it's a safe function
				if ( in_array( $function, $safe_functions ) ) {
					continue;
				}
				
				// Skip if it's a variable function call (e.g., $func())
				if ( $function[0] === '$' ) {
					continue;
				}
				
				// Skip if it's defined in this snippet
				if ( in_array( $function, $defined_functions ) ) {
					continue;
				}
				
				// Check if the function exists
				if ( ! function_exists( $function ) ) {
					error_log( 'MC Functionality: Undefined function detected: ' . $function );
					return new WP_Error( 'runtime_error', "The function '$function' does not exist. Please check the function name or define it before using." );
				}
			}
		}
		
		// Check for class instantiation
		if ( preg_match_all( '/new\s+([a-zA-Z_][a-zA-Z0-9_]*)/', $php_code, $matches ) ) {
			$classes = array_unique( $matches[1] );
			error_log( 'MC Functionality: Found class instantiations: ' . implode( ', ', $classes ) );
			
			foreach ( $classes as $class ) {
				if ( ! class_exists( $class ) ) {
					error_log( 'MC Functionality: Undefined class detected: ' . $class );
					return new WP_Error( 'runtime_error', "The class '$class' does not exist. Please check the class name or ensure it's properly included." );
				}
			}
		}
		
		// Check for type errors only for functions defined in this snippet
		foreach ( $defined_functions as $func_name ) {
			// Look for function calls to this function
			if ( preg_match_all( '/' . preg_quote( $func_name ) . '\s*\(([^)]*)\)/', $php_code, $call_matches ) ) {
				foreach ( $call_matches[1] as $args ) {
					// Check if arguments contain arrays that might cause type errors
					if ( preg_match( '/\[.*\]/', $args ) ) {
						// Look for the function definition to check for type hints
						if ( preg_match( '/function\s+' . preg_quote( $func_name ) . '\s*\([^)]*\)\s*\{/', $php_code, $def_match ) ) {
							$func_def = $def_match[0];
							if ( preg_match( '/string\s+\$/', $func_def ) ) {
								error_log( 'MC Functionality: Type error detected - array passed to string parameter in function: ' . $func_name );
								return new WP_Error( 'runtime_error', "Type error: The function '$func_name' expects a string parameter, but an array is being passed. This will cause a fatal TypeError at runtime." );
							}
							if ( preg_match( '/int\s+\$/', $func_def ) ) {
								error_log( 'MC Functionality: Type error detected - array passed to int parameter in function: ' . $func_name );
								return new WP_Error( 'runtime_error', "Type error: The function '$func_name' expects an integer parameter, but an array is being passed. This will cause a fatal TypeError at runtime." );
							}
							if ( preg_match( '/float\s+\$/', $func_def ) ) {
								error_log( 'MC Functionality: Type error detected - array passed to float parameter in function: ' . $func_name );
								return new WP_Error( 'runtime_error', "Type error: The function '$func_name' expects a float parameter, but an array is being passed. This will cause a fatal TypeError at runtime." );
							}
							if ( preg_match( '/bool\s+\$/', $func_def ) ) {
								error_log( 'MC Functionality: Type error detected - array passed to bool parameter in function: ' . $func_name );
								return new WP_Error( 'runtime_error', "Type error: The function '$func_name' expects a boolean parameter, but an array is being passed. This will cause a fatal TypeError at runtime." );
							}
						}
					}
				}
			}
		}
		
		error_log( 'MC Functionality: Runtime execution test passed' );
		return true;
	}

	/**
	 * Validate snippet content for syntax errors.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The snippet content to validate.
	 * @return   true|WP_Error    True if valid, WP_Error if syntax error.
	 */
	private function validate_snippet_content( $content ) {
		// First, check for memory-hogging patterns
		$memory_validation = $this->validate_memory_usage( $content );
		if ( is_wp_error( $memory_validation ) ) {
			return $memory_validation;
		}
		
		// Then validate PHP syntax
		return $this->validate_php_code( $content );
	}

	/**
	 * Validate memory usage patterns against current PHP limits.
	 *
	 * @since    1.0.0
	 * @param    string    $content    The snippet content to validate.
	 * @return   true|WP_Error    True if valid, WP_Error if error.
	 */
	private function validate_memory_usage( $content ) {
		error_log( 'MC Functionality: validate_memory_usage() called' );
		$memory_limit = ini_get( 'memory_limit' );
		$memory_limit_bytes = $this->parse_memory_limit( $memory_limit );
		error_log( 'MC Functionality: Memory limit: ' . $memory_limit . ' (' . $memory_limit_bytes . ' bytes)' );
		
		// Define memory-hogging patterns to check
		$memory_checks = array(
			'str_repeat' => array(
				'pattern' => '/str_repeat\s*\(\s*["\'][^"\']*["\']\s*,\s*([^)]+)/',
				'message' => 'str_repeat() with large count',
				'multiplier' => 1024, // Rough estimate: 1KB per character
			),
			'array_fill' => array(
				'pattern' => '/array_fill\s*\(\s*0\s*,\s*([^)]+)/',
				'message' => 'array_fill() with large size',
				'multiplier' => 64, // Rough estimate: 64 bytes per array element
			),
			'range' => array(
				'pattern' => '/range\s*\(\s*0\s*,\s*([^)]+)/',
				'message' => 'range() with large end value',
				'multiplier' => 64, // Rough estimate: 64 bytes per array element
			),
		);
		
		foreach ( $memory_checks as $function => $check ) {
			error_log( 'MC Functionality: Checking pattern for: ' . $function );
			if ( preg_match_all( $check['pattern'], $content, $matches ) ) {
				error_log( 'MC Functionality: Found ' . count( $matches[1] ) . ' matches for ' . $function );
				foreach ( $matches[1] as $expression ) {
					// Clean up the expression and evaluate it safely
					$expression = trim( $expression );
					error_log( 'MC Functionality: Raw expression: ' . $expression );
					
					// Only allow basic mathematical operations for security
					if ( preg_match( '/^[\d\s\*\+\-\(\)]+$/', $expression ) ) {
						// Use eval() in a safe context to evaluate the expression
						$count = @eval( 'return ' . $expression . ';' );
						if ( $count === false || ! is_numeric( $count ) ) {
							error_log( 'MC Functionality: Could not evaluate expression: ' . $expression );
							continue;
						}
					} else {
						// If it's not a simple math expression, try to parse it as a single number
						$count = intval( $expression );
						if ( $count == 0 && $expression !== '0' ) {
							error_log( 'MC Functionality: Could not parse expression as number: ' . $expression );
							continue;
						}
					}
					
					$estimated_memory = $count * $check['multiplier'];
					$safe_threshold = $memory_limit_bytes * 0.3; // 30% of memory limit
					
					error_log( 'MC Functionality: Evaluated count: ' . $count . ', Estimated memory: ' . $estimated_memory . ', Safe threshold: ' . $safe_threshold );
					
					if ( $estimated_memory > $safe_threshold ) {
						error_log( 'MC Functionality: MEMORY RISK DETECTED!' );
						
						// Create more actionable error messages based on the function
						$action_message = '';
						switch ( $function ) {
							case 'str_repeat':
								$action_message = "Consider using a smaller string or count. For large strings, use a loop or chunk the operation.";
								break;
							case 'array_fill':
								$action_message = "Consider using a smaller array size or use a loop to build the array incrementally.";
								break;
							case 'range':
								$action_message = "Consider using a smaller range or use a loop to process values incrementally.";
								break;
							default:
								$action_message = "Consider reducing the size or using a more memory-efficient approach.";
						}
						
						return new WP_Error( 
							'memory_risk', 
							sprintf( 
								'This code would use approximately %s memory, which exceeds the safe limit (%s). %s detected with expression "%s" (evaluates to %d). %s',
								size_format( $estimated_memory ),
								size_format( $safe_threshold ),
								$check['message'],
								$expression,
								$count,
								$action_message
							)
						);
					}
				}
			}
		}
		
		return true;
	}

	/**
	 * Parse memory limit string to bytes.
	 *
	 * @since    1.0.0
	 * @param    string    $limit    Memory limit string (e.g., '128M', '1G').
	 * @return   int       Memory limit in bytes.
	 */
	private function parse_memory_limit( $limit ) {
		$unit = strtolower( substr( $limit, -1 ) );
		$value = intval( $limit );
		
		switch ( $unit ) {
			case 'g':
				return $value * 1024 * 1024 * 1024;
			case 'm':
				return $value * 1024 * 1024;
			case 'k':
				return $value * 1024;
			default:
				return $value;
		}
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

		// Only load CodeMirror and related scripts on the Snippets tab
		$current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'snippets';
		
		if ( $current_tab === 'snippets' ) {
			// Get the selected theme
			$selected_theme = get_option( 'mc_codemirror_theme', 'default' );
			
			// Enqueue the selected theme CSS BEFORE CodeMirror if it's not default
			if ( $selected_theme !== 'default' ) {
				$theme_css_url = plugin_dir_url( __FILE__ ) . 'css/' . $selected_theme . '.css';
				wp_enqueue_style( 'codemirror-theme-' . $selected_theme, $theme_css_url, array(), $this->version );
				
				// Debug logging
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'MC Functionality: Loading theme CSS: ' . $theme_css_url );
				}
			}
			
			// Enqueue WordPress code editor for snippet editing
			wp_enqueue_code_editor( array( 'type' => 'text/x-php' ) );

			// Get current PHP environment information
			$memory_limit = ini_get( 'memory_limit' );
			$max_execution_time = ini_get( 'max_execution_time' );
			
			// Localize script for AJAX
			wp_localize_script( $this->plugin_name, 'mc_functionality_ajax', array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'mc_functionality_editor_nonce' ),
				'theme'    => $selected_theme,
				'environment' => array(
					'memory_limit' => $memory_limit,
					'max_execution_time' => $max_execution_time,
				),
			) );
		}
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
		add_action( 'wp_ajax_mc_functionality_toggle_snippet', array( $this, 'ajax_toggle_snippet' ) );
	}

	/**
	 * Register plugin settings.
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting( 'mc_functionality_settings', 'mc_codemirror_theme', array(
			'type' => 'string',
			'default' => 'default',
			'sanitize_callback' => array( $this, 'sanitize_theme_setting' )
		) );
		
		// Add Site Health test for snippet environment
		add_filter( 'site_status_tests', array( $this, 'add_site_health_test' ) );
	}

	/**
	 * Sanitize the CodeMirror theme setting
	 *
	 * @param string $theme The theme value to sanitize
	 * @return string The sanitized theme value
	 */
	public function sanitize_theme_setting( $theme ) {
		$allowed_themes = array( 'default', 'darcula', 'eclipse', 'material', 'mdn-like', 'monokai' );
		
		if ( in_array( $theme, $allowed_themes, true ) ) {
			return $theme;
		}
		
		return 'default';
	}

	/**
	 * Add Site Health test for MC Functionality snippet environment
	 *
	 * @param array $tests Array of Site Health tests
	 * @return array Modified array of tests
	 */
	public function add_site_health_test( $tests ) {
		$tests['direct']['mc_functionality_snippets'] = array(
			'label' => __( 'MC Functionality Snippet Environment' ),
			'test' => array( $this, 'test_snippet_environment' ),
		);
		
		return $tests;
	}

	/**
	 * Test the snippet environment for Site Health
	 *
	 * @return array Test result data
	 */
	public function test_snippet_environment() {
		$memory_limit = ini_get( 'memory_limit' );
		$max_execution_time = ini_get( 'max_execution_time' );
		$memory_limit_bytes = $this->parse_memory_limit( $memory_limit );
		
		$result = array(
			'label' => __( 'MC Functionality snippet environment is properly configured' ),
			'status' => 'good',
			'badge' => array(
				'label' => __( 'MC Functionality' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				__( 'Memory limit: %s, Max execution time: %s seconds' ),
				$memory_limit,
				$max_execution_time
			),
			'actions' => '',
			'test' => 'mc_functionality_snippets',
		);
		
		// Check if memory limit is reasonable for snippet execution
		if ( $memory_limit_bytes < 64 * 1024 * 1024 ) { // Less than 64MB
			$result['status'] = 'recommended';
			$result['label'] = __( 'MC Functionality may need higher memory limit' );
			$result['description'] .= ' ' . __( 'Consider increasing memory limit for better snippet performance.' );
		}
		
		// Check if execution time is reasonable
		if ( $max_execution_time > 0 && $max_execution_time < 30 ) { // Less than 30 seconds
			$result['status'] = 'recommended';
			$result['label'] = __( 'MC Functionality may need higher execution time limit' );
			$result['description'] .= ' ' . __( 'Consider increasing max_execution_time for complex snippets.' );
		}
		
		return $result;
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
		
		// Check if this is a disabled file
		$is_disabled = false;
		$base_filename = $filename;
		
		if ( strpos( $filename, '.disabled' ) !== false ) {
			$is_disabled = true;
			$base_filename = str_replace( '.disabled', '', $filename );
		}
		
		// Validate filename (allow both .php and .php.disabled)
		if ( empty( $filename ) || ! preg_match( '/^[a-zA-Z0-9\-_\.]+\.php(\.disabled)?$/', $filename ) ) {
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

		// Parse metadata from content
		$metadata = $this->parse_snippet_metadata( $content );

		// Debug logging
		error_log( 'MC Functionality: AJAX response metadata - Run Context: ' . $metadata['run_context'] . ', Priority: ' . $metadata['priority'] );

		wp_send_json_success( array(
			'content' => $content,
			'filename' => $filename,
			'run_context' => $metadata['run_context'],
			'priority' => $metadata['priority'],
			'is_disabled' => $is_disabled,
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
		$run_context = sanitize_text_field( $_POST['run_context'] );
		$priority = intval( $_POST['priority'] );
		
		// Check if this is a disabled file
		$is_disabled = false;
		$base_filename = $filename;
		
		if ( strpos( $filename, '.disabled' ) !== false ) {
			$is_disabled = true;
			$base_filename = str_replace( '.disabled', '', $filename );
		}
		
		// Validate filename (allow both .php and .php.disabled)
		if ( empty( $filename ) || ! preg_match( '/^[a-zA-Z0-9\-_\.]+\.php(\.disabled)?$/', $filename ) ) {
			wp_send_json_error( 'Invalid filename' );
		}

		// Validate run_context
		$valid_contexts = array( 'everywhere', 'admin-only', 'frontend-only', 'once' );
		if ( ! in_array( $run_context, $valid_contexts ) ) {
			wp_send_json_error( 'Invalid run context' );
		}

		// Validate priority
		if ( $priority < 1 || $priority > 100 ) {
			wp_send_json_error( 'Invalid priority value' );
		}

		// Determine file paths
		$current_file_path = MC_FUNCTIONALITY_SNIPPETS_DIR . '/' . $filename;
		$enabled_file_path = MC_FUNCTIONALITY_SNIPPETS_DIR . '/' . $base_filename;
		$disabled_file_path = MC_FUNCTIONALITY_SNIPPETS_DIR . '/' . $base_filename . '.disabled';

		// Security check: ensure file is within snippets directory
		$real_file_path = realpath( dirname( $current_file_path ) );
		$real_snippets_dir = realpath( MC_FUNCTIONALITY_SNIPPETS_DIR );
		
		if ( $real_file_path === false || $real_snippets_dir === false || strpos( $real_file_path, $real_snippets_dir ) !== 0 ) {
			wp_send_json_error( 'File not found or access denied' );
		}

		// Update content with metadata
		$updated_content = $this->update_snippet_metadata( $content, $run_context, $priority );
		
		// Debug logging
		error_log( 'MC Functionality: Original content length: ' . strlen( $content ) );
		error_log( 'MC Functionality: Updated content length: ' . strlen( $updated_content ) );
		error_log( 'MC Functionality: Run Context: ' . $run_context . ', Priority: ' . $priority );

		// Validate content BEFORE writing to file
		error_log( 'MC Functionality: Starting validation...' );
		$validation_result = $this->validate_snippet_content( $updated_content );
		error_log( 'MC Functionality: Validation result: ' . ( is_wp_error( $validation_result ) ? 'ERROR: ' . $validation_result->get_error_message() : 'SUCCESS' ) );
		
		if ( is_wp_error( $validation_result ) ) {
			error_log( 'MC Functionality: Sending JSON error response: ' . $validation_result->get_error_message() );
			wp_send_json_error( array(
				'message' => $validation_result->get_error_message(),
				'notice_type' => 'error'
			) );
		}
		
		// Check for validation warnings
		$validation_warning = null;
		if ( is_array( $validation_result ) && isset( $validation_result['warning'] ) ) {
			$validation_warning = $validation_result['warning'];
		}

		// Write file content (only after validation passes)
		$result = file_put_contents( $current_file_path, $updated_content );
		if ( $result === false ) {
			wp_send_json_error( 'Unable to save file' );
		}

		// If this was a disabled file and validation passed, enable it
		if ( $is_disabled ) {
			$enable_result = rename( $current_file_path, $enabled_file_path );
			if ( $enable_result ) {
				$message = 'Snippet saved and enabled successfully!';
				$filename = $base_filename; // Return the enabled filename
			} else {
				$message = 'Snippet saved but could not be enabled. Please try again.';
			}
		} else {
			$message = 'Snippet saved successfully!';
		}

		$response_data = array(
			'message' => $message,
			'notice_type' => 'success',
			'filename' => $filename,
		);
		
		// Add warning if validation had issues
		if ( $validation_warning ) {
			$response_data['warning'] = $validation_warning;
		}
		
		wp_send_json_success( $response_data );
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
		
		// Check if file already exists (both enabled and disabled versions)
		$enabled_path = MC_FUNCTIONALITY_SNIPPETS_DIR . '/' . $filename;
		$disabled_path = MC_FUNCTIONALITY_SNIPPETS_DIR . '/' . $filename . '.disabled';
		
		if ( file_exists( $enabled_path ) || file_exists( $disabled_path ) ) {
			wp_send_json_error( 'A snippet with this name already exists' );
		}

		// Generate initial content
		$content = $this->generate_snippet_template( $name, $description );

		// Write file content as .disabled (will be enabled after first successful save)
		$result = file_put_contents( $disabled_path, $content );
		if ( $result === false ) {
			wp_send_json_error( 'Unable to create file' );
		}

		$response_data = array(
			'message' => 'Snippet created successfully! (File is disabled until first save)',
			'notice_type' => 'success',
			'filename' => $filename . '.disabled',
			'content' => $content,
			'is_disabled' => true,
		);
		
		wp_send_json_success( $response_data );
	}

	/**
	 * AJAX handler to toggle snippet enable/disable status.
	 *
	 * @since    1.0.0
	 */
	public function ajax_toggle_snippet() {
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

		// Initialize snippet loader
		$snippet_loader = new Mc_Functionality_Snippet_Loader();
		
		// Check current status
		$is_enabled = $snippet_loader->is_snippet_enabled( $filename );
		
		// Toggle status
		if ( $is_enabled ) {
			$result = $snippet_loader->disable_snippet( $filename );
			$new_status = 'disabled';
			$message = 'Snippet disabled successfully!';
		} else {
			$result = $snippet_loader->enable_snippet( $filename );
			$new_status = 'enabled';
			$message = 'Snippet enabled successfully!';
		}

		if ( $result ) {
			wp_send_json_success( array(
				'message' => $message,
				'notice_type' => 'success',
				'status' => $new_status,
			) );
		} else {
			wp_send_json_error( 'Unable to toggle snippet status' );
		}
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
	 * Generate snippet template with metadata.
	 *
	 * @since    1.0.0
	 * @param    string $name        The snippet name.
	 * @param    string $description The snippet description.
	 * @param    string $run_context The run context (default: everywhere).
	 * @param    int    $priority    The priority (default: 10).
	 * @return   string The template content.
	 */
	private function generate_snippet_template( $name, $description, $run_context = 'everywhere', $priority = 10 ) {
		$template = "<?php\n";
		$template .= "/**\n";
		$template .= " * " . esc_html( $name ) . "\n";
		$template .= " * \n";
		$template .= " * " . esc_html( $description ) . "\n";
		$template .= " * \n";
		$template .= " * Run-Context: " . esc_html( $run_context ) . "\n";
		$template .= " * Priority: " . intval( $priority ) . "\n";
		$template .= " * \n";
		$template .= " * @package Mc_Functionality\n";
		$template .= " */\n\n";
		$template .= "// Your code here\n";
		return $template;
	}

	/**
	 * Parse metadata from snippet file content.
	 *
	 * @since    1.0.0
	 * @param    string $content The file content to parse.
	 * @return   array  Array with 'run_context' and 'priority' values.
	 */
	private function parse_snippet_metadata( $content ) {
		$metadata = array(
			'run_context' => 'everywhere',
			'priority' => 10
		);
		
		// Debug: Log the content being parsed
		error_log( 'MC Functionality: Parsing content: ' . substr( $content, 0, 500 ) );
		
		// Look for Run-Context in comment block (more flexible pattern)
		if ( preg_match( '/Run-Context:\s*([a-zA-Z0-9\-]+)/', $content, $matches ) ) {
			$metadata['run_context'] = $matches[1];
			error_log( 'MC Functionality: Found Run-Context: ' . $matches[1] );
		} elseif ( preg_match( '/run_context:\s*([a-zA-Z0-9\-]+)/', $content, $matches ) ) {
			$metadata['run_context'] = $matches[1];
			error_log( 'MC Functionality: Found run_context: ' . $matches[1] );
		} else {
			error_log( 'MC Functionality: No Run-Context found, using default: everywhere' );
		}
		
		// Look for Priority in comment block (more flexible pattern)
		if ( preg_match( '/Priority:\s*(\d+)/', $content, $matches ) ) {
			$metadata['priority'] = intval( $matches[1] );
			error_log( 'MC Functionality: Found Priority: ' . $matches[1] );
		} elseif ( preg_match( '/priority:\s*(\d+)/', $content, $matches ) ) {
			$metadata['priority'] = intval( $matches[1] );
			error_log( 'MC Functionality: Found priority: ' . $matches[1] );
		} else {
			error_log( 'MC Functionality: No Priority found, using default: 10' );
		}
		
		// Debug logging
		error_log( 'MC Functionality: Final parsed metadata - Run Context: ' . $metadata['run_context'] . ', Priority: ' . $metadata['priority'] );
		
		return $metadata;
	}

	/**
	 * Generate metadata comment block.
	 *
	 * @since    1.0.0
	 * @param    string $run_context The run context value.
	 * @param    int    $priority    The priority value.
	 * @return   string The formatted comment block.
	 */
	private function generate_metadata_comment( $run_context, $priority ) {
		$comment = "/**\n";
		$comment .= " * Run-Context: " . esc_html( $run_context ) . "\n";
		$comment .= " * Priority: " . intval( $priority ) . "\n";
		$comment .= " */\n";
		return $comment;
	}

	/**
	 * Update snippet content with metadata.
	 *
	 * @since    1.0.0
	 * @param    string $content     The original content.
	 * @param    string $run_context The run context value.
	 * @param    int    $priority    The priority value.
	 * @return   string The updated content with metadata.
	 */
	private function update_snippet_metadata( $content, $run_context, $priority ) {
		// Generate new metadata comment
		$metadata_comment = $this->generate_metadata_comment( $run_context, $priority );
		
		// Check if content already has our metadata format
		if ( preg_match( '/\/\*\*\s*\n\s*\* Run-Context: [^\n]*\n\s*\* Priority: [^\n]*\n\s*\*\/\s*\n/', $content ) ) {
			// Replace existing metadata with new metadata
			$content = preg_replace( '/\/\*\*\s*\n\s*\* Run-Context: [^\n]*\n\s*\* Priority: [^\n]*\n\s*\*\/\s*\n/', $metadata_comment, $content );
		} else {
			// Add metadata at the top of the file (after <?php if it exists)
			if ( strpos( $content, '<?php' ) === 0 ) {
				$content = '<?php' . "\n" . $metadata_comment . substr( $content, 5 );
			} else {
				$content = $metadata_comment . $content;
			}
		}
		
		return $content;
	}

}
