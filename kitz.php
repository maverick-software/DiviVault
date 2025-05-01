<?php
 /*
 * Plugin name: Kitz Pro Builder
 * Description: Kitz Builder helps you build Divi sites ten times faster with cloud storage and drop-in features.
 * Version: 6.12.24
 * Author: The Open Source Ageny
 * Author URI: https://theopensourceagency.com
 * License: GPL v2
 */

define('kitz_url', plugin_dir_url(__FILE__));
define('kitz_path', plugin_dir_path(__FILE__));
define('kitz_plugin', plugin_basename(__FILE__));
define('kitz_apiurl', "https://app.divikitz.com/");


define('CURRENT_SITEURL', get_site_url());
define('BUILDR_PLUGIN_SLUG','buildr');
define('UPDATE_PLUGIN_URL','https://app.divikitz.com/repository/');
define('code_updated','yes');

//Global Layout
// update_option('neo_globalsec_api', kitz_apiurl.'global-section-staff-api/');
// update_option('neo_globallayout_api', kitz_apiurl.'global-layout-staff-api/');

update_option('kitz_unauth_section_api', kitz_apiurl.'unauth-section-api');
update_option('kitz_unauth_layout_api', kitz_apiurl.'unauth-layout-api/');


update_option('kitz_drop_returnuri', kitz_url.'dropbox.php');

require_once 'includes/bloxx_core.php';
require_once 'bloxxtemplater.php';
require_once 'templates/bloxx_dashboard_api.php';
require_once 'templates/builderapi.php';
require_once 'templates/kitz_dropbox.php';

register_activation_hook(__FILE__, 'bloxx_buildr_plugin_activated'); 
    
register_deactivation_hook( __FILE__, 'bloxx_buildr_plugin_deactivated' );


function bloxx_buildr_plugin_activated() {
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => kitz_apiurl.'wp-json/kitzdropbox/connect',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    
    $decode_resp=json_decode($response, true);
    update_option('testE', $decode_resp);
    update_option('kitz_dropbox', "disable");
    if($decode_resp['code']=="200"){
        update_option('dropbox', "enable");
        update_option('drop_api', $decode_resp['drop_api']);
        update_option('drop_secret', $decode_resp['drop_secret']);
    }
}

function bloxx_buildr_plugin_deactivated() {
    update_option('dropbox', "disable");
    update_option('drop_api', '');
    update_option('drop_secret', ''); 
}






if (!function_exists('pre')) {
    function pre($arr){
        echo '<pre>';
        print_r($arr);
        echo '</pre>';
    }
}


if (!function_exists('db_refresh_token_function')) {
    function db_refresh_token_function(){
        $drop_key= get_option('drop_api', true);
        $drop_secret= get_option('drop_secret', true);
        $refresh_token=get_option('db_refresh_token', true);
        // echo $refresh_token;
        // die("HJERE");
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.dropbox.com/oauth2/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "grant_type=refresh_token&refresh_token=$refresh_token&client_id=$drop_key&client_secret=$drop_secret",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $decode_drop_box= json_decode($response, true);
        update_option('dropbox_token_detail', $decode_drop_box);
    }
}





add_action( 'wp_head', 'wp_head_callback' );
function wp_head_callback() {
    $bloxxbuilder_connect_type = get_option('bloxxbuilder_connect_type');
    $is_bloxxbuilder_connect = get_option('bloxxbuilder_connect');
    if($bloxxbuilder_connect_type=='simple'){
        $auth_type = 'simple';
    }else{
        $auth_type = '';
    }
    ?>
    <input type="hidden" id="auth_type" value="<?php echo $auth_type; ?>">
    <input type="hidden" id="site_url" value="<?php echo CURRENT_SITEURL; ?>">
    <input type="hidden" id="is_bloxxbuilder_connect" value="<?php echo $is_bloxxbuilder_connect; ?>">
    
    <?php
}



defined( 'ABSPATH' ) || exit;


