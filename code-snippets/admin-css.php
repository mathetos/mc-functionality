<?php
/**
 * Run-Context: admin-only
 * Priority: 10
 */

// Your code here

add_action('admin_head', function () {

    echo '<style>
        /* Your custom admin styles */
        body.wp-admin {
            background-color: #f9f9f9;
        }

        #adminmenu .wp-has-current-submenu > a {
            background-color: #007cba;
            color: #fff;
        }

        input[type="text"], textarea {
            border: 2px solid #007cba;
        }
		ul#adminmenu li .separator {
			height: 3px;
			background: rgba(255,255,255,0.25);
			max-width: 80%;
			margin: 10px auto;
			border-radius: 6px;
		}
		ul#adminmenu li.menu-editor-link a {
			text-decoration: underline;
			font-style: italic;
			font-size: 90%;
		}
    </style>';
}, 20); // Priority 20 ensures most styles are already loaded before injecting
