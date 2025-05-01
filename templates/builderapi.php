<?php
class Builderapi {

    public function __construct() {
        add_action('rest_api_init', array($this, 'globally_header_footer'));
        add_action('rest_api_init', array($this, 'login_api_hooks'));
        add_action('rest_api_init', array($this, 'create_page_hooks'));

        //Page Content
        add_action('rest_api_init', array($this, 'insert_page_hooks'));

        //Trash Page
        add_action('rest_api_init', array($this, 'delete_page_hooks'));

        //Page Content
        add_action('rest_api_init', array($this, 'edit_page_hooks'));

        //Update Page API
        add_action('rest_api_init', array($this, 'update_page_hooks'));

        //Update Page API
        add_action('rest_api_init', array($this, 'default_page_hooks'));

        //Permalink Retrive API
        add_action('rest_api_init', array($this, 'permalink_page_hooks'));
        
        //Pages Permalink API
        add_action('rest_api_init', array($this, 'page_permalinks'));
        
        add_action('rest_api_init', array($this, 'update_blog'));
		
		  //Update Page title API
        add_action('rest_api_init', array($this, 'update_page_title_hooks'));

        //Sync Pages Array
        add_action('rest_api_init', array($this, 'sync_all_pages'));

        //Sync Pages Array
        add_action('rest_api_init', array($this, 'duplicate_page'));

        add_action('rest_api_init', array($this, 'sitefavicon_set'));
    }



    // Update site favicon API
    function sitefavicon_set() {
        @register_rest_route(
            'builder-page', '/update-favicon/', 
            array(
                'methods' => 'POST',
                'callback' => array($this, 'sitefavicon_setaction'),
            )
        );
    }

    function sitefavicon_setaction($request){
        if(isset($_FILES['file'])){

            $file_name = "icon_".uniqid().".png";
            $file_temp = $_FILES['file']['tmp_name'];

            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents( $file_temp );
            $filename = basename( $file_name );
            $filetype = wp_check_filetype($file_name);
            $filename = time().'.'.$filetype['ext'];

            if ( wp_mkdir_p( $upload_dir['path'] ) ) {
                $file = $upload_dir['path'] . '/' . $filename;
            }
            else {
                $file = $upload_dir['basedir'] . '/' . $filename;
            }

            file_put_contents( $file, $image_data );
            $wp_filetype = wp_check_filetype( $filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name( $filename ),
                'post_content' => '',
                'post_status' => 'inherit',
                'post_author' =>1
            );

            $attach_id = wp_insert_attachment( $attachment, $file );
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
            wp_update_attachment_metadata( $attach_id, $attach_data );
            update_option('site_icon',$attach_id);
            if(!empty($attach_id)){
                echo 'success';die;
            }else{
                echo 'fail';die;
            }

        }
    }




    // Update Page API
    function duplicate_page() {
        @register_rest_route(
            'builder-page', '/duplicate/', array(
                    'methods' => 'POST',
                    'callback' => array($this, 'builder_page_duplicate'),
            )
        );
    }
    
    function builder_page_duplicate($request) {

        global $wpdb;
        $page_id = $request["server_page_id"];        

        if ($page_id != "") {
            $tb_name = $wpdb->prefix . 'posts';
            $page_query = "SELECT * FROM $tb_name where ID='$page_id'";
            $pages_result = $wpdb->get_row($page_query);
            
            $page_title=$pages_result->post_title;
            $page_content=$pages_result->post_content;


            $create_page = array(
                'post_title' => $page_title." Copy",
                'post_status' => 'publish',
                'post_author' => 1,
                'post_content'=> $page_content,
                'post_type' => 'page'
            );
            $pid = wp_insert_post($create_page);

            $result = array(
                "code" => 200,
                "page_id" => $pid,
                "message" => "Dulicate $page_title page created successfully",
            );
        } else {
            $result = array(
                "code" => 202,
                "message" => "Failed to create duplicate $page_title page"                
            );
        }
        echo json_encode($result);
        die();
    }



    // Update Page API
    function sync_all_pages() {
        register_rest_route(
                'builder-page', '/sync/', array(
            'methods' => 'POST',
            'callback' => array($this, 'sync_page_data_array'),
                )
        );
    }
    
