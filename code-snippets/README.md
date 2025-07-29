# Code Snippets Directory

This directory contains PHP code snippets that are automatically loaded and executed by the MC Functionality plugin.

## How to Use

1. **Create PHP files** in this directory with a `.php` extension
2. **Write your WordPress code** directly in the PHP files
3. **Save the files** - they will be automatically loaded on the next page load

## File Naming

- Use descriptive names like `custom-shortcodes.php`, `theme-modifications.php`, etc.
- Files must have a `.php` extension
- Avoid using `index.php` as it's reserved for security

## Code Examples

### Adding Shortcodes
```php
<?php
// Add a custom shortcode
add_shortcode( 'my_shortcode', function( $atts ) {
    return '<div>My custom content</div>';
} );
```

### Adding Actions
```php
<?php
// Hook into WordPress actions
add_action( 'wp_footer', function() {
    echo '<!-- Custom footer content -->';
} );
```

### Adding Filters
```php
<?php
// Modify WordPress behavior
add_filter( 'the_title', function( $title ) {
    return 'Modified: ' . $title;
} );
```

## Security Features

- Files are validated before loading
- Only files within this directory are allowed
- Errors are logged when WP_DEBUG is enabled
- Files are loaded with `require_once` to prevent duplicates

## Loading Order

Snippets are loaded early in the WordPress lifecycle (priority 5 on `plugins_loaded`) to ensure they're available before themes and other plugins.

## Best Practices

1. **Use descriptive file names** that explain the snippet's purpose
2. **Include proper documentation** in your snippet files
3. **Test snippets thoroughly** before deploying
4. **Keep snippets focused** on a single purpose per file
5. **Use WordPress coding standards** for consistency