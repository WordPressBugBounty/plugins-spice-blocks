<?php

/**
* Plugin Name:          Spice Blocks
* Plugin URI:           https://spiceblocks.com/
* Description:          Spice Blocks Plugin is a block plugin that is compatible with all WordPress themes. In plugin block controls are given, that's help to develop a beautiful WordPress theme.
* Version:              2.0.7.7
* Requires at least:    5.3
* Requires PHP:         5.2
* Tested up to:         6.9
* Author:               Spicethemes
* Author URI:           https://spicethemes.com
* License:              GPLv2 or later
* License URI:          https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:          spice-blocks
* Domain Path:          /languages
*/
if ( !function_exists( 'sb_fs' ) ) {
    // Create a helper function for easy SDK access.
    function sb_fs() {
        global $sb_fs;
        if ( !isset( $sb_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $sb_fs = fs_dynamic_init( array(
                'id'             => '11560',
                'slug'           => 'spice-blocks',
                'premium_slug'   => 'spice-blocks-pro',
                'type'           => 'plugin',
                'public_key'     => 'pk_cc4dac906a3ad63fe8d670b7c85eb',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                    'days'               => 14,
                    'is_require_payment' => true,
                ),
                'menu'           => array(
                    'slug'       => 'spice-blocks',
                    'first-path' => 'admin.php?page=spice-blocks',
                ),
                'is_live'        => true,
            ) );
        }
        return $sb_fs;
    }

    // Init Freemius.
    sb_fs();
    // Signal that SDK was initiated.
    do_action( 'sb_fs_loaded' );
}
error_reporting( 0 );
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
define( 'SPICE_BLOCKS_VERSION', 'self::VERSION' );
define( 'SPICE_BLOCKS_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'SPICE_BLOCKS_PLUGIN_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
define( 'SPICE_BLOCKS_PLUGIN_UPLOAD', trailingslashit( wp_upload_dir()['basedir'] ) );
require_once SPICE_BLOCKS_PLUGIN_PATH . '/inc/block-import.php';
if ( sb_fs()->can_use_premium_code() ) {
    require_once SPICE_BLOCKS_PLUGIN_PATH . '/pro-plugin.php';
}
if ( !sb_fs()->can_use_premium_code() ) {
    require_once SPICE_BLOCKS_PLUGIN_PATH . '/free-plugin.php';
}