    function sync_page_data_array($request) {

        global $wpdb;
        $sync_page = $request["sync_page"];        

        if ($sync_page == "yes") {
            $tb_name = $wpdb->prefix . 'posts';
            $page_query = "SELECT * FROM $tb_name where post_type='page' and post_status='publish'";
            $pages_result = $wpdb->get_results($page_query);
            
            $page_array=array();

            foreach($pages_result as $mypages){
                $page_id= $mypages->ID;
                $permalink_url= get_the_permalink($page_id);
                $page_array[]=array(
                    'ID'=> $page_id,
                    'post_title'=> $mypages->post_title,
                    'post_slug'=> $mypages->post_name,
                    'page_url' => $permalink_url
                );
            }

            $permalink_url = get_the_permalink($page_id);
            $result = array(
                "code" => 200,
                "page_data" => $page_array,
                "message" => "Page sync successfully",
            );
        } else {
            $result = array(
                "code" => 202,
                "message" => "Failed to page sync from app hosted website"                
            );
        }
        echo json_encode($result);
    }


    
	// Update Page API
    function update_page_title_hooks() {
        register_rest_route(
                'builder-page', '/update_content_title/', array(
            'methods' => 'POST',
            'callback' => array($this, 'sync_update_page_title'),
                )
        );
    }
    
    function sync_update_page_title($request) {
        global $wpdb;
        $page_id = $request["server_page_id"];
        $project_title = $request["project_title"];

        if ($page_id != "") {
            $tb_name = $wpdb->prefix . 'posts';
            $wpdb->update($tb_name, array('post_title' => $project_title), array('ID' => $page_id));

            $permalink_url = get_the_permalink($page_id);
            $result = array(
                "code" => 200,
                "page_link" => $permalink_url,
                "message" => "Page Updated Successfully",
                'page_id' => $page_id
            );
        } else {
            $result = array(
                "code" => 202,
                "message" => "Failed to update page data to reference website by API",
                'content' => $content
            );
        }
        echo json_encode($result);
    }
	
    function update_blog(){
        register_rest_route(
            'bloginfo', '/update/', array(
                'methods' => 'POST',
                'callback' => array($this, 'update_bloginfo'),
            )
        );
    }
    
    
    function update_bloginfo($request) {
        global $wpdb;
        $bloginfo = $request["server_bloginfo"];

        if ($bloginfo != "") {
            $tb_name = $wpdb->prefix . 'options';
            $wpdb->update($tb_name, array('option_value' => $bloginfo), array('option_name' => "blogname"));            
            $result = array(
                "code" => 200,
                "message" => "Blog info update Successfully"
            );
        } else {
            $result = array(
                "code" => 202,
                "message" => "Failed to update blog info with API"                
            );
        }
        echo json_encode($result);
    }
    
    
    
    
    
    function page_permalinks(){
        register_rest_route(
            'pages', '/getpermalinks/', array(
                'methods' => 'POST',
                'callback' => array($this, 'result_page_permalinks'),
            )
        );
    }
    
    function result_page_permalinks($request){
        $page_ids=$request['pages_permalinks'];
        if(isset($page_ids) && !empty($page_ids)){
            $page_permalinks=array();
            foreach ($page_ids as $page_id):
                $get_permalinks=get_the_permalink($page_id);
                $page_permalinks[]=array(
                    $page_id => $get_permalinks
                );                
            endforeach;            
            
            $result=array(
                "code" => 200,
                "page_permalink"=> $page_permalinks,
                "message" => "Permalink retrive successfully"                
            );
        } else {
            $result=array(
              "code" => 202,
              "message" => "Failed to get response from permalinks"                
            );
        }
        echo json_encode($result);
        die();
    }
    
    

    // Create Divi Library API
    function globally_header_footer() {
        register_rest_route(
            'globally', '/header_footer/', array(
                'methods' => 'POST',
                'callback' => array($this, 'sync_assign_globally'),
            )
        );
    }

