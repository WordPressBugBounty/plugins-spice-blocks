<?php 
//Options Page
add_action( 'admin_menu', 'spice_blocks_options_page',999 );
if(!function_exists('spice_blocks_options_page')){
    function spice_blocks_options_page() {
        add_menu_page(
            esc_html__( 'Spice Blocks', 'spice-blocks' ),
            esc_html__( 'Spice Blocks', 'spice-blocks' ),
            'manage_options',
            'spice-blocks',
            function() { require_once SPICE_BLOCKS_PLUGIN_PATH.'/admin/view-free.php'; },
            'dashicons-groups',
            20
        );
        add_submenu_page(
            'spice-blocks',
            esc_html__( 'Spice Blocks Panel', 'spice-blocks' ),
            esc_html__( 'Spice Blocks Panel', 'spice-blocks' ),
            'manage_options',
            'spice-blocks',
            function() { require_once SPICE_BLOCKS_PLUGIN_PATH.'/admin/view-free.php'; },
            1
        );
    }
}

//Enqueue Style & Script for admin
add_action('admin_enqueue_scripts','spice_blocks_style_script');
if(!function_exists('spice_blocks_style_script')){
    function spice_blocks_style_script(){
        $id = $GLOBALS['hook_suffix'];
        if('toplevel_page_spice-blocks'==$id){
            wp_enqueue_style( 'spice-blocks-about-css', SPICE_BLOCKS_PLUGIN_URL . 'admin/assets/css/about.css', array(),SPICE_BLOCKS_VERSION );
            wp_enqueue_style( 'spice-blocks-all-css', SPICE_BLOCKS_PLUGIN_URL .'assets/all.min.css', array(),'6.2.1');
        }
    }
}

//Main Class
final class Spice_Blocks{

    /** 
     * Construct Function
     */
    private  function __construct(){
        add_action('plugins_loaded',[$this,'init_plugin']);
    }

    
    /**
     * Singletone Instance
     */
    public static function init(){
        static $instance=false;
        if(!$instance){
            $instance=new self();
        }
        add_theme_support( 'menus' );
        return $instance;

    }

    /**
     * Plugin Init
     */
    public function init_plugin(){
       $this->enqueue_scripts();
    }

    /**
     * Enqueue Script
     */
    public function enqueue_scripts(){
        add_action('enqueue_block_editor_assets',[$this,'register_block_editor_assets']);
        add_action('enqueue_block_assets',[$this,'register_block_assets']);
        add_action('admin_enqueue_scripts',[$this,'register_admin_scripts']);
        add_action('init',[$this,'register_block']);        
        add_action('init',[$this,'spice_blocks_load_plugin_textdomain']);
    }

    public function register_block_editor_assets(){
        wp_enqueue_script(
            'spice-blocks-free',
            SPICE_BLOCKS_PLUGIN_URL.'/build/free-blocks.bundle.js',
            [
                'wp-blocks',
                'wp-editor',
                'wp-i18n',
                'wp-element',
                'wp-components',
                'wp-data',

            ],
            filemtime( plugin_dir_path( __FILE__ ) . 'build/free-blocks.bundle.js' ), // version
            true // in footer
        );
         wp_localize_script(
                'spice-blocks-free',
                'SpiceBlocksData',
                [
                    'is_pro' => false,
                ]
            );
    }

    public function register_block_assets(){
        if(isset($GLOBALS['hook_suffix'])){
            $hook_suffix = $GLOBALS['hook_suffix'];
            if($hook_suffix!='customize.php'){
                wp_enqueue_script('spice-jquery-js', SPICE_BLOCKS_PLUGIN_URL . 'assets/js/jquery.min.js',
                array('jquery'), // Dependencies (optional)
                filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/jquery.min.js' ), // Version (auto from file modified time)
                true // Load in footer
            );
            }
        }
         wp_enqueue_style(
            'spice-blocks-editor-css',
            SPICE_BLOCKS_PLUGIN_URL.'assets/css/editor.css',
            SPICE_BLOCKS_VERSION,
            'all'
        );
        wp_enqueue_style(
            'spice-blocks-animate',
            SPICE_BLOCKS_PLUGIN_URL.'assets/css/animate.css',
            array(),
            '3.5.1' 
        );  
         wp_enqueue_script(
             'spice-blocks-accordion',
             SPICE_BLOCKS_PLUGIN_URL.'assets/js/accordion.js',
            array('jquery'),
            SPICE_BLOCKS_VERSION,
            true
         );

         wp_enqueue_script(
            'spice-blocks-fontawesome',
            SPICE_BLOCKS_PLUGIN_URL.'assets/js/fontawesome.js',
            array(),
            filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/fontawesome.js' ),
            true
         );
        if( ! is_admin() ){
             wp_enqueue_style(
                'spice-blocks-img-compare',
                SPICE_BLOCKS_PLUGIN_URL.'assets/css/image-compare-viewer.css',
                array(),
                filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/image-compare-viewer.css' ),
                'all'
             );
             wp_enqueue_script(
                 'spice-blocks-image-compare-viewer',
                 SPICE_BLOCKS_PLUGIN_URL.'assets/js/image-compare-viewer-min.js',array('jquery'), wp_rand(), true
            );
             wp_enqueue_script(
                 'spice-blocks-image-compare-custom',
                 SPICE_BLOCKS_PLUGIN_URL.'assets/js/image-compare-custom.js',array('jquery'), wp_rand(), true
             );
        }
        wp_enqueue_style(
           'spice-blocks-style',
           SPICE_BLOCKS_PLUGIN_URL.'assets/css/style.css',
           [],
           SPICE_BLOCKS_VERSION,
           'all'
        );

        wp_enqueue_style(
           'spice-blocks-newsstyle',
           SPICE_BLOCKS_PLUGIN_URL.'assets/css/newsstyle.css',
           [],
           SPICE_BLOCKS_VERSION,
           'all'
        );        
        wp_enqueue_style('spice-blocks-magnific-css',SPICE_BLOCKS_PLUGIN_URL . 'assets/css/magnific-popup.css', array(), '1.1.0'); 
        wp_enqueue_script('animate-js', SPICE_BLOCKS_PLUGIN_URL . 'assets/js/animation/animate.js',
            array(), 
            filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/animation/animate.js' ), 
            true );
        wp_enqueue_script(
            'wow-js',
            SPICE_BLOCKS_PLUGIN_URL . 'assets/js/animation/wow.min.js',
            array(), // dependencies
            '0.1.9', // version (use your plugin version constant or filemtime)
            true // in footer
        );

        // Custom script
        wp_enqueue_script(
            'spice-blocks',
            SPICE_BLOCKS_PLUGIN_URL . 'assets/js/custom.js',
            array('jquery'), // example dependency
            SPICE_BLOCKS_VERSION,
            true
        );

        // Isotope
        wp_enqueue_script(
            'spice-blocks-isotope-js',
            SPICE_BLOCKS_PLUGIN_URL . 'assets/js/isotope.pkgd.min.js',
            array('jquery'),
            '3.0.6',
            true
        );
        
         wp_enqueue_script(
            'spice-video',
             SPICE_BLOCKS_PLUGIN_URL . 'assets/js/jquery.magnific-popup.min.js', array( 'jquery' ), '1.1.0', true
        );

        wp_enqueue_script('spice-blocks-imageloaded-js', SPICE_BLOCKS_PLUGIN_URL . 'assets/js/imagesloaded.pkgd.min.js', array( 'jquery' ), '5.0.0', true  );       
        
    }

    
    /**
     * Register Admin Scripts
     */   
    public function register_admin_scripts(){  

        wp_enqueue_script(
           'spice-blocks-editor-js',
           SPICE_BLOCKS_PLUGIN_URL.'assets/js/editor.js',
           array('jquery'),
           wp_rand(),
           true
        );
        wp_localize_script('spice-blocks-editor-js','plugin',['pluginpath' => SPICE_BLOCKS_PLUGIN_URL,'plugindir' => SPICE_BLOCKS_PLUGIN_UPLOAD, 'pva_ajax_url' => admin_url( 'admin-ajax.php' ) ]);
        wp_enqueue_script('spice-blocks-editor-js');
         
       
        wp_enqueue_style(
           'spice-blocks-fonticonpicker-material',
           SPICE_BLOCKS_PLUGIN_URL.'assets/css/fonticonpicker/fonticonpicker.material-theme.react.css',
           ['wp-edit-blocks'],
               '1.2.0',
               'all'
        );
        wp_enqueue_style(
           'spice-blocks-fonticonpicker-base',
           SPICE_BLOCKS_PLUGIN_URL.'assets/css/fonticonpicker/fonticonpicker.base-theme.react.css',
           ['wp-edit-blocks'],
               '1.2.0',
               'all'
        );
    }


