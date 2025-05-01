<?php

class Bloxx_core {

    public function __construct() {
        add_action('wp_footer', array($this, 'footer_css'));
        add_action('admin_menu', array($this, 'builder_menu'));

        add_filter( 'plugin_action_links_kitz-pro-builder/kitz.php', array($this, 'kitz_setting_link'));

        add_action('admin_bar_menu', array($this, 'enable_neo_builder'), 9999);

        add_action('wp_enqueue_scripts', array($this, 'plugin_css_jsscripts'));

        //add_action('admin_enqueue_scripts', array($this, 'plugin_css_jsscripts'));

        add_action('admin_enqueue_scripts', array($this, 'admin_css_jsscripts'));

        //add_action('admin_notices', array($this, 'check_divi_theme'));
        //add_action('admin_notices', array($this, 'check_bloxxkey'), 10);

        //CSS for Admin Bar
        add_action('admin_head', array($this, 'adminbar_bloxxbuilder_css'));
        add_action('wp_head', array($this, 'adminbar_bloxxbuilder_css'));

        //add_action('wp_head', array($this, 'load_template'));

        add_filter('template_include', array($this, 'load_template'));

        add_action('wp_head', array($this, "reload_cssjs"));

        //Admin check API
        add_action("wp_ajax_siteblox_key_saved", array($this, "siteblox_key_saved"));
        add_action("wp_ajax_nopriv_siteblox_key_saved", array($this, "siteblox_key_saved"));


        //Admin DropBox
        add_action("wp_ajax_kitz_dropbox", array($this, "kitz_dropbox"));
        add_action("wp_ajax_nopriv_kitz_dropbox", array($this, "kitz_dropbox"));



        //Admin DropBox
        add_action("wp_ajax_dropbox_create_folder", array($this, "dropbox_create_folder"));
        add_action("wp_ajax_nopriv_dropbox_create_folder", array($this, "dropbox_create_folder"));

        //Admin DropBox
        add_action("wp_ajax_kitzdropbox_upload", array($this, "kitzdropbox_upload"));
        add_action("wp_ajax_nopriv_kitzdropbox_upload", array($this, "kitzdropbox_upload"));

        //Admin check API for Non Bloxx User
       // add_action("wp_ajax_siteblox_key_saved_simple", array($this, "siteblox_key_saved_simple"));
       // add_action("wp_ajax_nopriv_siteblox_key_saved_simple", array($this, "siteblox_key_saved_simple"));


        add_action( 'init', array($this, "is_bloxxpage_open"));  
        
        add_action('init', array($this, 'autologin'));
    }


    function footer_css(){
        if(isset($_GET['kitzdb'])){
            ?>
            <style>
                header, footer {
                    display: none !important;
                }
            </style>
            <?php
        }
    }