if( ! class_exists( 'updateChecker' ) ) {

    class updateChecker{

        public $plugin_slug;
        public $version;
        public $cache_key;
        public $cache_allowed;
        public $gaurav;
        public function __construct() {

            $this->plugin_slug = plugin_basename( __DIR__ );
            $this->version = '6.12.24';
            $this->cache_key = 'kitsbuilder';
            $this->cache_allowed = false;

            add_filter( 'plugins_api', array( $this, 'info' ), 20, 3 );
            add_filter( 'site_transient_update_plugins', array( $this, 'update' ) );
            add_action( 'upgrader_process_complete', array( $this, 'purge' ), 10, 2 );

        }

        public function request(){

            $remote = get_transient( $this->cache_key );

            if( false === $remote || ! $this->cache_allowed ) {

                $remote = wp_remote_get(
                    UPDATE_PLUGIN_URL.'buildr.json',
                    array(
                        'timeout' => 10,
                        'headers' => array(
                            'Accept' => 'application/json'
                        )
                    )
                );

                if(
                    is_wp_error( $remote )
                    || 200 !== wp_remote_retrieve_response_code( $remote )
                    || empty( wp_remote_retrieve_body( $remote ) )
                ) {
                    return false;
                }

                set_transient( $this->cache_key, $remote, DAY_IN_SECONDS );

            }

            $remote = json_decode( wp_remote_retrieve_body( $remote ) );

            return $remote;

        }


        function info( $res, $action, $args ) {

            // print_r( $action );
            // print_r( $args );

            // do nothing if you're not getting plugin information right now
            if( 'plugin_information' !== $action ) {
                return false;
            }

            // do nothing if it is not our plugin
            if( $this->plugin_slug !== $args->slug ) {
                return false;
            }

            // get updates
            $remote = $this->request();

            if( ! $remote ) {
                return false;
            }

            $res = new stdClass();

            $res->name = $remote->name;
            $res->slug = $remote->slug;
            $res->version = $remote->version;
            $res->tested = $remote->tested;
            $res->requires = $remote->requires;
            $res->author = $remote->author;
            $res->author_profile = $remote->author_profile;
            $res->download_link = $remote->download_url;
            $res->trunk = $remote->download_url;
            $res->requires_php = $remote->requires_php;
            $res->last_updated = $remote->last_updated;

            $res->sections = array(
                'description' => $remote->sections->description,
                'installation' => $remote->sections->installation,
                'changelog' => $remote->sections->changelog
            );

            if( ! empty( $remote->banners ) ) {
                $res->banners = array(
                    'low' => $remote->banners->low,
                    'high' => $remote->banners->high
                );
            }

            return $res;

        }

        public function update( $transient ) {

            if ( empty($transient->checked ) ) {
                return $transient;
            }

            $remote = $this->request();

            if(
                $remote
                && version_compare( $this->version, $remote->version, '<' )
                && version_compare( $remote->requires, get_bloginfo( 'version' ), '<' )
                && version_compare( $remote->requires_php, PHP_VERSION, '<' )
            ) {
                $res = new stdClass();
                $res->slug = $this->plugin_slug;
                $res->plugin = plugin_basename( __FILE__ ); // misha-update-plugin/misha-update-plugin.php
                $res->new_version = $remote->version;
                $res->tested = $remote->tested;
                $res->package = $remote->download_url;

                $transient->response[ $res->plugin ] = $res;

        }

            return $transient;

        }

        public function purge(){

            if (
                $this->cache_allowed
                && 'update' === $options['action']
                && 'plugin' === $options[ 'type' ]
            ) {
                // just clean the cache when new plugin version is installed
                delete_transient( $this->cache_key );
            }

        }


    }

    new updateChecker();

}

function insertValueAtPosition($arr, $insertedArray, $position) {
    $i = 0;
    $new_array=[];
    foreach ($arr as $key => $value) {
        if ($i == $position) {
            foreach ($insertedArray as $ikey => $ivalue) {
                $new_array[$ikey] = $ivalue;
            }
        }
        $new_array[$key] = $value;
        $i++;
    }
    return $new_array;
}


add_action( 'admin_init', 'main_add_edit_link_filters' );

function main_add_edit_link_filters() {
    add_filter( 'page_row_actions', 'main_add_edit_link', 10, 2 );
}

function main_add_edit_link( $actions, $post ) {

        $post_link = get_permalink( $post->ID ).'?kitz_builder=enable';
        $edit_action = array(
            'divi2' => sprintf(
                '<a href="%s" aria-label="%s">%s</a>',
                esc_url( $post_link ),
                esc_attr(
                    sprintf(
                        __( 'Edit “%s” in Divi', 'et_builder' ),
                        esc_url( $post_link )
                    )
                ),
                esc_html__( 'Edit With Buildr', 'et_builder' )
            ),
        );

        $actions = array_merge( $actions, $edit_action );
        //print_r($actions);exit;
        return $actions;
}