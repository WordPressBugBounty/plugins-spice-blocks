<?php

class Widget_Data {

    static $import_page;

    /**
     * initialize
     */
    public static function init() {
        if( !is_admin() )
            return;

        add_action( 'admin_menu', array( __CLASS__, 'add_admin_menus' ) );
        add_action( 'wp_ajax_import_widget_data', array( __CLASS__, 'ajax_import_widget_data' ) );
    }

    private static function clear_widgets() {
        $sidebars = wp_get_sidebars_widgets();
        $inactive = isset($sidebars['wp_inactive_widgets']) ? $sidebars['wp_inactive_widgets'] : array();

        unset($sidebars['wp_inactive_widgets']);

        foreach ( $sidebars as $sidebar => $widgets ) {
            $inactive = array_merge($inactive, $widgets);
            $sidebars[$sidebar] = array();
        }

        $sidebars['wp_inactive_widgets'] = $inactive;
        wp_set_sidebars_widgets( $sidebars );
    }

    /**
     * Register admin pages
     */
    public static function add_admin_menus() {
        //import
        add_management_page( 'Widget Settings Import', 'Widget Settings Import', 'manage_options', 'widget-settings-import', array( __CLASS__, 'import_settings_page' ) );
    }

    
    
    /**
     * HTML for import admin page
     * @return type
     */
    public static function import_settings_page() {
        $nonce = isset( $_POST['widget_upload_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['widget_upload_nonce'] ) ) : '';

        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'widget_upload_action' ) ) {
            return new WP_Error( 'invalid_nonce', __( 'Security check failed.', 'spice-blocks' ) );
        }
        ?>
        <div class="widget-data import-widget-settings">
            <div class="wrap">
                <h2>Widget Setting Import</h2>
                <?php //$_FILES['widget-upload-file']=SPICE_BLOCKS_PLUGIN_URL.'/demo.json';
                if ( isset( $_FILES['widget-upload-file'] ) ) : ?>
                    <div id="notifier" style="display: none;"></div>
                    <div class="import-wrapper">
                        <p>
                            <a class="button select-all">Select All Active Widgets</a>
                            <a class="button unselect-all">Un-Select All Active Widgets</a>
                        </p>
                        <form action="" id="import-widget-data" method="post">
                            <?php wp_nonce_field('import_widget_data', '_wpnonce');

                                //$json = self::get_widget_settings_json();

$json[0] = file_get_contents(SPICE_BLOCKS_PLUGIN_PATH.'/demo.json');
                                if( !$json || !( $json_data = json_decode( $json[0], true ) ) )
                                    return;
                                //echo 'dev010'.$json[0];
                                $json_file = SPICE_BLOCKS_PLUGIN_PATH.'/demo.json';
                                
                                
                            ?>
                            <input type="hidden" name="import_file" value="<?php echo esc_attr( $json_file ); ?>"/>
                            <input type="hidden" name="action" value="import_widget_data"/>
                            <div class="title">
                                <p class="widget-selection-error">Please select a widget to continue.</p>
                                <h3>Sidebars</h3>
                                <div class="clear"></div>
                            </div>
                            <div class="sidebars">
                                <?php
                                if ( isset( $json_data[0] ) ) :
                                    foreach ( self::order_sidebar_widgets( $json_data[0] ) as $sidebar_name => $widget_list ) :
                                        if ( count( $widget_list ) == 0 ) {
                                            continue;
                                        }
                                        $sidebar_info = self::get_sidebar_info( $sidebar_name );
                                        if ( $sidebar_info ) : ?>
                                            <div class="sidebar">
                                                <h4><?php echo esc_html($sidebar_info['name']); ?></h4>

                                                <div class="widgets">
                                                    <?php
                                                    foreach ( $widget_list as $widget ) :
                                                        $widget_options = false;

                                                        $widget_type = trim( substr( $widget, 0, strrpos( $widget, '-' ) ) );
                                                        $widget_type_index = trim( substr( $widget, strrpos( $widget, '-' ) + 1 ) );
                                                        foreach ( $json_data[1] as $name => $option ) {
                                                            if ( $name == $widget_type ) {
                                                                $widget_type_options = $option;
                                                                break;
                                                            }
                                                        }
                                                        if ( !isset($widget_type_options) || !$widget_type_options )
                                                            continue;

                                                        $widget_title = isset( $widget_type_options[$widget_type_index]['title'] ) ? $widget_type_options[$widget_type_index]['title'] : '';
                                                        $widget_options = $widget_type_options[$widget_type_index];
                                                        ?>
                                                        <div class="import-form-row">
                                                            <input class="<?php echo ($sidebar_name == 'wp_inactive_widgets') ? 'inactive' : 'active'; ?> widget-checkbox" type="checkbox" name="<?php echo esc_attr( printf('widgets[%s][%d]', $widget_type, $widget_type_index) ); ?>" id="<?php echo esc_attr( 'meta_' . $widget ); ?>" />
                                                            <label for="meta_<?php echo esc_attr( 'meta_' . $widget ); ?>">&nbsp;
                                                            <?php
                                                                echo esc_html( ucfirst( $widget_type ) );
                                                                if ( ! empty( $widget_title ) ) {
                                                                    echo ' - ' . esc_html( $widget_title );
                                                                }
                                                            ?>
                                                            </label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div> <!-- end widgets -->
                                            </div> <!-- end sidebar -->
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div> <!-- end sidebars -->
                            <p>
                                <input type="checkbox" name="clear_current" id="clear-current" checked=checked value="true" />
                                <label for="clear-current">Clear Current Widgets Before Import</label><br/>
                                <span class="description">All active widgets will be moved to inactive</span>
                            </p>
                            <input class="button-bottom button-primary" type="submit" name="import-widgets" id="import-widgets" value="Import Widget Settings" />
                        </form>
                    </div>
                <?php else : ?>
                    <form action="" id="upload-widget-data" method="post" enctype="multipart/form-data">
                        <p>Select the file that contains widget settings</p>
                        <p>
                            <input type="text" disabled="disabled" class="file-name regular-text" />
                            <a id="upload-button" class="button upload-button">Select a file</a>
                            <input type="file" name="widget-upload-file" id="widget-upload-file" size="40" style="display:none;" />
                        </p>
                        <input type="submit" name="button-upload-submit" id="button-upload-submit" class="button" value="Show Widget Settings" />
                    </form>
                <?php endif; ?>
            </div> <!-- end wrap -->
        </div> <!-- end import-widget-settings -->
        <?php
    }

    
    /**
     * Import widgets
     * @param array $import_array
     */
    public static function parse_import_data( $import_array ) {
        $sidebars_data = $import_array[0];
        $widget_data = $import_array[1];
        $current_sidebars = get_option( 'sidebars_widgets' );
        $new_widgets = array( );

        foreach ( $sidebars_data as $import_sidebar => $import_widgets ) :

            foreach ( $import_widgets as $import_widget ) :
                //if the sidebar exists
                if ( isset( $current_sidebars[$import_sidebar] ) ) :
                    $title = trim( substr( $import_widget, 0, strrpos( $import_widget, '-' ) ) );
                    $index = trim( substr( $import_widget, strrpos( $import_widget, '-' ) + 1 ) );
                    $current_widget_data = get_option( 'widget_' . $title );
                    $new_widget_name = self::get_new_widget_name( $title, $index );
                    $new_index = trim( substr( $new_widget_name, strrpos( $new_widget_name, '-' ) + 1 ) );

                    if ( !empty( $new_widgets[ $title ] ) && is_array( $new_widgets[$title] ) ) {
                        while ( array_key_exists( $new_index, $new_widgets[$title] ) ) {
                            $new_index++;
                        }
                    }
                    $current_sidebars[$import_sidebar][] = $title . '-' . $new_index;
                    if ( array_key_exists( $title, $new_widgets ) ) {
                        $new_widgets[$title][$new_index] = $widget_data[$title][$index];
                        $multiwidget = $new_widgets[$title]['_multiwidget'];
                        unset( $new_widgets[$title]['_multiwidget'] );
                        $new_widgets[$title]['_multiwidget'] = $multiwidget;
                    } else {
                        $current_widget_data[$new_index] = $widget_data[$title][$index];
                        $current_multiwidget = $current_widget_data['_multiwidget'];
                        $new_multiwidget = isset($widget_data[$title]['_multiwidget']) ? $widget_data[$title]['_multiwidget'] : false;
                        $multiwidget = ($current_multiwidget != $new_multiwidget) ? $current_multiwidget : 1;
                        unset( $current_widget_data['_multiwidget'] );
                        $current_widget_data['_multiwidget'] = $multiwidget;
                        $new_widgets[$title] = $current_widget_data;
                    }

                endif;
            endforeach;
        endforeach;

        if ( isset( $new_widgets ) && isset( $current_sidebars ) ) {
            update_option( 'sidebars_widgets', $current_sidebars );

            foreach ( $new_widgets as $title => $content ) {
                $content = apply_filters( 'widget_data_import', $content, $title );
                update_option( 'widget_' . $title, $content );
            }

            return true;
        }

        return false;
    }


