<?php
/**
 * Created by PhpStorm.
 * User: ali
 * Date: 12/8/18
 * Time: 11:50 AM
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class VTOW_initial_settings
{
    function __construct(){


        add_action( 'admin_menu', array($this,'add_migration_menu_page') );

    }
    function add_migration_menu_page(){
        if(current_user_can('manage_options')) {
            add_menu_page('ٰVirtuemart 2 Woocommerce', 'Virtuemart 2 WC', 'manage_options', 'Migration_options', array($this, 'Migration_options'), 'dashicons-download');
            add_submenu_page('Migration_options', 'Virtuemart 2 Woocommerce Settings', 'Setting', 'manage_options', 'Migration_settings', array($this, 'Migration_settings'));
        }
    }
    function Migration_options(){
        include VTOW_PLUGIN_PATH."/view/admin_migration_page.php";
        if(isset($_POST['submit']))
        {
            $migrate = new VTOW_migration();
            $migrate->migration();
        }
    }
    function Migration_settings(){
        $this->migration_save_settings();
        include VTOW_PLUGIN_PATH."/view/admin_settings.php";
    }
    private function migration_save_settings(){
        $_POST  = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        if ( ! isset( $_POST['VTOW_nonce'] ) ||
            ! wp_verify_nonce( $_POST['VTOW_nonce'], 'VTOW_nonce' ) )
            return;
        //var_dump($_POST);

        if(isset($_POST['vtm_setting_submit'])){
            update_option('vtm_Host',$_POST['host']);
            update_option('vtm_db',$_POST['db']);
            update_option('vtm_dbUser',$_POST['dbUser']);
            update_option('vtm_dbPass',$_POST['dbPass']);
            update_option('vrm_prefix',$_POST['vrm_prefix']);
        }
    }

}
$setting = new VTOW_initial_settings();