   /**
    * Register Blocks
    */
    public function register_block(){      
        register_block_type('spice-blocks/spice-heading',[
           'editor_style'=>'spice-blocks-editor-css',
        ]);

        register_block_type('spice-blocks/spice-text-editor',[
           'editor_style'=>'spice-blocks-editor-css',
        ]);

        register_block_type('spice-blocks/spice-divider',[
           'editor_style'=>'spice-blocks-editor-css',
        ]);

        register_block_type('spice-blocks/spice-spacer',[
           'editor_style'=>'spice-blocks-editor-css',
        ]);

        register_block_type('spice-blocks/spice-icon',[
           'style'=> 'spice-blocks-style',
           'editor_style'=>'spice-blocks-editor',
        ]);

        register_block_type('spice-blocks/spice-section',[
           'style'=> 'wp-block-columns',
           'editor_style'=>'wp-block-columns-editor',
        ]); 

        register_block_type('spice-blocks/spice-image',[
           'style'=> 'spice-blocks-style',
           'editor_style'=>'wp-block-columns-editor',
        ]);

        register_block_type('spice-blocks/spice-blockquote',[
           'style'=> 'spice-blocks-style',
           'editor_style'=>'wp-block-columns-editor',
        ]);

        register_block_type('spice-blocks/spice-cta',[
           'style'=> 'spice-blocks-style',
           'editor_style'=>'wp-block-columns-editor',
        ]);

        register_block_type('spice-blocks/spice-timeline',[
            'style'=> 'spice-blocks-public',
            'editor_style'=>'spice-blocks-editor-css',
         ]);

         register_block_type('spice-blocks/spice-accordion',[
            'style'=> 'spice-blocks-public',
            'editor_style'=>'spice-blocks-editor-css',
         ]);

         register_block_type('spice-blocks/spice-icon-list',[
            'style'=> 'spice-blocks-public',
            'editor_style'=>'spice-blocks-editor-css',
         ]);

         register_block_type('spice-blocks/img-comparison',[
            'style'=> 'spice-blocks-public',
            'editor_style'=>'spice-blocks-editor-css',
         ]);
         
         register_block_type('spice-blocks/spice-gallery',[
            'style'=> 'spice-blocks-public',
            'editor_style'=>'spice-blocks-editor-css',
         ]);

         register_block_type('spice-blocks/spice-img-accordion',[
            'style'=> 'spice-blocks-public',
            'editor_style'=>'spice-blocks-editor-css',
         ]);

         register_block_type('spice-blocks/spice-progress-bar',[
            'style'=> 'spice-blocks-public',
            'editor_style'=>'spice-blocks-editor-css',
         ]);

         register_block_type('spice-blocks/spice-service-box',[
           'style'=> 'spice-blocks-style',
           'editor_style'=>'wp-block-columns-editor',
        ]);

        register_block_type('spice-blocks/spice-social-icon',[
           'style'=> 'spice-blocks-style',
           'editor_style'=>'spice-blocks-editor-css',
        ]);    

        register_block_type('spice-block/spice-socials',[
           'style'=> 'spice-blocks-style',
           'editor_style'=>'spice-blocks-editor-css',
        ]);        
     
        register_block_type('spice-blocks/spice-category-tab-posts', array(
                'style'=> 'spice-blocks-newsstyle',
                'editor_style'=>'spice-blocks-editor-css',
                'render_callback' => 'spice_blocks_render_category_tab_block',
                'attributes' => [
                        'uniqueid'=>[ 'type'=>'string'],
                        'categoryColors' =>['type'=>'object', 'default'=>[]],
                        'titleText'=> [ 'type'=> 'string', 'default'=> 'Trending Post'],
                        'postsPerPage'=> ['type'=> "number",'default'=> 6],
                        'excerptLength'=> ['type'=> "number",'default'=> 20],                        
                        'order'=> ['type'=> "string",'default'=> 'desc'],
                        'postgap'=> ['type'=> "number",'default'=> 20],
                        'columns'=> ['type'=> "number",'default'=> 3],
                        'selectedCategories'=>['type'=> "array",'default'=>[], 'items'=>['type' => 'number'],],
                        'textTab1'=> [ 'type'=> 'string', 'default'=> 'Popular'],
                        'textTab2'=> [ 'type'=> 'string', 'default'=> 'Recent'],
                        'displayAuthor'=> ['type'=> "boolean",  'default'=> true],
                        'displayDate'=> ['type'=> "boolean",  'default'=> true],
                        'displayComment'=> ['type'=> "boolean",  'default'=> true],
                        'displayCat'=> ['type'=> "boolean",  'default'=> true],
                        'displayContent'=> ['type'=> "boolean",  'default'=> true],
                        'bgColor'=>[ 'type'=>'string','default'=>''],
                        'bg1Color'=>[ 'type'=>'string','default'=>''],
                        'bggradientValue'=>[ 'type'=>'string','default'=>''],
                        'bg1gradientValue'=>[ 'type'=>'string','default'=>''],
                        'btnbggradientValue'=>[ 'type'=>'string','default'=>''],
                        'bggradientValue'=>[ 'type'=>'string','default'=>''],
                        'btnbggradientactiveValue'=>[ 'type'=>'string','default'=>''],
                        'catbggradientValue'=>[ 'type'=>'string','default'=>''],
                        'titleColor'=>[ 'type'=>'string','default'=>'#fff'],
                        'posttitleColor'=>[ 'type'=>'string','default'=>'#232323'],
                        'posttitlehColor'=>[ 'type'=>'string','default'=>'#679a9b'],
                        'postmetaColor'=>[ 'type'=>'string','default'=>'#727272'],
                        'postmetahColor'=>[ 'type'=>'string','default'=>'#679a9b'],
                        'catColor'=>[ 'type'=>'string','default'=>'#fff'],
                        'catbgColor'=>[ 'type'=>'string','default'=>''],
                        'postmetaiconColor'=>[ 'type'=>'string','default'=>'#679a9b'],
                        'postcontentColor'=>[ 'type'=>'string','default'=>'#727272'],
                        'btnColor'=>[ 'type'=>'string','default'=>'#6d6d6f'],
                        'btnbgColor'=>[ 'type'=>'string','default'=>''],
                        'btnColoractive'=>[ 'type'=>'string','default'=>'#fff'],
                        'btnbgColoractive'=>[ 'type'=>'string','default'=>''],
                        
                        'metamargins'=> ['type'=>'object', 'default'=> [ 'top'=> '15px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'11px'],],
                        'posttitlemargins'=> ['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'15px'],],
                        'contentmargins'=> ['type'=>'object', 'default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'28px'],],
                        'paddings'=>[ 'type'=>'object','default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                        'contentpaddings'=>[ 'type'=>'object','default'=> [ 'top'=> '0px', 'left'=> '0px', 'right'=> '0px', 'bottom'=>'0px'],],
                        'titlepaddings'=>[ 'type'=>'object','default'=> [ 'top'=> '7px', 'left'=> '16px', 'right'=> '16px', 'bottom'=>'7px'],],
                        'tabspaddings'=>[ 'type'=>'object','default'=> [ 'top'=> '4px', 'left'=> '6px', 'right'=> '6px', 'bottom'=>'4px'],],
                        'catpaddings'=>[ 'type'=>'object','default'=> [ 'top'=> '4px', 'left'=> '8px', 'right'=> '8px', 'bottom'=>'4px'],],
                        'border' =>['type' => 'object','default' => ['color' => '', 'style' => '', 'width' => '',],],
                        'bordertopradius'=>['type' => 'string','default' => '0px'],
                        'borderrightradius'=>['type' => 'string','default' => '0px'],
                        'borderbottomradius'=>['type' => 'string','default' => '0px'],
                        'borderleftradius'=>['type' => 'string','default' => '0px'],
                        'titleborder' =>['type' => 'object','default' => ['color' => '', 'style' => '', 'width' => '',],],
                        'titlebordertopradius'=>['type' => 'string','default' => '10px'],
                        'titleborderrightradius'=>['type' => 'string','default' => '10px'],
                        'titleborderbottomradius'=>['type' => 'string','default' => '0px'],
                        'titleborderleftradius'=>['type' => 'string','default' => '0px'],
                        'tabborder' =>['type' => 'object','default' => ['color' => '', 'style' => '', 'width' => '0px',],],
                        'tabbordertopradius'=>['type' => 'string','default' => '0px'],
                        'tabborderrightradius'=>['type' => 'string','default' => '0px'],
                        'tabborderbottomradius'=>['type' => 'string','default' => '0px'],
                        'tabborderleftradius'=>['type' => 'string','default' => '0px'],
                        'imgborder' =>['type' => 'object','default' => ['color' => '', 'style' => '', 'width' => '',],],
                        'imgbordertopradius'=>['type' => 'string','default' => '0px'],
                        'imgborderrightradius'=>['type' => 'string','default' => '0px'],
                        'imgborderbottomradius'=>['type' => 'string','default' => '0px'],
                        'imgborderleftradius'=>['type' => 'string','default' => '0px'],
                        'catborder' =>['type' => 'object','default' => ['color' => '', 'style' => '', 'width' => '',],],
                        'catbordertopradius'=>['type' => 'string','default' => '0px'],
                        'catborderrightradius'=>['type' => 'string','default' => '0px'],
                        'catborderbottomradius'=>['type' => 'string','default' => '0px'],
                        'catborderleftradius'=>['type' => 'string','default' => '0px'],
                        'postborder' =>['type' => 'object','default' => ['color' => '', 'style' => '', 'width' => '',],],
                        'postbordertopradius'=>['type' => 'string','default' => '0px'],
                        'postborderrightradius'=>['type' => 'string','default' => '0px'],
                        'postborderbottomradius'=>['type' => 'string','default' => '0px'],
                        'postborderleftradius'=>['type' => 'string','default' => '0px'],
                        'posthborder' =>['type' => 'object','default' => ['color' => '', 'style' => '', 'width' => '',],],
                        'posthbordertopradius'=>['type' => 'string','default' => '0px'],
                        'posthborderrightradius'=>['type' => 'string','default' => '0px'],
                        'posthborderbottomradius'=>['type' => 'string','default' => '0px'],
                        'posthborderleftradius'=>['type' => 'string','default' => '0px'],
                        
                        'fontfamily'=>[ 'type'=>'string','default'=>'Poppins'], 
                        'titlefontSize'=>[ 'type'=>'string','default'=>'20px'], 
                        'TitleFontWeight'=>[ 'type'=>'string','default'=>'700'], 
                        'TitleLineHeight'=>[ 'type'=>'string','default'=>'28px'], 
                        'TitleTransform'=>[ 'type'=>'string','default'=>'capitalize'], 
                        'TitleDecoration'=>[ 'type'=>'string','default'=>'none'],
                        'TitleLetterSpacing'=>[ 'type'=>'number','default'=>''], 
                        
                        'metafontfamily'=>[ 'type'=>'string','default'=>'Poppins'],
                        'metafontSize'=>[ 'type'=>'string','default'=>'14px'], 
                        'metaFontWeight'=>[ 'type'=>'string','default'=>'400'], 
                        'metaLineHeight'=>[ 'type'=>'string','default'=>'1.4'], 
                        'metaTransform'=>[ 'type'=>'string','default'=>''], 
                        'metaDecoration'=>[ 'type'=>'string','default'=>'none'], 
                        'metaLetterSpacing'=>[ 'type'=>'number','default'=>1], 

                        'contentfontfamily'=>[ 'type'=>'string','default'=>'Poppins'],
                        'contentfontSize'=>[ 'type'=>'string','default'=>'20px'], 
                        'contentFontWeight'=>[ 'type'=>'string','default'=>'700'], 
                        'contentLineHeight'=>[ 'type'=>'string','default'=>'1'], 
                        'contentTransform'=>[ 'type'=>'string','default'=>'uppercase'], 
                        'contentDecoration'=>[ 'type'=>'string','default'=>'none'], 
                        'contentLetterSpacing'=>[ 'type'=>'number','default'=>''], 
                        
                        'postcontentfontfamily'=>[ 'type'=>'string','default'=>'Poppins'],
                        'postcontentfontSize'=>[ 'type'=>'string','default'=>'16px'], 
                        'postcontentFontWeight'=>[ 'type'=>'string','default'=>'400'], 
                        'postcontentLineHeight'=>[ 'type'=>'string','default'=>'25px'], 
                        'postcontentTransform'=>[ 'type'=>'string','default'=>''], 
                        'postcontentDecoration'=>[ 'type'=>'string','default'=>'none'], 
                        'postcontentLetterSpacing'=>[ 'type'=>'number','default'=>''], 

                        'btnfontfamily'=>[ 'type'=>'string','default'=>'Poppins'],
                        'btnfontSize'=>[ 'type'=>'string','default'=>'14px'], 
                        'btnFontWeight'=>[ 'type'=>'string','default'=>'600'], 
                        'btnLineHeight'=>[ 'type'=>'string','default'=>'1.4'], 
                        'btnTransform'=>[ 'type'=>'string','default'=>'uppercase'], 
                        'btnDecoration'=>[ 'type'=>'string','default'=>'none'], 
                        'btnLetterSpacing'=>[ 'type'=>'number','default'=>1], 

                        'addid'=>['type'=>'string', 'default'=>'',],
                        'addclass'=>['type' => 'string','default' => ''],
                        'customcss'=>['type' => 'string','default' => ''],
                    ]
                )); 
        
        
    }       

    /**
     * Load the localisation file.
     */
    public function spice_blocks_load_plugin_textdomain() {
        load_textdomain(
            'spice-blocks',
            plugin_dir_path( __FILE__ ) . 'languages/' . get_locale() . '.mo'
        );
    }

}


/** 
 * Init
 */
function spice_blocks_run_plugin(){
   return Spice_Blocks::init();

}
spice_blocks_run_plugin();


//Add Category 
function spice_blocks_custom_block_category( $spice_blocks_categories ) {
    return array_merge(
        array(
            array(
                'slug' => 'spice-blocks',
                'title' => __( 'Spice Blocks', 'spice-blocks' ),
            ),
        ),
        $spice_blocks_categories
    );
}
if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
    add_filter( 'block_categories_all', 'spice_blocks_custom_block_category' );
} else {
    add_filter( 'block_categories', 'spice_blocks_custom_block_category', 10, 2 );
}

//Body Class
add_filter( 'body_class', 'spice_blocks_body_class' );
function spice_blocks_body_class( $classes ) {
    $classes[] = 'spice-block';
    return $classes;
}

//Download Page
// Trigger download if 'download_page' is set in POST request
function spice_block_json_download_new() {

    // Check if form submitted
    if ( isset( $_POST['download_page'] ) ) {

        // Verify nonce for security
        if ( ! isset( $_POST['spice_block_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['spice_block_nonce'] ) ), 'spice_block_download_action' ) ) {
            wp_die( esc_html__( 'Security check failed.', 'spice-blocks' ) );
        }

        // Validate and sanitize the post ID from GET
        $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

        if ( empty( $post_id ) ) {
            echo '<script type="text/javascript">
                    window.onload = function () { alert("Please save the page first."); }
                  </script>';
            return;
        }

        // Query the post
        $query = new WP_Query( array( 'page_id' => $post_id ) );

        $posts = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) :
                $query->the_post();

                $posts = array(
                    '__file'  => 'Spice_Blocks_Export',
                    'version' => 2,
                    'content' => get_the_content(),
                );

            endwhile;
            wp_reset_postdata();
        }

        // Send JSON download
        $filename = 'WP-POST-' . $post_id . '.json';

        header( 'Content-Type: application/json' );
        header( 'Content-Disposition: attachment; filename=' . sanitize_file_name( $filename ) );
        header( 'Pragma: no-cache' );

        // Safely output JSON
        echo wp_json_encode( $posts, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }
}
add_action( 'admin_init', 'spice_block_json_download_new' );




// Add Custom Template
function spice_blocks_custom_template_replace() {
    $theme_template_path_html = get_stylesheet_directory() . '/templates/spice-blocks-full-width-template.html';
    $theme_template_path_php  = get_stylesheet_directory() . '/spice-blocks-full-width-template.php';

    if ( file_exists( get_template_directory() . '/theme.json' ) ) {
        // Use HTML template if theme.json exists
        $plugin_template_path = plugin_dir_path( __FILE__ ) . 'inc/template/spice-blocks-full-width-template.html';

        if ( ! file_exists( $theme_template_path_html ) && file_exists( $plugin_template_path ) ) {
            if ( ! copy( $plugin_template_path, $theme_template_path_html ) ) {
                add_action( 'admin_notices', function () use ( $plugin_template_path, $theme_template_path_html ) {
                    printf(
                        '<div class="notice notice-error"><p>%s</p></div>',
                        esc_html( sprintf( 'Failed to copy %s to %s', $plugin_template_path, $theme_template_path_html ) )
                    );
                } );
            }
        }
    } else {
        // Use PHP template otherwise
        $plugin_template_path = plugin_dir_path( __FILE__ ) . 'inc/template/spice-blocks-full-width-template.php';

        if ( ! file_exists( $theme_template_path_php ) && file_exists( $plugin_template_path ) ) {
            if ( ! copy( $plugin_template_path, $theme_template_path_php ) ) {
                add_action( 'admin_notices', function () use ( $plugin_template_path, $theme_template_path_php ) {
                    printf(
                        '<div class="notice notice-error"><p>%s</p></div>',
                        esc_html( sprintf( 'Failed to copy %s to %s', $plugin_template_path, $theme_template_path_php ) )
                    );
                } );
            }
        }
    }
}

add_action('after_setup_theme', 'spice_blocks_custom_template_replace');

//Add Template in Block themes
if(file_exists(get_template_directory().'/theme.json')){
    function filter_theme_json_theme( $theme_json ){
        $new_data = array(
            'version'  => 2,
            'customTemplates'=> array(
                array(
                    'name'=> 'spice-blocks-full-width-template',
                    'title'=> 'Spice Blocks Full Width Template'
                ),   
            ),
        );

        return $theme_json->update_with( $new_data );
    }
    add_filter( 'wp_theme_json_data_theme', 'filter_theme_json_theme' );
}

function spice_blocks_render_category_tab_block($attributes) {
    $uniqueid = esc_attr($attributes['uniqueid']);
    $customcss = isset($attributes['customcss']) ? $attributes['customcss'] : '';
    $selected_categories = isset($attributes['selectedCategories']) ? $attributes['selectedCategories'] : [];
    $excerpt_length = isset($attributes['excerptLength']) ? intval($attributes['excerptLength']) : 20;
    $post_order = isset($attributes['order']) && $attributes['order'] === 'asc' ? 'ASC' : 'DESC';
    $posts_per_page = isset($attributes['postsPerPage']) ? intval($attributes['postsPerPage']) : 6;
    $category_colors = isset($attributes['categoryColors']) ? $attributes['categoryColors'] : [];
    $posts_output = '';
    $categories_output = '';

    $categories = get_categories([
        'include' => $selected_categories,
        'hide_empty' => false,
    ]);

    $categories_output .= '<button data-filter="all" class="hst-btn active">All</button>';
    foreach ($categories as $cat) {
        $categories_output .= '<button data-filter="' . esc_attr($cat->slug) . '" class="hst-btn">' . esc_html($cat->name) . '</button>';
    }

        // Category-wise posts_output
    $all_tab_output = '<div class="hst-tab-content" data-cat="all">';
    $category_tabs_output = [];

    $displayed_ids = []; // Optional if you want to avoid duplicates across tabs

    foreach ($categories as $cat) {
        $cat_slug = esc_attr($cat->slug);
        $cat_posts_html = '<div class="hst-tab-content" data-cat="' . $cat_slug . '" style="display:none;">';

        $cat_query = new WP_Query([
            'post_status'         => 'publish',
            'posts_per_page'      => $posts_per_page,
            'orderby'             => 'meta_value_num',
            'order'               => $post_order,
            'cat'                 => $cat->term_id,
            'ignore_sticky_posts' => true
        ]);

        if ($cat_query->have_posts()) {
            while ($cat_query->have_posts()) {
                $cat_query->the_post();
                $post_id = get_the_ID();
                $displayed_ids[] = $post_id;
                $cat_slugs = array();
                $cat_names = array();
                $cat_links = array();

                $categories = get_the_category();
                if (!empty($categories)) {
                    foreach ($categories as $category) {
                        $cat_slugs[] = esc_attr($category->slug);
                        $cat_names[] = esc_html($category->name);
                        $cat_links[] = esc_url(get_category_link($category->term_id));
                    }
                } else {
                    $cat_slugs[] = 'uncategorized';
                    $cat_names[] = 'Uncategorized';
                    $cat_links[] = '#';
                }

                // Concatenated slug string for data attribute
                $cat_slug_attr = implode(',', $cat_slugs);
                // Use first category for display (visual only)
                $cat_name = $cat_names[0];
                $cat_link = $cat_links[0];

                $author_name = get_the_author();
                $comment_count = get_comments_number();
                $post_date = get_the_date('M j, Y');
                $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt()), $excerpt_length);

                $displayauthor = ($attributes['displayAuthor'] === true)
                    ? '<span class="hst-author"><i class="fa-solid fa-user"></i><a href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html($author_name) . '</a></span>'
                    : '';

                $displaydate = ($attributes['displayDate'] === true)
                    ? '<span class="hst-date"><i class="fa-regular fa-calendar"></i><a href="' . esc_url(home_url('/')) . esc_html(gmdate('Y/m', strtotime(get_the_date()))) . '">' . esc_html($post_date) . '</a></span>'
                    : '';

                $displaycomment = ($attributes['displayComment'] === true)
                    ? '<span class="hst-comment"><i class="fa-solid fa-message"></i><a href="' . get_the_permalink() . '#respond">' . esc_html($comment_count) . '</a></span>'
                    : '';
                //print_r($attributes['categoryColors']);
                $displaycategory = '';
                if ($attributes['displayCat'] === true && !empty($categories)) {
                    $displaycategory = '<span class="hst-category">';
                    foreach ($categories as $category) {
                        $slug = esc_attr($category->slug);
                        $name = esc_html($category->name);
                        $link = esc_url(get_category_link($category->term_id));
                        $color = isset($category_colors[$slug]) ? ' style="background-color:' . esc_attr($category_colors[$slug]) . '"' : '';
                        $displaycategory .= '<a class="post-category" href="' . $link . '" target="_blank" rel="noopener noreferrer"' . $color . '>' . $name . '</a> ';
                    }
                    $displaycategory .= '</span>';
                }

                $displaycontent = ($attributes['displayContent'] === true)
                    ? '<p class="hst-post-excerpt">' . esc_html($excerpt) . '</p>'
                    : '';

                        // All the meta setup (excerpt, author, date, comment count, etc.)
                $cat_posts_html .= '<article class="hst-post" data-category="' . esc_attr($cat_slug_attr) . '">
                                        <div class="hst-post-wrap">
                                            <div class="hst-post-thumbg 01">
                                                ' . get_the_post_thumbnail($post_id, 'medium') . $displaycategory . '                        
                                            </div>
                                            <div class="hst-post-content">
                                                <div class="hst-post-meta">                            
                                                    ' . $displayauthor . $displaydate . $displaycomment . '
                                                </div>
                                                <h2 class="hst-post-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>
                                                ' . $displaycontent . '
                                                <div class="hst-bottom-meta">
                                                    ' . $displaycategory . $displaycomment . '                            
                                                </div>
                                            </div>
                                        </div>
                                    </article>'; // Simplified for clarity
            }
            wp_reset_postdata();
        }

        $cat_posts_html .= '</div>';
        $category_tabs_output[] = $cat_posts_html;
    }

    // Render "All" tab (optional: fetch again or combine results)
    $all_query = new WP_Query([
        'post_status'         => 'publish',
        'posts_per_page'      => $posts_per_page,
        'orderby'             => 'meta_value_num',
        'order'               => $post_order,
        'category__in'        => $selected_categories,
        'ignore_sticky_posts' => true
    ]);

    if ($all_query->have_posts()) {
        while ($all_query->have_posts()) {
            $all_query->the_post();
            $post_id = get_the_ID();
            $displayed_ids[] = $post_id;
            $cat_slugs = array();
            $cat_names = array();
            $cat_links = array();

            $categories = get_the_category();
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    $cat_slugs[] = esc_attr($category->slug);
                    $cat_names[] = esc_html($category->name);
                    $cat_links[] = esc_url(get_category_link($category->term_id));
                }
            } else {
                $cat_slugs[] = 'uncategorized';
                $cat_names[] = 'Uncategorized';
                $cat_links[] = '#';
            }

            // Concatenated slug string for data attribute
            $cat_slug_attr = implode(',', $cat_slugs);
            // Use first category for display (visual only)
            $cat_name = $cat_names[0];
            $cat_link = $cat_links[0];

            $author_name = get_the_author();
            $comment_count = get_comments_number();
            $post_date = get_the_date('M j, Y');
            $excerpt = wp_trim_words(wp_strip_all_tags(get_the_excerpt()), $excerpt_length);

            $displayauthor = ($attributes['displayAuthor'] === true)
                ? '<span class="hst-author"><i class="fa-solid fa-user"></i><a href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html($author_name) . '</a></span>'
                : '';

            $displaydate = ($attributes['displayDate'] === true)
                ? '<span class="hst-date"><i class="fa-regular fa-calendar"></i><a href="' . esc_url(home_url('/')) . esc_html(gmdate('Y/m', strtotime(get_the_date()))) . '">' . esc_html($post_date) . '</a></span>'
                : '';

            $displaycomment = ($attributes['displayComment'] === true)
                ? '<span class="hst-comment"><i class="fa-solid fa-message"></i><a href="' . get_the_permalink() . '#respond">' . esc_html($comment_count) . '</a></span>'
                : '';

            $displaycategory = '';
            if ($attributes['displayCat'] === true && !empty($categories)) {
                $displaycategory = '<span class="hst-category">';                
                foreach ($categories as $category) {
                        $slug = esc_attr($category->slug);
                        $name = esc_html($category->name);
                        $link = esc_url(get_category_link($category->term_id));
                        $color = isset($category_colors[$slug]) ? ' style="background-color:' . esc_attr($category_colors[$slug]) . '"' : '';
                        $displaycategory .= '<a class="post-category" href="' . $link . '" target="_blank" rel="noopener noreferrer"' . $color . '>' . $name . '</a> ';
                    }
                $displaycategory .= '</span>';
            }

            $displaycontent = ($attributes['displayContent'] === true)
                ? '<p class="hst-post-excerpt">' . esc_html($excerpt) . '</p>'
                : '';
            $all_tab_output .= '<article class="hst-post" data-category="' . esc_attr($cat_slug_attr) . '">
                                    <div class="hst-post-wrap 02">
                                        <div class="hst-post-thumbg 02">
                                            ' . get_the_post_thumbnail($post_id, 'medium') . $displaycategory . '                        
                                        </div>
                                        <div class="hst-post-content">
                                            <div class="hst-post-meta">                            
                                                ' . $displayauthor . $displaydate . $displaycomment . '
                                            </div>
                                            <h2 class="hst-post-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>
                                            ' . $displaycontent . '
                                            <div class="hst-bottom-meta">
                                                ' . $displaycategory . $displaycomment . '                            
                                            </div>
                                        </div>
                                    </div>
                                </article>';
        }
        wp_reset_postdata();
    }
    $all_tab_output .= '</div>';

    $posts_output = $all_tab_output . implode('', $category_tabs_output);

   
    // Optional CSS
    $custom_style = '';
    if (!empty($customcss)) {
        $custom_style = "<style>'.$customcss.'</style>";
    }

    // Scoped JS with Unique ID
    $script ='<script>
                document.addEventListener("DOMContentLoaded", function () {
                    const container = document.getElementById("'.$uniqueid.'");
                    const buttons = container.querySelectorAll(".hst-btn");
                    const tabContents = container.querySelectorAll(".hst-tab-content");

                    // Default tab
                    const devcat = "all"; 
                    const defaultTab = container.querySelector(`.hst-tab-content[data-cat="${devcat}"]`);
                    if (defaultTab) {
                        defaultTab.style.display = "flex";

                        // Set "all" button active if present
                        const defaultBtn = container.querySelector(`.hst-btn[data-filter="${devcat}"]`);
                        if (defaultBtn) defaultBtn.classList.add("active");

                        updateVisiblePostStyles(); // ✅ run once on load
                    }

                    // Click handlers...
                    buttons.forEach(btn => {
                        btn.addEventListener("click", () => {
                            const filter = btn.getAttribute("data-filter");

                            buttons.forEach(b => b.classList.remove("active"));
                            btn.classList.add("active");

                            tabContents.forEach(tab => {
                                tab.style.display = (tab.getAttribute("data-cat") === filter) ? "flex" : "none";
                            });

                            updateVisiblePostStyles();
                        });
                    });

                    function resetPostStyles(post) {
                        post.classList.remove("highlight-post");
                        post.style.flex = "";
                        post.style.borderBottom = "";

                        const excerpt = post.querySelector(".hst-post-excerpt");
                        const thumb = post.querySelector(".hst-post-thumbg");
                        if (excerpt) excerpt.style.marginBottom = "";
                        if (thumb) {
                            thumb.style.maxWidth = "";
                            thumb.style.height = "";
                        }
                    }

                    function updateVisiblePostStyles() {
                        // Reset all posts
                        const allPosts = container.querySelectorAll(".hst-post");
                        allPosts.forEach(resetPostStyles);

                        // Find the currently visible tab-content
                        tabContents.forEach(tab => {
                            if (tab.style.display === "flex") {
                                const posts = tab.querySelectorAll(".hst-post");

                                // Add highlight to first 2
                                posts.forEach((post, index) => {
                                    if (index < 2) {
                                        post.classList.add("highlight-post");
                                    }
                                });

                                if (posts.length === 1) {
                                    const post = posts[0];
                                    post.style.flex = "100%";
                                    post.style.borderBottom = "none";
                                    const excerpt = post.querySelector(".hst-post-excerpt");
                                    const thumb = post.querySelector(".hst-post-thumbg");
                                    if (excerpt) excerpt.style.marginBottom = "0";
                                    if (thumb) {
                                        thumb.style.maxWidth = "100%";
                                        thumb.style.height = "100%";
                                    }
                                }

                                if (posts.length === 2) {
                                    posts.forEach(p => {
                                        p.style.borderBottom = "none";
                                        const excerpt = p.querySelector(".hst-post-excerpt");
                                        if (excerpt) excerpt.style.marginBottom = "0";
                                    });
                                }
                            }
                        });
                    }

                    window.addEventListener("resize", updateVisiblePostStyles);
                });

          
        var url6 = "https://fonts.googleapis.com/css2?family='.$attributes['metafontfamily'].':wght@100;200;300;400;500;600;700;800;900&display=swap";
        var link6 = document.createElement("link");
        link6.href = url6;
        link6.rel = "stylesheet";
        link6.type =  "text/css";             
        document.head.appendChild(link6);

        var url3 = "https://fonts.googleapis.com/css2?family='.$attributes['fontfamily'].':wght@100;200;300;400;500;600;700;800;900&display=swap";
        var link3 = document.createElement("link");
        link3.href = url3;
        link3.rel = "stylesheet";
        link3.type =  "text/css";             
        document.head.appendChild(link3);
        
        var url4 = "https://fonts.googleapis.com/css2?family='.$attributes['contentfontfamily'].':wght@100;200;300;400;500;600;700;800;900&display=swap";
        var link4 = document.createElement("link");
        link4.href = url4;
        link4.rel = "stylesheet";
        link4.type =  "text/css";             
        document.head.appendChild(link4);

        var url1 = "https://fonts.googleapis.com/css2?family='.$attributes['postcontentfontfamily'].':wght@100;200;300;400;500;600;700;800;900&display=swap";
        var link1 = document.createElement("link");
        link1.href = url1;
        link1.rel = "stylesheet";
        link1.type =  "text/css";             
        document.head.appendChild(link1);

        var url5 = "https://fonts.googleapis.com/css2?family='.$attributes['btnfontfamily'].':wght@100;200;300;400;500;600;700;800;900&display=swap";
        var link5 = document.createElement("link");
        link5.href = url5;
        link5.rel = "stylesheet";
        link5.type =  "text/css";             
        document.head.appendChild(link5);

    </script>';

    $postbackground = '';
    if( $attributes['bg1Color'] == '' && $attributes['bg1gradientValue'] != ''){
        $postbackground = $attributes['bg1gradientValue'];
    }
    else if( $attributes['bg1Color'] != '' && $attributes['bg1gradientValue'] == ''){
        $postbackground = $attributes['bg1Color'];
    }

    $titlebackground = '';
    if( $attributes['bgColor'] == '' && $attributes['bggradientValue'] != ''){
        $titlebackground = $attributes['bggradientValue'];
    }
    else if( $attributes['bgColor'] != '' && $attributes['bggradientValue'] == ''){
        $titlebackground = $attributes['bgColor'];
    }

    $btnbackground = '';
    if( $attributes['btnbgColor'] == '' && $attributes['btnbggradientValue'] != ''){
        $btnbackground = $attributes['btnbggradientValue'];
    }
    else if( $attributes['btnbgColor'] != '' && $attributes['btnbggradientValue'] == ''){
        $btnbackground = $attributes['btnbgColor'];
    }

    $btnbackgroundactive = '';
    if( $attributes['btnbgColoractive'] == '' && $attributes['btnbggradientactiveValue'] != ''){
        $btnbackgroundactive = $attributes['btnbggradientactiveValue'];
    }
    else if( $attributes['btnbgColoractive'] != '' && $attributes['btnbggradientactiveValue'] == ''){
        $btnbackgroundactive = $attributes['btnbgColoractive'];
    }

    $catbackground = '';
    if( $attributes['catbgColor'] == '' && $attributes['catbggradientValue'] != ''){
        $catbackground = $attributes['catbggradientValue'];
    }
    else if( $attributes['catbgColor'] != '' && $attributes['catbggradientValue'] == ''){
        $catbackground = $attributes['catbgColor'];
    }

    $borderwidth=(!empty($attributes['border']['width']))? $attributes['border']['width'] :null;
    $borderstyle=(!empty($attributes['border']['style']))? $attributes['border']['style'] :null;
    $bordercolor=(!empty($attributes['border']['color']))? $attributes['border']['color'] :null;

    $bordertop=(!empty($attributes['border']['top'])) ? $attributes['border']['top']['width'].' '. $attributes['border']['top']['style'].' '. $attributes['border']['top']['color'] : null;
    $borderright=(!empty($attributes['border']['right'])) ? $attributes['border']['right']['width'].' '. $attributes['border']['right']['style'].' '. $attributes['border']['right']['color'] : null;
    $borderbottom=(!empty($attributes['border']['bottom'])) ? $attributes['border']['bottom']['width'].' '. $attributes['border']['bottom']['style'].' '. $attributes['border']['bottom']['color'] : null;
    $borderleft=(!empty($attributes['border']['left'] )) ? $attributes['border']['left']['width'].' '. $attributes['border']['left']['style'].' '. $attributes['border']['left']['color'] : null;


    $tabborderwidth=(!empty($attributes['tabborder']['width']))? $attributes['tabborder']['width'] :null;
    $tabborderstyle=(!empty($attributes['tabborder']['style']))? $attributes['tabborder']['style'] :null;
    $tabbordercolor=(!empty($attributes['tabborder']['color']))? $attributes['tabborder']['color'] :null;

    $tabbordertop=(!empty($attributes['tabborder']['top'])) ? $attributes['tabborder']['top']['width'].' '. $attributes['tabborder']['top']['style'].' '. $attributes['tabborder']['top']['color'] : null;
    $tabborderright=(!empty($attributes['tabborder']['right'])) ? $attributes['tabborder']['right']['width'].' '. $attributes['tabborder']['right']['style'].' '. $attributes['tabborder']['right']['color'] : null;
    $tabborderbottom=(!empty($attributes['tabborder']['bottom'])) ? $attributes['tabborder']['bottom']['width'].' '. $attributes['tabborder']['bottom']['style'].' '. $attributes['tabborder']['bottom']['color'] : null;
    $tabborderleft=(!empty($attributes['tabborder']['left'] )) ? $attributes['tabborder']['left']['width'].' '. $attributes['tabborder']['left']['style'].' '. $attributes['tabborder']['left']['color'] : null;

    $catborderwidth=(!empty($attributes['catborder']['width']))? $attributes['catborder']['width'] :null;
    $catborderstyle=(!empty($attributes['catborder']['style']))? $attributes['catborder']['style'] :null;
    $catbordercolor=(!empty($attributes['catborder']['color']))? $attributes['catborder']['color'] :null;

    $catbordertop=(!empty($attributes['catborder']['top'])) ? $attributes['catborder']['top']['width'].' '. $attributes['catborder']['top']['style'].' '. $attributes['catborder']['top']['color'] : null;
    $catborderright=(!empty($attributes['catborder']['right'])) ? $attributes['catborder']['right']['width'].' '. $attributes['catborder']['right']['style'].' '. $attributes['catborder']['right']['color'] : null;
    $catborderbottom=(!empty($attributes['catborder']['bottom'])) ? $attributes['catborder']['bottom']['width'].' '. $attributes['catborder']['bottom']['style'].' '. $attributes['catborder']['bottom']['color'] : null;
    $catborderleft=(!empty($attributes['catborder']['left'] )) ? $attributes['catborder']['left']['width'].' '. $attributes['catborder']['left']['style'].' '. $attributes['catborder']['left']['color'] : null;

    $titleborderwidth=(!empty($attributes['titleborder']['width']))? $attributes['titleborder']['width'] :null;
    $titleborderstyle=(!empty($attributes['titleborder']['style']))? $attributes['titleborder']['style'] :null;
    $titlebordercolor=(!empty($attributes['titleborder']['color']))? $attributes['titleborder']['color'] :null;

    $titlebordertop=(!empty($attributes['titleborder']['top'])) ? $attributes['titleborder']['top']['width'].' '. $attributes['titleborder']['top']['style'].' '. $attributes['titleborder']['top']['color'] : null;
    $titleborderright=(!empty($attributes['titleborder']['right'])) ? $attributes['titleborder']['right']['width'].' '. $attributes['titleborder']['right']['style'].' '. $attributes['titleborder']['right']['color'] : null;
    $titleborderbottom=(!empty($attributes['titleborder']['bottom'])) ? $attributes['titleborder']['bottom']['width'].' '. $attributes['titleborder']['bottom']['style'].' '. $attributes['titleborder']['bottom']['color'] : null;
    $titleborderleft=(!empty($attributes['titleborder']['left'] )) ? $attributes['titleborder']['left']['width'].' '. $attributes['titleborder']['left']['style'].' '. $attributes['titleborder']['left']['color'] : null;

    $postborderwidth=(!empty($attributes['postborder']['width']))? $attributes['postborder']['width'] :null;
    $postborderstyle=(!empty($attributes['postborder']['style']))? $attributes['postborder']['style'] :null;
    $postbordercolor=(!empty($attributes['postborder']['color']))? $attributes['postborder']['color'] :null;

    $postbordertop=(!empty($attributes['postborder']['top'])) ? $attributes['postborder']['top']['width'].' '. $attributes['postborder']['top']['style'].' '. $attributes['postborder']['top']['color'] : null;
    $postborderright=(!empty($attributes['postborder']['right'])) ? $attributes['postborder']['right']['width'].' '. $attributes['postborder']['right']['style'].' '. $attributes['postborder']['right']['color'] : null;
    $postborderbottom=(!empty($attributes['postborder']['bottom'])) ? $attributes['postborder']['bottom']['width'].' '. $attributes['postborder']['bottom']['style'].' '. $attributes['postborder']['bottom']['color'] : null;
    $postborderleft=(!empty($attributes['postborder']['left'] )) ? $attributes['postborder']['left']['width'].' '. $attributes['postborder']['left']['style'].' '. $attributes['postborder']['left']['color'] : null;

    $posthborderwidth=(!empty($attributes['posthborder']['width']))? $attributes['posthborder']['width'] :null;
    $posthborderstyle=(!empty($attributes['posthborder']['style']))? $attributes['posthborder']['style'] :null;
    $posthbordercolor=(!empty($attributes['posthborder']['color']))? $attributes['posthborder']['color'] :null;

    $posthbordertop=(!empty($attributes['posthborder']['top'])) ? $attributes['posthborder']['top']['width'].' '. $attributes['posthborder']['top']['style'].' '. $attributes['posthborder']['top']['color'] : null;
    $posthborderright=(!empty($attributes['posthborder']['right'])) ? $attributes['posthborder']['right']['width'].' '. $attributes['posthborder']['right']['style'].' '. $attributes['posthborder']['right']['color'] : null;
    $posthborderbottom=(!empty($attributes['posthborder']['bottom'])) ? $attributes['posthborder']['bottom']['width'].' '. $attributes['posthborder']['bottom']['style'].' '. $attributes['posthborder']['bottom']['color'] : null;
    $posthborderleft=(!empty($attributes['posthborder']['left'] )) ? $attributes['posthborder']['left']['width'].' '. $attributes['posthborder']['left']['style'].' '. $attributes['posthborder']['left']['color'] : null;



    $imgborderwidth=(!empty($attributes['imgborder']['width']))? $attributes['imgborder']['width'] :null;
    $imgborderstyle=(!empty($attributes['imgborder']['style']))? $attributes['imgborder']['style'] :null;
    $imgbordercolor=(!empty($attributes['imgborder']['color']))? $attributes['imgborder']['color'] :null;

    $imgbordertop=(!empty($attributes['imgborder']['top'])) ? $attributes['imgborder']['top']['width'].' '. $attributes['imgborder']['top']['style'].' '. $attributes['imgborder']['top']['color'] : null;
    $imgborderright=(!empty($attributes['imgborder']['right'])) ? $attributes['imgborder']['right']['width'].' '. $attributes['imgborder']['right']['style'].' '. $attributes['imgborder']['right']['color'] : null;
    $imgborderbottom=(!empty($attributes['imgborder']['bottom'])) ? $attributes['imgborder']['bottom']['width'].' '. $attributes['imgborder']['bottom']['style'].' '. $attributes['imgborder']['bottom']['color'] : null;
    $imgborderleft=(!empty($attributes['imgborder']['left'] )) ? $attributes['imgborder']['left']['width'].' '. $attributes['imgborder']['left']['style'].' '. $attributes['imgborder']['left']['color'] : null;

    $btnborderwidth=(!empty($attributes['btnborder']['width']))? $attributes['btnborder']['width'] :null;
    $btnborderstyle=(!empty($attributes['btnborder']['style']))? $attributes['btnborder']['style'] :null;
    $btnbordercolor=(!empty($attributes['btnborder']['color']))? $attributes['btnborder']['color'] :null;

    $btnbordertop=(!empty($attributes['btnborder']['top'])) ? $attributes['btnborder']['top']['width'].' '. $attributes['btnborder']['top']['style'].' '. $attributes['btnborder']['top']['color'] : null;
    $btnborderright=(!empty($attributes['btnborder']['right'])) ? $attributes['btnborder']['right']['width'].' '. $attributes['btnborder']['right']['style'].' '. $attributes['btnborder']['right']['color'] : null;
    $btnborderbottom=(!empty($attributes['btnborder']['bottom'])) ? $attributes['btnborder']['bottom']['width'].' '. $attributes['btnborder']['bottom']['style'].' '. $attributes['btnborder']['bottom']['color'] : null;
    $btnborderleft=(!empty($attributes['btnborder']['left'] )) ? $attributes['btnborder']['left']['width'].' '. $attributes['btnborder']['left']['style'].' '. $attributes['btnborder']['left']['color'] : null;

    $custom_style = '<style>
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-tab-content{
            --post-gap: '.$attributes['postgap'].'px;

        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-cat-sort{
            border: '.$borderwidth.' '.$borderstyle.' '.$bordercolor.';
            border-top:'.$bordertop.';
            border-right:'.$borderright.';
            border-bottom:'.$borderbottom.';
            border-left:'.$borderleft.';
            border-radius:'.$attributes['bordertopradius'].' '.$attributes['borderrightradius'].' '.$attributes['borderbottomradius'].' '.$attributes['borderleftradius'].';
            
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post{
            background:'.$postbackground.';
            padding: '.$attributes['paddings']['top'].' '.$attributes['paddings']['right'].' '.$attributes['paddings']['bottom'].' '.$attributes['paddings']['left'].';

        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post:not(.highlight-post){
            border: '.$postborderwidth.' '.$postborderstyle.' '.$postbordercolor.';
            border-top:'.$postbordertop.';
            border-right:'.$postborderright.';
            border-bottom:'.$postborderbottom.';
            border-left:'.$postborderleft.';
            border-radius:'.$attributes['postbordertopradius'].' '.$attributes['postborderrightradius'].' '.$attributes['postborderbottomradius'].' '.$attributes['postborderleftradius'].';
            
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-cat-sort .hst-showcase-title{
            border: '.$titleborderwidth.' '.$titleborderstyle.' '.$titlebordercolor.';
            border-top:'.$titlebordertop.';
            border-right:'.$titleborderright.';
            border-bottom:'.$titleborderbottom.';
            border-left:'.$titleborderleft.';
            border-radius:'.$attributes['titlebordertopradius'].' '.$attributes['titleborderrightradius'].' '.$attributes['titleborderbottomradius'].' '.$attributes['titleborderleftradius'].';
            padding: '.$attributes['titlepaddings']['top'].' '.$attributes['titlepaddings']['right'].' '.$attributes['titlepaddings']['bottom'].' '.$attributes['titlepaddings']['left'].';
            background:'.$titlebackground.';
            
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-cat-sort .hst-showcase-title h2{
            font-family: '.$attributes['contentfontfamily'].';
            font-size: '.$attributes['contentfontSize'].';
            font-weight: '.$attributes['contentFontWeight'].';
            text-transform:'.$attributes['contentTransform'].';
            text-decoration:'.$attributes['contentDecoration'].';
            line-height: '.$attributes['contentLineHeight'].';
            letter-spacing: '.$attributes['contentLetterSpacing'].'px;
            color:'.$attributes['titleColor'].';
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post.highlight-post{
            border: '.$posthborderwidth.' '.$posthborderstyle.' '.$posthbordercolor.';
            border-top:'.$posthbordertop.';
            border-right:'.$posthborderright.';
            border-bottom:'.$posthborderbottom.';
            border-left:'.$posthborderleft.';
            border-radius:'.$attributes['posthbordertopradius'].' '.$attributes['posthborderrightradius'].' '.$attributes['posthborderbottomradius'].' '.$attributes['posthborderleftradius'].';
                
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post.highlight-post .hst-post-content{                            
            padding: '.$attributes['contentpaddings']['top'].' '.$attributes['contentpaddings']['right'].' '.$attributes['contentpaddings']['bottom'].' '.$attributes['contentpaddings']['left'].';
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post .hst-post-content .hst-post-title{
            font-family: '.$attributes['fontfamily'].';
            font-size: '.$attributes['titlefontSize'].';
            font-weight: '.$attributes['TitleFontWeight'].';
            text-transform:'.$attributes['TitleTransform'].';
            text-decoration:'.$attributes['TitleDecoration'].';
            line-height: '.$attributes['TitleLineHeight'].';
            letter-spacing: '.$attributes['TitleLetterSpacing'].'px;
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-cat-sort .hst-showcase-category-btn .hst-btn{
            border: '.$tabborderwidth.' '.$tabborderstyle.' '.$tabbordercolor.';
            border-top:'.$tabbordertop.';
            border-right:'.$tabborderright.';
            border-bottom:'.$tabborderbottom.';
            border-left:'.$tabborderleft.';
            border-radius:'.$attributes['tabbordertopradius'].' '.$attributes['tabborderrightradius'].' '.$attributes['tabborderbottomradius'].' '.$attributes['tabborderleftradius'].';
            padding: '.$attributes['tabspaddings']['top'].' '.$attributes['tabspaddings']['right'].' '.$attributes['tabspaddings']['bottom'].' '.$attributes['tabspaddings']['left'].';
            font-family: '.$attributes['btnfontfamily'].';
            font-size: '.$attributes['btnfontSize'].';
            font-weight: '.$attributes['btnFontWeight'].';
            text-transform:'.$attributes['btnTransform'].';
            text-decoration:'.$attributes['btnDecoration'].';
            line-height: '.$attributes['btnLineHeight'].';
            letter-spacing: '.$attributes['btnLetterSpacing'].'px;
            color:'.$attributes['btnColor'].';
            background:'.$btnbackground.';
        
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-cat-sort .hst-showcase-category-btn .hst-btn.active,
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-cat-sort .hst-showcase-category-btn .hst-btn:is(:hover, :focus){
            color:'.$attributes['btnColoractive'].';
            background:'.$btnbackgroundactive.';
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post .hst-post-thumbg:has(img){
            border: '.$imgborderwidth.' '.$imgborderstyle.' '.$imgbordercolor.';
            border-top:'.$imgbordertop.';
            border-right:'.$imgborderright.';
            border-bottom:'.$imgborderbottom.';
            border-left:'.$imgborderleft.';
            border-radius:'.$attributes['imgbordertopradius'].' '.$attributes['imgborderrightradius'].' '.$attributes['imgborderbottomradius'].' '.$attributes['imgborderleftradius'].';
            
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post.highlight-post .hst-category a{
            color:'.$attributes['catColor'].';
            background:'.$catbackground.';
            border: '.$catborderwidth.' '.$catborderstyle.' '.$catbordercolor.';
            border-top:'.$catbordertop.';
            border-right:'.$catborderright.';
            border-bottom:'.$catborderbottom.';
            border-left:'.$catborderleft.';
            border-radius:'.$attributes['catbordertopradius'].' '.$attributes['catborderrightradius'].' '.$attributes['catborderbottomradius'].' '.$attributes['catborderleftradius'].';
            padding: '.$attributes['catpaddings']['top'].' '.$attributes['catpaddings']['right'].' '.$attributes['catpaddings']['bottom'].' '.$attributes['catpaddings']['left'].';
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post .hst-category a{
            color:'.$attributes['catColor'].';
            background:'.$catbackground.';
            border: '.$catborderwidth.' '.$catborderstyle.' '.$catbordercolor.';
            border-top:'.$catbordertop.';
            border-right:'.$catborderright.';
            border-bottom:'.$catborderbottom.';
            border-left:'.$catborderleft.';
            border-radius:'.$attributes['catbordertopradius'].' '.$attributes['catborderrightradius'].' '.$attributes['catborderbottomradius'].' '.$attributes['catborderleftradius'].';
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post.highlight-post .hst-post-content .hst-post-meta{
            margin: '.$attributes['metamargins']['top'].' '.$attributes['metamargins']['right'].' '.$attributes['metamargins']['bottom'].' '.$attributes['metamargins']['left'].';                        
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post .hst-post-content :is(.hst-author, .hst-date, .hst-comment) a{
            font-family: '.$attributes['metafontfamily'].';
            font-size: '.$attributes['metafontSize'].';
            font-weight: '.$attributes['metaFontWeight'].';
            text-transform:'.$attributes['metaTransform'].';
            text-decoration:'.$attributes['metaDecoration'].';
            line-height: '.$attributes['metaLineHeight'].';
            letter-spacing: '.$attributes['metaLetterSpacing'].'px;
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post .hst-post-content :is(.hst-author, .hst-date, .hst-comment) i{
            color:'.$attributes['postmetaiconColor'].';
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post .hst-post-content :is(.hst-author, .hst-date, .hst-comment){
            color:'.$attributes['postmetaColor'].';
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post .hst-post-content :is(.hst-author, .hst-date, .hst-comment) a:is(:hover, :focus){
            color:'.$attributes['postmetahColor'].';
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post.highlight-post .hst-post-content .hst-post-title{
            color:'.$attributes['posttitleColor'].';
            margin: '.$attributes['posttitlemargins']['top'].' '.$attributes['posttitlemargins']['right'].' '.$attributes['posttitlemargins']['bottom'].' '.$attributes['posttitlemargins']['left'].';                        
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post .hst-post-content .hst-post-title{
            color:'.$attributes['posttitleColor'].';                     
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post .hst-post-content .hst-post-title a:is(:hover, :focus){
            color:'.$attributes['posttitlehColor'].';
        }
        #'.$uniqueid.'.hst-Post-Showcase .hst-showcase-posts .hst-post .hst-post-content .hst-post-excerpt{
            color:'.$attributes['postcontentColor'].';
            font-family: '.$attributes['postcontentfontfamily'].';
            font-size: '.$attributes['postcontentfontSize'].';
            font-weight: '.$attributes['postcontentFontWeight'].';
            text-transform:'.$attributes['postcontentTransform'].';
            text-decoration:'.$attributes['postcontentDecoration'].';
            line-height: '.$attributes['postcontentLineHeight'].';
            letter-spacing: '.$attributes['postcontentLetterSpacing'].'px;
            margin: '.$attributes['contentmargins']['top'].' '.$attributes['contentmargins']['right'].' '.$attributes['contentmargins']['bottom'].' '.$attributes['contentmargins']['left'].';                        
        }
        
        '.$customcss.'
    </style>';
    
    // Final Output
    $output = '<div id="' . esc_attr($attributes['addid']) . '"><div class="hst-Post-Showcase ' . esc_attr($attributes['addclass']) . '" id="' . $uniqueid . '">
        <div class="hst-showcase-cat-sort">
            <div class="hst-showcase-title"><h2>' . esc_html($attributes['titleText']) . '</h2></div>
            <div class="hst-showcase-category-btn">' . $categories_output . '</div>
        </div>
        <div class="hst-showcase-posts">' . $posts_output . '</div>
    </div></div>' . $custom_style . $script;

    return $output;
}