    /**
     * Parse JSON import file and load
     */
   public static function ajax_import_widget_data() {
    // Verify nonce for security
    check_ajax_referer( 'spice_import_widget_nonce', 'security' );

    $response = array(
        'what'   => 'widget_import_export',
        'action' => 'import_submit'
    );

    // Sanitize inputs
    $widgets = array();
    if ( isset( $_POST['widgets'] ) && is_array( $_POST['widgets'] ) ) {
        $widgets = array_map(
            function( $widget_group ) {
                return array_map( 'sanitize_text_field', (array) $widget_group );
            },
            (array) wp_unslash( $_POST['widgets'] ) // Sanitization immediately after unslash
        );
    }

    $import_file = isset( $_POST['import_file'] ) ? esc_url_raw( wp_unslash( $_POST['import_file'] ) ) : '';

    // Read and decode JSON
    $json_data    = file_get_contents( $import_file );
    $json_data    = json_decode( $json_data, true );
    $sidebar_data = $json_data[0];
    $widget_data  = $json_data[1];

    foreach ( $sidebar_data as $title => $sidebar ) {
        $count = count( $sidebar );
        for ( $i = 0; $i < $count; $i++ ) {
            $widget                = array();
            $widget['type']        = trim( substr( $sidebar[ $i ], 0, strrpos( $sidebar[ $i ], '-' ) ) );
            $widget['type-index']  = trim( substr( $sidebar[ $i ], strrpos( $sidebar[ $i ], '-' ) + 1 ) );

            if ( ! isset( $widgets[ $widget['type'] ][ $widget['type-index'] ] ) ) {
                unset( $sidebar_data[ $title ][ $i ] );
            }
        }
        $sidebar_data[ $title ] = array_values( $sidebar_data[ $title ] );
    }

    foreach ( $widgets as $widget_title => $widget_value ) {
        foreach ( $widget_value as $widget_key => $widget_value ) {
            $widgets[ $widget_title ][ $widget_key ] = $widget_data[ $widget_title ][ $widget_key ];
        }
    }

    $sidebar_data   = array( array_filter( $sidebar_data ), $widgets );
    $response['id'] = ( self::parse_import_data( $sidebar_data ) ) ? true : new WP_Error( 'widget_import_submit', 'Unknown Error' );

    $response = new WP_Ajax_Response( $response );
    $response->send();
}