    function sync_assign_globally($request) {
        global $wpdb;
        $page_name = $request["post_title"];
        $page_content = $request['post_content'];
        $page_slug = $request['post_slug'];
        $user_id = $request['user_id'];
        $type = $request['type'];
        $server_page_id=$request['server_pageid'];
        update_post_meta($server_page_id, 'page_referesh', 'yes');

        if ($type == "assign_header") {
            $post_type = 'et_header_layout';
            $meta_name = '_et_header_layout_id';
            $meta_enable = '_et_header_layout_enabled';
            //$msg = "$page_name header";
            $msg = "Header";
            $guid = site_url() . "/?post_type=et_header_layout&p=";
        } else {
            $post_type = 'et_footer_layout';
            $meta_name = '_et_footer_layout_id';
            //$msg = "$page_name footer";
            $msg = "Footer";
            $meta_enable = '_et_footer_layout_enabled';
            $guid = site_url() . "/?post_type=et_footer_layout&p=";
        }

        global $wpdb;

        $theme_builder_query = $wpdb->get_row("SELECT post_name, ID FROM {$wpdb->prefix}posts WHERE post_name = 'theme-builder'", 'ARRAY_A');
        if (null === $theme_builder_query) {
            $create_post = array(
                'post_content' => '',
                'post_title' => 'Theme Builder',
                'post_status' => 'publish',
                'post_author' => $user_id,
                'post_type' => 'et_theme_builder'
            );
            $theme_builder_id = wp_insert_post($create_post);
        } else {
            $theme_builder_id = $theme_builder_query['ID'];
        }


        $default_template_query = $wpdb->get_row("SELECT post_name, ID FROM {$wpdb->prefix}posts WHERE post_name = 'default-website-template'", 'ARRAY_A');

        if (null === $default_template_query) {
            $create_post = array(
                'post_content' => '',
                'post_title' => 'Default Website Template',
                'post_status' => 'publish',
                'post_author' => $user_id,
                'post_type' => 'et_template'
            );
            $default_template_id = wp_insert_post($create_post);
        } else {
            $default_template_id = $default_template_query['ID'];
        }

        update_post_meta($theme_builder_id, '_et_template', $default_template_id);
        update_post_meta($default_template_id, '_et_default', 1);
        update_post_meta($default_template_id, $meta_enable, 1);
        update_post_meta($default_template_id, '_et_enabled', 1);
        update_post_meta($default_template_id, '_et_body_layout_enabled', 1);


        $main_query = "SELECT post_name, ID FROM {$wpdb->prefix}posts WHERE post_type = '$post_type'";
        $page_row = $wpdb->get_row($main_query, 'ARRAY_A');
        if (null === $page_row) {            
            
            $tb_name = $wpdb->prefix . 'posts'; 
            $create_post = array(                
                'post_title' => $page_name,
                'post_status' => 'publish',
                'post_author' => $user_id,
                'post_type' => $post_type
            );
            $wpdb->insert($tb_name, $create_post);
            $pid = $wpdb->insert_id;
            
            
            
            //$pid = wp_insert_post($create_post);

            $update_page = array(
                'ID' => $pid,
                'guid' => $guid . $pid
            );
            wp_update_post($update_page);

            update_post_meta($default_template_id, $meta_name, $pid);

            update_post_meta($pid, '_et_pb_use_builder', 'on');
            update_post_meta($pid, '_et_pb_show_page_creation', 'on');
            update_post_meta($pid, '_et_pb_built_for_post_type', 'on');
            
            $wpdb->update($tb_name, array('post_content' => $page_content), array('ID' => $pid));
            $result = array(
                "code" => 200,
                "message" => "$msg set globally for all pages"
            );
        } else {
            
            $page_id = $page_row['ID'];
//            $update_page = array(
//                'ID' => $page_id,
//                'post_title' => $page_name,
//                'post_content' => $page_content,
//            );
//            wp_update_post($update_page);
            $tb_name = $wpdb->prefix . 'posts';            
            $wpdb->update($tb_name, array('post_content' => $page_content), array('ID' => $page_id));

            update_post_meta($default_template_id, $meta_name, $page_id);
            update_post_meta($page_id, '_et_pb_use_builder', 'on');
            update_post_meta($page_id, '_et_pb_show_page_creation', 'on');
            update_post_meta($page_id, '_et_pb_built_for_post_type', 'on');
            $result = array(
                "code" => 200,
                "message" => "$msg updated globally for all pages"
            );
        }


        echo json_encode($result);
    }