    function dropbox_create_folder(){
        extract($_REQUEST);
        if(!get_option('kitz_dropbox', true)){
            $result=array(
                "code"=> 202,
                "message"=> "You need to connect your drop box for continue"
            );
        } else {
            // echo $section_title;
            // die();
            $listing_folder=array(
                "autorename"=> false,
                "path"=> "/kitzbuilder/".$kitz_parent_type."/".$section_title
            );

            // echo "<pre>";
            // print_r($listing_folder);
            // die();

            $dropbox_token_detail= get_option('dropbox_token_detail');
            $access_token= $dropbox_token_detail['access_token'];
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.dropboxapi.com/2/files/create_folder_v2',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($listing_folder),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$access_token
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            
            // echo $response;
            // die();
            $resp_decode=json_decode($response, true);
            if(isset($resp_decode['error_summary']) && !empty($resp_decode['error_summary'])){
                $result=array(
                    "code"=> 202,
                    "message"=> "May be folder already created on Dropbox"
                );
            } else {
                $entry_folder= "/kitzbuilder/".$kitz_parent_type."/";
                $curl1 = curl_init();
                $curl_folder= array(
                    "include_deleted"=> false,
                    "include_has_explicit_shared_members"=> false,
                    "include_media_info"=> false,
                    "include_mounted_folders"=> true,
                    "include_non_downloadable_files"=> true,
                    "path"=> $entry_folder,
                    "recursive"=> false
                );
                curl_setopt_array($curl1, array(
                    CURLOPT_URL => 'https://api.dropboxapi.com/2/files/list_folder',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>json_encode($curl_folder),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: Bearer '.$access_token
                    ),
                ));

                $resp= curl_exec($curl1);
                $get_direct= json_decode($resp, true);
                $data=array();
                if(isset($get_direct['error_summary']) && !empty($get_direct['error_summary'])){
                    $data=array();
                } else {
                    foreach ($get_direct['entries'] as $folders) {
                        $data[]=array(
                            "subfolder"=> $folders['name']
                        );
                    }
                }

                $result=array(
                    "code"=> 200,
                    "subdirectory"=> $data,
                    "message"=> "$section_title folder created under $kitz_parent_type parent category in Dropbox."
                );
            }
        }
        echo json_encode($result);
        die();
    }



    function kitzdropbox_upload(){
        // ini_set('display_errors', 1);
        // ini_set('display_startup_errors', 1);
        // error_reporting(E_ALL);
        extract($_REQUEST);
        db_refresh_token_function();
        $tmp_nm= $_FILES['json_url']['tmp_name'];
        $fn= $_FILES["json_url"]['name'];
        $fn_str=str_replace("-", "", $fn);      

        $file_nm= round(microtime(true));
        $filename = $file_nm.".json";
        $filename_img = $file_nm.".png";

        // echo $file_nm;
        // die();

        $plugin_path= kitz_path."/dropbox/json/";
        if (move_uploaded_file($tmp_nm, $plugin_path.$filename)) {
            $file_url= kitz_url."dropbox/json/". $filename;
            $request = wp_remote_get( $file_url);
            $remote_json = wp_remote_retrieve_body( $request, true );
            $remote_data = json_decode( $remote_json, true, JSON_UNESCAPED_SLASHES);
            $remote_data_renew = json_decode( $remote_json, true, JSON_UNESCAPED_SLASHES);
            $remote_json_data= $remote_data['data'];
            $j=1;

            foreach($remote_json_data as $remote_data):
                if($j==1) {
                    if(isset($remote_data['post_content'])){                            
                        $remote_content= $remote_data['post_content'];
                    } else {                            
                        $remote_content= $remote_data;
                    }
                    //Create
                    $new_post = array(
                        'post_title' => $section_title,
                        'post_content' => $remote_content,
                        'post_status' => 'publish',
                        'post_type' => 'page'
                    );
                    $pid = wp_insert_post($new_post);                       
                    update_post_meta( $pid, '_et_pb_page_layout', 'et_no_sidebar');
                    update_post_meta( $pid, '_et_pb_use_builder', 'on');
                    $section_permalink= get_the_permalink($pid)."?kitzdb=yes";
                    
                    //Generating image Curl response
                    $image_process= $this->appkitz_createimage($section_permalink);
                    wp_delete_post($pid);
                    wp_trash_post( $pid );
                    if($image_process['code']==200){
                        //Save Image Code from URL
                        $generate_url = $image_process['image_url'];
                        $kitz_imgstorage_path = kitz_path."/dropbox/images/".$filename_img;
                        file_put_contents($kitz_imgstorage_path, file_get_contents($generate_url));
                        

                        //File Move to dropbox
                        $file_path= kitz_path."dropbox/dropbox_json/$filename";
                        $save_file = fopen($file_path,"wb");
                        fwrite($save_file, $remote_content);
                        fclose($save_file); 

                        $drop_path_filename="/kitzbuilder/".$kitz."/".$kitz_subdirectory."/".$filename;
                        $upload_return= $this->dropbox_movefiles($file_path, $drop_path_filename, $drop_path_filename);
                        // echo "<pre>";
                        // print_r($upload_return);
                        // echo "<br/>";
                        // echo $upload_return['error']['.tag'];
                        // die();

                        if(@$upload_return['error']['.tag']=="path"){
                            $result=array(
                                'code' => 202,  
                                'message' => "May be you deleted the folder structure"
                            );
                        } else {
                            //Image Move to dropbox
                            $drop_path_img="/kitzbuilder/".$kitz."/".$kitz_subdirectory."/".$filename_img;
                            $this->dropbox_movefiles($kitz_imgstorage_path, $drop_path_img, $filename_img);

                            unlink($plugin_path.$filename);
                            unlink($file_path);
                            unlink($kitz_imgstorage_path);
                            $result=array(
                                'code' => 200,  
                                'message' => "Upload json data has been moved to drop box successfully"
                            );
                        }
                    } else {
                        $result=array(
                            'code' => 202,  
                            'message' => $image_process['message']
                        );
                    }
                }
            endforeach;
        }

        echo json_encode($result);
        die();
    }


    function appkitz_createimage($page_link){
        $curl_img = curl_init();
        $post_field=array(
            "kitz_url"=> $page_link
        );

        curl_setopt_array($curl_img, array(
            CURLOPT_URL => 'https://app.divikitz.com/wp-json/divikitz/screenshot',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($post_field),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl_img);
        curl_close($curl_img);
        
        $kitz_resp= json_decode($response, true);
        return $kitz_resp;
    }


    function dropbox_movefiles($file_path, $drop_path, $trigger_nm){
        $dropbox_token_detail= get_option('dropbox_token_detail');
        $access_token= $dropbox_token_detail['access_token'];
        
        $fp = fopen($file_path, 'rb');
        $size = filesize($file_path);

        $cheaders = array('Authorization: Bearer '.$access_token,
                  'Content-Type: application/octet-stream',
                  'Dropbox-API-Arg: {"path":"'.$drop_path.'", "mode":"add"}');
       

        $ch = curl_init('https://content.dropboxapi.com/2/files/upload');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $cheaders);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_INFILESIZE, $size);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        curl_close($ch);
        fclose($fp);
        
        // echo $response;
        // die();

        $upload_data=json_decode($response, true);
        return $upload_data;
    }
    
    
    function autologin(){
        if(isset($_REQUEST['bloxx_auth_token'])){
            global $wpdb;
            //if ( !is_user_logged_in()){ 
                $auth_email= base64_decode($_REQUEST['bloxx_auth_token']);
                $user_details= get_user_by("email", $auth_email);
                $get_user=$user_details->data;
                $userid= $get_user->ID;
                $user_email= $get_user->user_email;
                $user_pass= $get_user->user_pass;
                $conn_site = $wpdb->prefix.'users';
                $login_query= "select * from $conn_site where user_login='$user_email' and user_pass='$user_pass'";

                $my_query= $wpdb->get_results($login_query);

                $count_data=  count( $my_query );
                if($count_data!=0){

                    wp_set_current_user($userid); // set the current wp user
                    wp_set_auth_cookie($userid);
                    wp_redirect( admin_url() );
                }
            // }else{
            //     echo "string";die;
            // }
        }
    }



    public function kitz_setting_link($links){
        // Build and escape the URL.
        $url = esc_url( add_query_arg(
            'page',
            'kitz_settings',
            get_admin_url() . 'admin.php'
        ) );

        // Create the link.
        $settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
        // Adds the link to the end of the array.
        array_push($links, $settings_link);
        return $links;
    }



    function is_bloxxpage_open() {
        $user_id = get_current_user_id();
        if(isset($_REQUEST['kitz_builder'])){
            update_user_meta($user_id, 'show_admin_bar_front', 'false');
        } else {
            update_user_meta($user_id, 'show_admin_bar_front', 'true');
        }
    }

    public function builder_menu() {
        add_menu_page('Kitz Pro', 'Kitz Pro', 'manage_options', 'kitz_settings', array($this, 'bloxx_connect'), kitz_url.'images/wpmenu_icon.png');

        add_submenu_page('kitz_settings','Kitz Drop Box', 'Kitz Drop Box','manage_options','kitz_dropbox', array($this, 'kitzdropbox'),10);
        
        // if ( is_plugin_active( 'WritrAI/Writr.php' ) ) {
        //    add_submenu_page('bloxx-connect','Bloxx Buildr', 'Bloxx Buildr','manage_options','bloxx_buildr_page', array($this,'bloxx_buildr_page_callback'),125);
        //     add_submenu_page('bloxx-connect','Writr Sidebar', 'Writr Sidebar','manage_options','custompage','my_custom_menu_page', 'dashicons-welcome-widgets-menus', 120 );

        //    add_submenu_page( 'bloxx-connect', 'WritrAI','WritrAI', 'manage_options', 'custompage', 'my_custom_menu_page', 'dashicons-welcome-widgets-menus', '' );
        // }
        

        // add_submenu_page('bloxx-connect','Bloxx WritrAI', 'Bloxx WritrAI','manage_options','bloxx_writr_page', array($this,'bloxx_writr_page_callback'));

        // add_submenu_page('bloxx-connect','Bloxx Pixr', 'Bloxx Pixr','manage_options','bloxx_pixr_page', array($this,'bloxx_pixr_page_callback'));



    } 

    public function bloxx_buildr_page_callback(){
        ?>
        <div class="wrap">
            <h1>Buildr Page</h1>
            <p>This is buildr plugin page</p>
        </div>

        <?php
    }

   
    public function bloxx_pixr_page_callback(){
        ?>
        <div class="wrap">
            <h1>Pixr Page</h1>
            <p>This is pixr plugin page</p>
        </div>

        <?php
    }


    public function bloxx_connect() {
        ob_start();
        include( kitz_path . 'admin/templates/settings-page.php' );
        $content = ob_get_contents();
        ob_get_clean();
        echo $content;
    }


    public function kitzdropbox(){
        ob_start();
        include( kitz_path . 'admin/templates/kitz_dropbox.php' );
        $content = ob_get_contents();
        ob_get_clean();
        echo $content;
    }

    public function reload_cssjs() {
        global $wpdb;
        global $wp_query;
        @$page_id = $wp_query->post->ID;

        //Enable Admin Bar
        $user_id = get_current_user_id();
        /*if(isset($_REQUEST['neo_builder'])){            
            update_user_meta($user_id, 'show_admin_bar_front', 'false');
        } else {
            update_user_meta($user_id, 'show_admin_bar_front', 'true');
        }*/

        if ($page_id != "" && is_user_logged_in()) {
            @$page_refresh = get_post_meta($page_id, 'page_referesh', true);



            if (@$page_refresh == "yes" && is_user_logged_in()) {

                $posts = $wpdb->prefix . 'posts';
                $page_query = "SELECT * FROM $posts where ID='$page_id' limit 1";
                $page_data = $wpdb->get_row($page_query);
                $page_content = $page_data->post_content;

                $update = wp_update_post(
                        array(
                            'ID' => $page_id,
                            'post_content' => $page_content,
                            'post_status' => "publish",
                        )
                );
                update_post_meta($page_id, "page_referesh", "no");
                ?>
                <script>
                    window.location.href = "";
                </script>
                <?php
            }
        }
        return true;
    }

    public function load_template($template) {
        global $wp_admin_bar, $wp_the_query;
        $post_id = get_the_ID();
        $bloxx_enable = get_post_meta($post_id, 'neo_builder', true);

        if (is_user_logged_in() && isset($_REQUEST['kitz_builder'])) {
            $temp_path = kitz_path . "bloxx_call_template.php";
            return $temp_path;
            //load_template($temp_path);
        } else {
            return $template;
        }
    }

    function enable_neo_builder() {
        global $wp_admin_bar, $wp_the_query;
        $post_id = get_the_ID();

        $is_divi_library = 'et_pb_layout' === get_post_type($post_id);
        $page_url = $is_divi_library ? get_edit_post_link($post_id) : get_permalink($post_id);

        if ( is_plugin_active( 'buildr/neo_builder.php' ) ) {
            $buildr_topbar_menu = '<li id="wp-admin-buildr_topbar_menu"><a class="neo-item neo-item-buildr" href="'.get_the_permalink($post_id).'?kitz_builder=enable">Buildr</a></li>';
        }else{
            $buildr_topbar_menu = '';
        }

        if ( is_plugin_active( 'WritrAI/Writr.php' ) ) {
            $writr_topbar_menu = '<li id="wp-admin-writr_topbar_menu"><a class="neo-item neo-item-writr" href="javascript:void(0)">WritrAI</a></li>';
        }else{
            $writr_topbar_menu = '';
        }
        

      

        //if (!is_admin() && !isset($_REQUEST['et_fb'])) {
        if (!is_admin()) {
            if (isset($_REQUEST['kitz_builder'])) {
                $wp_admin_bar->add_menu(
                        array(
                            'id' => 'exit-bloxx-builder',
                            'name' => $post_id,
                            'title' => esc_html__('Exit Kitz', 'neo_builder'),
                            'href' => esc_url($page_url),
                            'meta' => array(
                                'title' => $post_id
                            )
                        )
                );
            } else {
                $use_neo_builder_url = add_query_arg(
                        array('kitz_builder' => "enable"), $page_url
                );
                $wp_admin_bar->add_menu(
                        array(
                            'id' => 'et-bloxx-builder',
                            'class' => $post_id,
                            'href' => esc_url($use_neo_builder_url),
                            'title' => esc_html__('Kitz', 'neo_builder'),
                             
                            'class' => 'neo_menupop'
                        )
                );
            }
        }
        return;
    }

    function adminbar_bloxxbuilder_css() {


        if (is_admin_bar_showing()) {
        //if (is_admin_bar_showing() && !is_admin()) {
            ?>
           <!--  <a class="ab-item" aria-haspopup="true" href="https://dev.projectneo.ai/wp-admin/post-new.php">
                <span class="ab-icon" aria-hidden="true"></span>
                <span class="ab-label">New</span>
            </a>
            <div class="ab-sub-wrapper">
                <ul id="wp-admin-bar-new-content-default" class="ab-submenu">
                    <li id="wp-admin-bar-new-post"><a class="ab-item" href="https://dev.projectneo.ai/wp-admin/post-new.php">Post</a></li>
                    <li id="wp-admin-bar-new-media"><a class="ab-item" href="https://dev.projectneo.ai/wp-admin/media-new.php">Media</a></li>
                    <li id="wp-admin-bar-new-page"><a class="ab-item" href="https://dev.projectneo.ai/wp-admin/post-new.php?post_type=page">Page</a></li>
                </ul>
            </div> -->

            <style type="text/css">
                .neo-topbar-submenu{
                    display: none;
                }
                #wp-admin-bar-et-bloxx-builder:hover .neo-topbar-submenu{
                    display: block;
                    background: #1d2327;
                    position: absolute;
                    width: 125px;

                }
                .neo-topbar-submenu li {
                    display: block;
                    width: 100% !important;
                    float: left !important;
                }
                .neo-topbar-submenu li a{
                    color: #fff;
                }
                li#wp-admin-bar-et-bloxx-builder a, li#wp-admin-bar-exit-bloxx-builder a {
                    background: url('<?php echo kitz_url; ?>images/logoicon.png?v=<?= time(); ?>') no-repeat left center !important;
                    background-size: 20px auto !important;
                    font-weight: 600;
                    padding: 0 12px 0 30px !important;
                    position: relative;
                }

                li#wp-admin-bar-et-bloxx-builder a:hover, li#wp-admin-bar-exit-bloxx-builder a:hover {
                    background: #231942 url('<?php echo kitz_url; ?>images/logoicon.png?v=<?= time(); ?>') no-repeat left center !important;
                    background-size: 20px auto !important;
                    color: #fff !important;
                }
                

                li#wp-admin-bar-et-bloxx-builder ul.neo-topbar-submenu li a{
                    background: none !important;
                }
            </style>

            <script>
                jQuery(function ($) {

                    $("body").on("click", "li a.neo-item-writr", function (event) {
                        $('#checkebr').trigger('click');
                    });

                    $("body").on("click", "li#wp-admin-bar-et-bloxx-builder a", function (event) {
                        event.preventDefault();
                        var $this = $(this);
                        var page_id = $this.attr('title');
                        var meta_type = "enable";
                        update_bloxx_metas(page_id, meta_type, $this);
                    });

                    $("body").on("click", "li#wp-admin-bar-exit-bloxx-builder a", function (event) {
                        event.preventDefault();
                        var $this = $(this);
                        var page_id = $this.attr('title');
                        var meta_type = "disable";
                        update_bloxx_metas(page_id, meta_type, $this);
                    });


                    function update_bloxx_metas(page_id, meta_type, $this) {
                        var ajax_url = '<?php echo admin_url('admin-ajax.php') ?>';
                        $.ajax({
                            type: "POST",
                            url: ajax_url,
                            dataType: "json",
                            data: {
                                'action': 'bloxx_update_metabox',
                                'post_id': page_id,
                                'meta_type': meta_type
                            },
                            beforeSend: function () {
                               // $this.html('<i class="fa fa-spinner fa-spin"></i>');
                            },
                            success: function (resp) {
                                if (resp.code == 200) {
                                    window.location.href = $this.attr('href');
                                } else {
                                    alert(resp.message);
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    title: "Error!",
                                    text: "Please try again later",
                                    confirmButtonColor: '#000',
                                    icon: "error"
                                });
                            }
                        });
                    }
                });
            </script>
            <?php
        }
    }

    function plugin_css_jsscripts() {
        global $wp_query;
        @$page_id = $wp_query->post->ID;

        wp_dequeue_script('utils');
        wp_dequeue_script('moxiejs');
        //Datatable
        //jQuery UI 1.11.4
        wp_enqueue_script('jquery-ui', kitz_url . "jquery-ui/jquery-ui.min.js", array('jquery'), '', true);

        //jQuery UI Touch Punch 1.11.4
        wp_enqueue_script('jquery-uitouchpunch', kitz_url . "jquery-ui/jquery.ui.touch-punch.min.js", array('jquery'), '', true);


        //Sweet Alret
        wp_enqueue_script('sweer-alert', kitz_url . "js/sweetalert.min.js", array('jquery'), '', true);

        //Font Awesome
        wp_enqueue_style('font-awesome', kitz_url . "css/font-awesome.min.css");


        //Font Awesome
        wp_enqueue_style('all-awesome', kitz_url . "css/all.css");

        //Style css
        wp_enqueue_style('style-css', kitz_url . "css/style.css?v=" . time());



        

        // Pass ajax_url to script.js
        if (!isset($_GET['et_fb'])) {
            // JavaScript
             wp_enqueue_script('builderjs', kitz_url . 'js/script.js?v=' . time(), array(), '', true);
             
            $ajax_data=$this->basicApiext();

           // pre($ajax_data);
            
            wp_localize_script('builderjs', 'bloxx', array('ajax_url' => admin_url('admin-ajax.php')));
            wp_localize_script('builderjs', 'bloxxapi', $ajax_data);

            
        }
    }

    function admin_css_jsscripts() {
        wp_enqueue_style('bloxxbuilder-font-awesome', kitz_url . "css/font-awesome.min.css");
        wp_enqueue_style('bloxxbuilder-css', kitz_url . 'admin/assets/css/bloxx_admin.css?v=' . time());

        //Sweet Alret
        wp_enqueue_script('bloxxbuilder-sweer-alert', kitz_url . "js/sweetalert.min.js");

        //script.js
        wp_enqueue_script('bloxxbuilder-siteblox-script', kitz_url . 'admin/assets/js/bloxxscript.js?v=' . time());


        // Pass ajax_url to script.js
        if (!isset($_GET['et_fb'])) {
            $ajax_data=$this->basicApiext();
            
            wp_localize_script('bloxxbuilder-siteblox-script', 'bloxx', array('ajax_url' => admin_url('admin-ajax.php')));
            wp_localize_script('bloxxbuilder-siteblox-script', 'bloxxbuilder_admin', array('ajax_url' => admin_url('admin-ajax.php')));
            wp_localize_script('bloxxbuilder-siteblox-script', 'bloxxapi', $ajax_data);
        }
    }

    
    
    function basicApiext(){
        include_once(ABSPATH.'wp-admin/includes/plugin.php');
        $builderapi_url = get_option('bloxx_api_url', true);
        $builderapi_url_layouts = get_option('bloxx_api_url_layouts', true);

        $theme = wp_get_theme();
        $er = 0;
        $divi_type="activate";
        if ('Divi' == $theme->name) {               
            $er = 1;
            $divi_type="theme_activated";
        } else if ('Divi Child Theme' == $theme->name) {
            $divi_type="theme_activated";
            $er = 1;        
        } else if (is_plugin_active( 'divi-builder/divi-builder.php' ) ) {
            $divi_type="plugin_activated";
            $er = 1;        
        }

        // echo "<pre>";
        // print_r(get_option('kitz_dropbox'));
        // die();

        if(get_option('kitz_dropbox', true)!="disable" && get_option('kitz_dropbox', true)!=1){
            $dropbox="dropbox_enable";
        } else {
            $dropbox="dropbox_disable";
        }
        

        $ajax_data=array();
        
        if($er==0){
            $ajax_data= array(
                'builder_key' => "activate",
                'api_token' => "activate",
                'ajax_url' => "activate",
                'ajax_url_layouts'=> "activate",
                'key_url'=> $builderapi_url,
                'key_url_layouts' => $builderapi_url_layouts,
                'imageurl'=> kitz_url,
                'enable'=> $divi_type,
                'dropbox'=> $dropbox,
                'siteurl'=>site_url()
            );
        } else {
            $builder_connect="no";
            if(get_option('bloxxbuilder_connect')!=""){
                $builder_connect = get_option('bloxxbuilder_connect', true);
            }

            
            $builder_key= get_option('siteblox_key', true);
            $api_token= get_option('bloxx_api_token', true);

            $kitz_unauth_section_api=get_option('kitz_unauth_section_api', true);
            $kitz_unauth_layout_api=get_option('kitz_unauth_layout_api', true);

            $ajax_data= array(
                'ajax_url_layouts' => $builderapi_url_layouts,
                'ajax_url' => $builderapi_url,
                'kitz_unauth_section'=> $kitz_unauth_section_api,
                'kitz_unauth_layout'=> $kitz_unauth_layout_api,
                'builder_key' => $builder_key,
                'api_token' => $api_token,
                'key_url'=> $builderapi_url,
                'key_url_layouts' => $builderapi_url_layouts,
                'imageurl'=> kitz_url,
                'dropbox'=> $dropbox,
                'enable'=> $divi_type,
                'siteurl'=>site_url()
            );
            
        }
        return $ajax_data;
    }


    function check_divi_theme() {
        $theme = wp_get_theme();
        $er = 0;
        if ('Divi' == $theme->name) {
            $er = 0;
        } else if ('Divi Child Theme' == $theme->name) {
            $er = 0;            
        } else if ( is_plugin_active( 'divi-builder/divi-builder.php' ) ) {
            $er = 0;            
        } else {
            $er = 1;
        }

        if ($er == 1) {
            $error_message = "Please activate Divi theme or Divi Builder plugin to continue...  Bloxx Plugin";
            $this->error_message($error_message);
        }
    }

    // function check_bloxxkey(){
    //     $builder_key=get_option('builder_key');
    //     if(empty($builder_key) || $builder_key === false){
    //         $error_message = "Please enter API key before using the Neo's Builder";
    //         $this->error_message($error_message);
    //     }
    // }

    function error_message($error_message) {
        ?>
        <div class="updated error divi_builder">
            <p><?php esc_html_e($error_message); ?></p>
        </div>

        <?php
    }
    
    
    
    public function kitz_dropbox() {
        extract($_REQUEST);
        update_option('kitz_dropbox_detail', $_REQUEST);
        $result=array(
            "code"=> 200,
            "message"=> "Dropbox setting saved successfully"
        );
        echo json_encode($result);
        die();
    }

    
    //Check API Connection
    public function siteblox_key_saved() {
        extract($_REQUEST);
        $current_user = wp_get_current_user();
        $current_user_id = $current_user->ID;
        update_option('siteblox_key', $siteblox_key);
        $website_nm=get_option('blogname', true);
        $connect_data = array(
            'website_url' => $website_url,
            'server_userid' => $current_user_id,
            'siteblox_username' => trim($siteblox_username),
            'siteblox_key' => $siteblox_key,
            'website_nm' => $website_nm
        );

       // pre($connect_data);
       // die('stop client');
        $siteblox_json = json_encode($connect_data);

        if ($siteblox_status == "siteblox_connect") {
            $connect_url=kitz_apiurl."wp-json/siteblox-api/connect";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $connect_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $siteblox_json,
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);



            if ($err) {
                $result = array(
                    'code' => 202,
                    'message' => $err
                );
            } else {
                $siteblox_resp = json_decode($response, true);
                
                if ($siteblox_resp['code'] == 200) {
                    update_option('bloxx_api_url', $siteblox_resp['section_api']);
                    update_option('bloxx_api_url_layouts', $siteblox_resp['layout_api']);
                    update_option('bloxx_api_token', $siteblox_resp['api_token']);
                    update_option('builder_username', $siteblox_username);
                    update_option('builder_key', $siteblox_key);
                    update_option('bloxx_user_id', $siteblox_resp['user_id']);
                    update_option('bloxx_term_id', $siteblox_resp['term_id']);
                    update_option('bloxxbuilder_connect', 'yes');
                    update_option('bloxx_use_free_features', $response['bloxx_use_free_features']);
                }


                echo $response;
            }
        } else {
            $disconnect_url=kitz_apiurl."wp-json/siteblox-api/disconnect";
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $disconnect_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $siteblox_json,
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: application/json"
                ),
            ));

            $response = curl_exec($curl);
            
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                $result = array(
                    'code' => 202,
                    'message' => $err
                );
            } else {
                $siteblox_resp = json_decode($response, true);
                if ($siteblox_resp['code'] == 200) {
                    update_option('bloxx_use_free_features', $response['bloxx_use_free_features']);
                    update_option('bloxxbuilder_connect', 'no');
                    update_option('bloxx_api_url', "disconnect");
                    update_option('bloxx_api_url_layouts', "disconnect");
                    update_option('bloxx_api_token', "disconnect");
                    update_option('builder_username', '');
                    update_option('builder_key', '');
                }
                echo $response;
            }
        }
        die();
    }

}

$bloxx_core = new Bloxx_core();