    /**
     * Read uploaded JSON file
     * @return type
     */
    public static function get_widget_settings_json() {
        $widget_settings = self::upload_widget_settings_file();

        if( is_wp_error( $widget_settings ) || ! $widget_settings )
            return false;

        if( isset( $widget_settings['error'] ) )
            return new WP_Error( 'widget_import_upload_error', $widget_settings['error'] );

        $file_contents = file_get_contents( $widget_settings['file'] );
        return array( $file_contents, $widget_settings['file'] );
    }

    /**
     * Upload JSON file
     * @return boolean
     */
    public static function upload_widget_settings_file() {
        // Verify nonce before file upload
        $nonce = isset( $_POST['widget_upload_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['widget_upload_nonce'] ) ) : '';

        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'widget_upload_action' ) ) {
            return new WP_Error( 'invalid_nonce', __( 'Security check failed.', 'spice-blocks' ) );
        }

        wp_nonce_field( 'widget_upload_action', 'widget_upload_nonce' ); 
        if ( isset( $_FILES['widget-upload-file'] ) ) {
            add_filter( 'upload_mimes', array( __CLASS__, 'json_upload_mimes' ) );

            $upload = wp_handle_upload( $_FILES['widget-upload-file'], array( 'test_form' => false ) );

            remove_filter( 'upload_mimes', array( __CLASS__, 'json_upload_mimes' ) );
            return $upload;
        }

        return false;
    }

    /**
     *
     * @param string $widget_name
     * @param string $widget_index
     * @return string
     */
    public static function get_new_widget_name( $widget_name, $widget_index ) {
        $current_sidebars = get_option( 'sidebars_widgets' );
        $all_widget_array = array( );
        foreach ( $current_sidebars as $sidebar => $widgets ) {
            if ( !empty( $widgets ) && is_array( $widgets ) && $sidebar != 'wp_inactive_widgets' ) {
                foreach ( $widgets as $widget ) {
                    $all_widget_array[] = $widget;
                }
            }
        }
        while ( in_array( $widget_name . '-' . $widget_index, $all_widget_array ) ) {
            $widget_index++;
        }
        $new_widget_name = $widget_name . '-' . $widget_index;
        return $new_widget_name;
    }

    /**
     *
     * @global type $wp_registered_sidebars
     * @param type $sidebar_id
     * @return boolean
     */
    public static function get_sidebar_info( $sidebar_id ) {
        global $wp_registered_sidebars;

        //since wp_inactive_widget is only used in widgets.php
        if ( $sidebar_id == 'wp_inactive_widgets' )
            return array( 'name' => 'Inactive Widgets', 'id' => 'wp_inactive_widgets' );

        foreach ( $wp_registered_sidebars as $sidebar ) {
            if ( isset( $sidebar['id'] ) && $sidebar['id'] == $sidebar_id )
                return $sidebar;
        }

        return false;
    }

    /**
     *
     * @param array $sidebar_widgets
     * @return type
     */
    public static function order_sidebar_widgets( $sidebar_widgets ) {
        $inactive_widgets = false;

        //seperate inactive widget sidebar from other sidebars so it can be moved to the end of the array, if it exists
        if ( isset( $sidebar_widgets['wp_inactive_widgets'] ) ) {
            $inactive_widgets = $sidebar_widgets['wp_inactive_widgets'];
            unset( $sidebar_widgets['wp_inactive_widgets'] );
            $sidebar_widgets['wp_inactive_widgets'] = $inactive_widgets;
        }

        return $sidebar_widgets;
    }

    /**
     * Add mime type for JSON
     * @param array $existing_mimes
     * @return string
     */
    public static function json_upload_mimes( $existing_mimes = array( ) ) {
        $existing_mimes['json'] = 'application/json';
        return $existing_mimes;
    }

}
add_action( 'init', array( 'Widget_Data', 'init' ) );