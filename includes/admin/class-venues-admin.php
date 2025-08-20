<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
class TTA_Venues_Admin {
    public static function get_instance(){ static $inst; return $inst ?: $inst = new self(); }
    private function __construct(){ add_action('admin_menu',[ $this,'register_menu' ] ); }
    public function register_menu(){
        add_menu_page('TTA Venues','TTA Venues','manage_options','tta-venues',[ $this,'render_page' ],'dashicons-location-alt',9.8);
    }
    public function render_page(){
        $tabs = [ 'create'=>'Add Venue','manage'=>'Manage Venues' ];
        $current = isset($_GET['tab']) && isset($tabs[$_GET['tab']]) ? $_GET['tab'] : 'create';
        echo '<h1>TTA Venues</h1><h2 class="nav-tab-wrapper">';
        foreach($tabs as $slug=>$label){
            $class = $current===$slug ? ' nav-tab-active':'';
            $url = esc_url(add_query_arg(['page'=>'tta-venues','tab'=>$slug],admin_url('admin.php')));
            printf('<a href="%s" class="nav-tab%s">%s</a>',$url,$class,esc_html($label));
        }
        echo '</h2><div class="wrap">';
        $view = TTA_PLUGIN_DIR . "includes/admin/views/venues-{$current}.php";
        if(file_exists($view)){ include $view; } else { echo '<p>View not found.</p>'; }
        echo '</div>';
    }
}
TTA_Venues_Admin::get_instance();