    //Login User By API
    public function login_api_hooks() {
        register_rest_route(
                'builder-auth', '/login/', array(
            'methods' => 'POST',
            'callback' => array($this, 'builderlogin')
                )
        );
    }

    //Login API CAll Back Function
    public function builderlogin($request) {
        $creds = array();
        $creds['user_login'] = $request["username"];
        $creds['user_password'] = $request["password"];
        $creds['remember'] = true;
        $user = wp_signon($creds, false);

        if (is_wp_error($user))
            echo $user->get_error_message();

        return $user;
    }

    //insert_page_hooks
    function insert_page_hooks() {
        register_rest_route(
                'builder-page', '/insert/', array(
            'methods' => 'POST',
            'callback' => array($this, 'syn_insert_page'),
                )
        );
    }

    function syn_insert_page($request) {
        $page_content = $request['project_content'];
        $page_name = $request["project_title"];
        $user_id = $request['user_id'];

        $create_page = array(
            'post_content' => $page_content,
            'post_title' => $page_name,
            'post_status' => 'publish',
            'post_author' => $user_id,
            'post_type' => 'page'
        );

        $pid = wp_insert_post($create_page);
        update_post_meta($page_id, 'page_referesh', "yes");
        update_post_meta($pid, '_et_pb_page_layout', 'et_no_sidebar');
        update_post_meta($pid, '_et_pb_use_builder', 'on');

        $result = array(
            "code" => 200,
            "message" => "Page Created Successfully",
            'page_id' => $pid,
            'content' => $content
        );
        echo json_encode($result);
    }

    // Create Page API
    function create_page_hooks() {
        register_rest_route(
                'builder-page', '/sync_page/', array(
            'methods' => 'POST',
            'callback' => array($this, 'sync_create_page'),
                )
        );
    }

    function sync_create_page($request) {
        $page_content = $request['project_content'];

        global $wpdb;
        $page_row = $wpdb->get_row("SELECT post_name, ID FROM {$wpdb->prefix}posts WHERE post_name = '$page_slug'", 'ARRAY_A');
        if (null === $page_row) {
            $page_name = $request["project_title"];
            $page_slug = $request['project_slug'];
            $user_id = $request['server_userid'];
             $main_server_page_id = $request['main_server_page_id'];


            $create_page = array(
                'post_content' => $page_content,
                'post_title' => $page_name,
                'post_status' => 'publish',
                'post_author' => $user_id,
                'post_type' => 'page'
            );
            $pid = wp_insert_post($create_page);
            update_post_meta($pid, 'page_referesh', "yes");
            update_post_meta($pid, 'main_server_page_id', $main_server_page_id);
            
            update_post_meta($pid, '_et_pb_page_layout', 'et_no_sidebar');
            update_post_meta($pid, '_et_pb_use_builder', 'on');
            $result = array(
                "code" => 200,
                "message" => "Page Created Successfully",
                'page_id' => $pid,
                'content' => $content
            );
        } else {
            $page_id = $page_row['ID'];
            $update_page = array(
                'ID' => $page_id,
                'post_content' => $page_content,
            );
            wp_update_post($update_page);

            update_post_meta($page_id, '_et_pb_page_layout', 'et_no_sidebar');
            update_post_meta($page_id, '_et_pb_use_builder', 'on');

            $result = array(
                "code" => 200,
                "message" => "Page Updated Successfully",
                'page_id' => $page_id,
                'content' => $content
            );
        }
        echo json_encode($result);
    }

    // Delete Page API
    function delete_page_hooks() {
        register_rest_route(
                'builder-page', '/delete/', array(
            'methods' => 'POST',
            'callback' => array($this, 'sync_delete_page'),
                )
        );
    }

    function sync_delete_page($request) {
        $page_id = $request["server_page_id"];

        if ($page_id != "") {
            wp_delete_post($page_id);
            $result = array(
                "code" => 200,
                "message" => "Page Deleted Successfully",
                'content' => $content
            );
        } else {
            $result = array(
                "code" => 202,
                "message" => "Failed to delete page remotely, You can delete from wordpress administrator account",
                'content' => $content
            );
        }
        echo json_encode($result);
    }

