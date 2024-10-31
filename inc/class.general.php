<?php

if ( ! class_exists( 'Plugin_Compatibility_Info_General' ) ) {

    /**
     * The general/main class for the plugin
     * 
     * @since 1.0.0
     */
    class Plugin_Compatibility_Info_General {

        /**
         * Add filters/actions
         * 
         * @since 1.0.0
         */
        function __construct() {

            // translation
            add_action( 'init', array( $this, 'load_textdomain' ) );

            // scripts
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            // register new column
            add_filter( 'manage_plugins_columns', array( $this, 'add_column' ) );

            // display new column content
            add_action( 'manage_plugins_custom_column', array( $this, 'display_column' ), 10, 3 );

            // ajax hook for version check after plugin update
            add_action( 'wp_ajax_plugin_compatibility_info_get_version', array( $this, 'column_update_ajax' ) );

        }

        /**
         * Translations
         * 
         * @since 1.0.0
         */
        function load_textdomain() {

            load_plugin_textdomain( 'plugin-compatibility-info', false, PLUGIN_COMPATIBILITY_INFO_DIR_NAME . '/languages' ); 

        }

        /**
         * Enqueue scripts and styles
         * 
         * @since 1.0.0
         */
        function enqueue_scripts( $hook ) {

            // load only on the plugins page
		    if ( $hook == 'plugins.php' ) {
                
                // js
                wp_enqueue_script( 'plugin-compatibility-info-js', PLUGIN_COMPATIBILITY_INFO_URL . 'js/plugin-compatibility-info-admin.js', array( 'jquery' ), PLUGIN_COMPATIBILITY_INFO_VERSION, true );

                // css
                wp_enqueue_style( 'plugin-compatibility-info-css', PLUGIN_COMPATIBILITY_INFO_URL . 'css/plugin-compatibility-info-admin.css', array(), PLUGIN_COMPATIBILITY_INFO_VERSION, 'all' );

            }

        }

        /**
         * Add column
         * 
         * @since 1.0.0
         */
        function add_column( $columns ) {

            $columns['plugin-compatibility-info'] = esc_html__( 'Tested up to', 'plugin-compatibility-info' );
		    return $columns;

        }

        /**
         * Display column
         * 
         * @since 1.0.0
         */
        function display_column( $column, $plugin_file, $plugin_data ) {

            if ( $column == 'plugin-compatibility-info' ) {
                
                // plugin slug
                $slug = '';
                if ( ! empty( $plugin_data['slug'] ) ) {
                    $slug = $plugin_data['slug'];
                }

                // get versions
                $current_version = get_bloginfo( 'version' );
                $tested_version = $this->get_tested_version( $plugin_file );

                // output version
                if ( $tested_version ) {
                    $element_class = 'plugin-compatibility-info-level-' . $this->get_version_compatibility( $current_version, $tested_version );
                    echo '<span data-plugin-compatibility-info-slug="' . esc_attr( $slug ) . '" class="' . esc_attr( $element_class ) . '">' . esc_html( $tested_version ) . '</span>';
                } else {
                    esc_html_e( 'Not defined', 'plugin-compatibility-info' );
                }

            }

        }

        /**
         * Update column content on plugin update
         *
         * @since 1.0.0
         */
        public function column_update_ajax() {

            // hold the response ( fail by default )
            $response = array();
            $reponse['success'] = 'false';

            // make sure the user is allowed to get here
            if ( current_user_can( 'update_plugins' ) ) {

                // make sure the info is available
                if ( ! empty( $_POST['plugin_compatibility_info_plugin_file'] ) ) {

                    // get the data
                    $plugin_file = $_POST['plugin_compatibility_info_plugin_file'];
                    $current_version = get_bloginfo( 'version' );
                    $tested_version = $this->get_tested_version( $plugin_file );
                    $slug = dirname( plugin_basename( $plugin_file ) );

                    // output version
                    if ( $tested_version ) {
                        $element_class = 'plugin-compatibility-info-level-' . $this->get_version_compatibility( $current_version, $tested_version );
                        $output = '<span data-plugin-compatibility-info-slug="' . esc_attr( $slug ) . '" class="' . esc_attr( $element_class ) . '">' . esc_html( $tested_version ) . '</span>';
                    } else {
                        $output = esc_html__( 'Not defined', 'plugin-compatibility-info' );
                    }

                    $response['success'] = 'true';
                    $response['output'] = $output;

                }

            }

            // encode response
            $response_json = json_encode( $response );

            // send the response
            header( "Content-Type: application/json" );
            echo $response_json;

            // hasta la vista baby
            wp_die();

        }

        /**
         * Get tested up to value
         *
         * Parses the readme.txt of the plugin and gets the "Tested up to" value.
         *
         * @since 1.0.0
         */
        public function get_tested_version( $plugin_file ) {

            // get file path
            $file = trailingslashit( WP_PLUGIN_DIR ) . plugin_dir_path( $plugin_file ) . 'readme.txt';

            // file found, let's get the version
            if ( file_exists( $file ) ) {

                // get the file contents and turn it into an array ( item per line )
                $readme = file_get_contents( $file );
                $readme = nl2br( esc_html( $readme ) );
                $readme = explode( '<br />', $readme );			

                // go through the lines to find the "tested up to" and if found return the version
                foreach ( $readme as $readme_item ) {
                    if ( preg_match('|Tested up to:(.*)|i', $readme_item, $info ) ) {
                        return trim( $info[1] );
                    }
                }

                // "tested up to" not found, weird
                return false;

            // file not found :(
            } else {
                return false;
            }

        }

        /**
         * Get compatibility level
         *
         * Returns 1-4 ( 1 being all good, 4 being problematic )
         *
         * @since 1.0.0
         */
        public function get_version_compatibility( $a, $b ) {

            // LEVEL 1: same version, all ok
            if ( $a == $b ) {
                return 1;
            }

            // split version numbers
            $a_parts = explode( '.', $a );
            $b_parts = explode( '.', $b );

            // if z ( x.y.z ) not set, default to 0
            if ( empty( $a_parts[2] ) ) {
                $a_parts[2] = 0;
            }
            if ( empty( $b_parts[2] ) ) {
                $b_parts[2] = 0;
            }

            // LEVEL 4: major release difference
            if ( $a_parts[0] > $b_parts[0] ) {
                return 4;
            }

            // LEVEL 4: major release difference
            if ( $a_parts[1] > $b_parts[1] ) {
                return 4;
            }

            // LEVEL 3: 4+ diff in patch updates
            if ( ( $a_parts[2] - $b_parts[2] ) > 3 ) {
                return 3;
            }

            // LEVEL 2: 1-4 diff in patch updates
            if ( ( $a_parts[2] - $b_parts[2] ) <= 3 ) {
                return 2;
            }

            // default
            return 2;

        }

    }

}

new Plugin_Compatibility_Info_General();