    // Edit Page API Get Content
    function edit_page_hooks() {
        register_rest_route(
                'builder-page', '/content/', array(
            'methods' => 'POST',
            'callback' => array($this, 'sync_edit_page'),
                )
        );
    }

    function sync_edit_page($request) {
        global $wpdb;
        $page_id = $request["server_pageid"];

        if ($page_id != "") {
            $conn_site = $wpdb->prefix . 'posts';
            $project_query = "SELECT * FROM $conn_site where ID='$page_id' order by id desc limit 1";
            $con_details = $wpdb->get_row($project_query);

            $output = $con_details->post_content;

            $result = array(
                "code" => 200,
                "message" => "Page sync Successfully",
                'content' => $output
            );
        } else {
            $result = array(
                "code" => 202,
                "message" => "Failed to sync page data from reference website",
                'content' => $content
            );
        }
        echo json_encode($result);
    }

    // Update Page API
    function update_page_hooks() {
        register_rest_route(
                'builder-page', '/update_content/', array(
            'methods' => 'POST',
            'callback' => array($this, 'sync_update_page'),
                )
        );
    }

    function sync_update_page($request) {
        global $wpdb;
        $page_id = $request["server_page_id"];
        $content = $request["project_content"];

        if ($page_id != "") {
            update_post_meta($page_id, 'page_referesh', "yes");
            
            $tb_name = $wpdb->prefix . 'posts';
            $wpdb->update($tb_name, array('post_content' => $content), array('ID' => $page_id));

            /*
              $update_page = array(
              'ID'           => $page_id,
              'post_content' => $content,
              );
              wp_update_post($update_page); */
            $utils = ET_Core_Data_Utils::instance();

            do_action('et_update_post', $page_id);
            apply_filters('et_fb_ajax_save_verification_result', $content);
            
            
            /*  UPDATE DIVI OPTIONS  */
            $options=get_option('et_divi', true);
            $arr_replace=array("et_pb_static_css_file"=>"off", "et_enable_classic_editor"=>"on");
            $basket = array_replace($options, $arr_replace);
            update_option("et_divi", $basket);
            /* END UPDATE DIVI OPTIONS */

            update_post_meta($page_id, '_et_pb_page_layout', 'et_no_sidebar');
            update_post_meta($page_id, '_et_pb_use_builder', 'on');
            
            
            
            $permalink_url = get_the_permalink($page_id);
            $result = array(
                "code" => 200,
                "page_link" => $permalink_url,
                "message" => "Page Updated Successfully",
                'page_id' => $page_id
            );
        } else {
            $result = array(
                "code" => 202,
                "message" => "Failed to update page data to reference website by API",
                'content' => $content
            );
        }
        echo json_encode($result);
    }

    // Update Page API
    function permalink_page_hooks() {
        register_rest_route(
                'builder-page', '/permalink/', array(
            'methods' => 'POST',
            'callback' => array($this, 'retrive_permalink'),
                )
        );
    }

    function retrive_permalink($request) {
        global $wpdb;
        $page_id = $request["server_page_id"];

        if ($page_id != "") {
            $project_link = get_the_permalink($page_id);
            $result = array(
                "code" => 200,
                "message" => "Page Updated Successfully",
                'website_link' => $project_link,
                'page_id' => $page_id
            );
        } else {
            $result = array(
                "code" => 202,
                "message" => "Failed to update page data to reference website by API"
            );
        }
        echo json_encode($result);
    }

    // API for set by default Home Page
    function default_page_hooks() {
        register_rest_route(
                'builder-page', '/homepage/', array(
            'methods' => 'POST',
            'callback' => array($this, 'sync_set_homepage'),
                )
        );
    }

    function sync_set_homepage($request) {
        global $wpdb;
        $page_id = $request["server_pageid"];

        if ($page_id != "") {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $page_id);
            $result = array(
                "code" => 200,
                "message" => "Home page set successfully",
                'page_id' => $page_id
            );
        } else {
            $result = array(
                "code" => 202,
                'page_id' => $page_id,
                "message" => "Failed to set homepage for reference website by API"
            );
        }
        echo json_encode($result);
        die();
    }

}

$builderapi = new Builderapi();
