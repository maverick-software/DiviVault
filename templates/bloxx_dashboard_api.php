<?php

class Bloxx_dashboard_api {

    public function __construct() {
        //Enable/Disable Page meta ready for bloxx builder
        add_action("wp_ajax_bloxx_update_metabox", array($this, "bloxx_update_metabox"));
        add_action("wp_ajax_nopriv_bloxx_update_metabox", array($this, "bloxx_update_metabox"));


        //Swich Page Meta for enable bloxx builder to next page
        add_action("wp_ajax_bloxx_switch_metabox", array($this, "bloxx_switch_metabox"));
        add_action("wp_ajax_nopriv_bloxx_switch_metabox", array($this, "bloxx_switch_metabox"));

        //check page section type premium/free
        add_action("wp_ajax_bloxx_check_frontend_page_section_type_ajax", array($this, "bloxx_check_frontend_page_section_type_ajax"));
        add_action("wp_ajax_nopriv_bloxx_check_frontend_page_section_type_ajax", array($this, "bloxx_check_frontend_page_section_type_ajax"));

        add_action("wp_head", array($this, "bloxx_wp_head_add_html"));

        
        
        add_action("wp_ajax_saveproject", array($this, "saveproject"));
        add_action("wp_ajax_nopriv_saveproject", array($this, "saveproject"));
        
        
        add_action("wp_ajax_et_builder_load_css", array($this, "et_builder_load_css"));
        add_action("wp_ajax_nopriv_et_builder_load_css", array($this, "et_builder_load_css"));
        
        add_action("wp_ajax_headfooter_assign", array($this, "headfooter_assign"));
        add_action("wp_ajax_nopriv_headfooter_assign", array($this, "headfooter_assign"));



        add_action("wp_ajax_bloxx_createpage", array($this, "bloxx_createpage"));
        add_action("wp_ajax_nopriv_bloxx_createpage", array($this, "bloxx_createpage"));
        
        
        add_action("wp_ajax_neo_assets", array($this, "neo_assets"));
        add_action("wp_ajax_nopriv_neo_assets", array($this, "neo_assets"));



        add_action("wp_ajax_neo_cat_industry", array($this, "neo_cat_industry"));
        add_action("wp_ajax_nopriv_neo_cat_industry", array($this, "neo_cat_industry"));

        add_action("wp_ajax_savedropbox", array($this, "savedropbox"));
        add_action("wp_ajax_nopriv_savedropbox", array($this, "savedropbox"));

        //Send API when page is published
       // add_action('transition_post_status', array($this, 'send_notification'), 10, 3); 
    }

    public function neo_section_save(){
        extract($_REQUEST);
        $post = get_the_title($pageID);
        $remove_farward_slash = str_replace('\\', '', $section_content);
        echo $remove_farward_slash;
        die();
    }

    public function neo_cat_industry(){
        extract($_REQUEST);
        $user_email= get_option('builder_username', true);
        $user_id= get_option('bloxx_user_id', true);
        $curl_url = kitz_apiurl."wp-json/neo_directory/assets_cats";                
        $neo_cloud_cats = array(
            'neo_type'  => $neo_type,
            'user_id'  => $user_id
        );

        $neo_cats = json_encode($neo_cloud_cats);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $curl_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $neo_cats,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        echo $response;
        die();
    }


    public function neo_assets(){
        extract($_REQUEST);

        $user_email= get_option('builder_username', true);
        $user_id= get_option('bloxx_user_id', true);

        if($neo_type=="layout"){
            $page_title=get_the_title($builder_prj_id);
            $neo_save_directory = array(
                'page_title' => $page_title,
                'user_email' => $user_email,
                'user_id'  => $user_id,
                'neo_content' => $json_content,
                'neo_type'  => $neo_type,
                'neo_catID' => $catID,
                'neo_indID' => $indID,
            );
        } else {
            $page_title= $section_title;
            $neo_save_directory = array(
                'page_title' => $page_title,
                'user_email' => $user_email,
                'user_id'  => $user_id,
                'neo_content' => $json_content,
                'neo_type'  => $neo_type
            );
        }
     

        $curl_url = kitz_apiurl."wp-json/neo_directory/save_assets";                
        
        
        $neo_dir_json = json_encode($neo_save_directory);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $curl_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $neo_dir_json,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        echo json_encode($response);
        die();

    }

    public function bloxx_createpage(){
        extract($_REQUEST);
        $user_id = get_current_user_id();
        $create_page = array(
            'post_content' => "",
            'post_title' => $pnm,
            'post_status' => 'publish',
            'post_author' => $user_id,
            'post_type' => 'page'
        );

        $pid = wp_insert_post($create_page);
        update_post_meta($pid, '_et_pb_page_layout', 'et_no_sidebar');
        update_post_meta($pid, '_et_pb_use_builder', 'on');

        $page_permalink=get_the_permalink($pid);

        $enable_bloxx=$page_permalink."?kitz_builder=enable";
        $result = array(
            "code" => 200,
            "message" => "$pnm Page published successfully",
            'page_link' => $enable_bloxx
        );
        echo json_encode($result);
        die();
    }

    public function send_notification($new_status, $old_status, $post ){
        if ( $new_status == 'publish' && $old_status != 'publish' ) {
            $builder_connect="no";
            if(get_option('bloxxbuilder_connect')!=""){
                $builder_connect = get_option('bloxxbuilder_connect');
            }

            if ($builder_connect == "yes") {
                $server_id=$post->ID;
                $server_title=$post->post_title;
                $server_content=$post->post_content;
                $bloxx_termid= get_option('bloxx_term_id');
                $bloxx_userid= get_option('bloxx_user_id');

                $curl_url = kitz_apiurl."wp-json/bloxx-page/insert";                
                $builder_page_array = array(
                    'server_page_id' => $server_id,
                    'server_title'  => $server_title,
                    'project_content' => $server_content,
                    'bloxx_term'    => $bloxx_termid,
                    'bloxx_user'    => $bloxx_userid
                );

                $bloxx_page_json = json_encode($builder_page_array);
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $curl_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $bloxx_page_json,
                    CURLOPT_HTTPHEADER => array(
                        "cache-control: no-cache",
                        "content-type: application/json"
                    ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
            }
        }
    }



    

    // ak code start

    function bloxx_wp_head_add_html(){

$user = get_user_by('id', get_current_user_id());
$user_email = $user->user_email;
        ?>
        <style type="text/css">
            a.the_plan_button_client_popup{
                background: #ba0ea4;
                color: #fff;
                padding: 10px 15px;
                border-radius: 5px;
                display: inline-block;
                margin-top: 15px;
            }
        </style>
            <input type="hidden" id="get_user_id_bloxx_client" value="<?php echo $user_email; ?>">
        <?php
    }

    public function bloxx_check_frontend_page_section_type_ajax() {
        extract($_REQUEST);
       // update_post_meta($post_id, '_et_pb_page_layout', 'et_no_sidebar');
       // update_post_meta($post_id, '_et_pb_use_builder', 'on');
        $get_user_email = $_REQUEST['user_id'];

        


        $section_id  = $_REQUEST['section_id'];
        //update_user_meta($user_id, 'show_admin_bar_front', 'false');
        // echo 'user_id=>'.$user_id;
   


        $curl_url = kitz_apiurl."wp-json/bloxx-user/check_usertype";                
        $builder_page_array = array(
            'user_email' => $get_user_email,
            'section_id'  => $section_id
        );

        $bloxx_page_json = json_encode($builder_page_array);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $curl_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $bloxx_page_json,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);


        // echo '<pre>';
        // print_r($response);
        // echo '</pre>';

        $get_host_response = json_decode($response);

        //die('stop client api');


        // echo '<pre>';
        // print_r($get_host_response);
        // echo '</pre>';

       // die('stop client api');
       if ($get_host_response->usertype == "free" && $get_host_response->sectiontype =="Premium") {
            $result = array(
                'code' => 200,
                'message' => "Bloxx section is Premium"
            );
        } else {
            $result = array(
                'code' => 202,
                'message' => "Bloxx section is Free"
            );
        }        
        echo json_encode($result);
        die();
    }
    // ak code end

    public function bloxx_switch_metabox() {
        extract($_REQUEST);
        update_post_meta($post_id, '_et_pb_page_layout', 'et_no_sidebar');
        update_post_meta($post_id, '_et_pb_use_builder', 'on');
        $user_id = get_current_user_id();
        //update_user_meta($user_id, 'show_admin_bar_front', 'false');

        if ($old_page != "" && $post_id !="") {
            //Disable Builder for old page 
            update_post_meta($old_page, 'neo_builder', 'disable');
            update_post_meta($old_page, '_wp_page_template', 'default');
            
            
            //Enable Builder for new page
            update_post_meta($post_id, 'neo_builder', 'enable');
            //update_post_meta($post_id, '_wp_page_template', 'bloxx_call_template.php');
            
            
            $result = array(
                'code' => 200,
                'message' => "Kitzpro builder switched successfully"
            );
        } else {
            $result = array(
                'code' => 202,
                'message' => "Failed to switch Kitzpro builder"
            );
        }        
        echo json_encode($result);
        die();
    }
    
    

    public function bloxx_update_metabox() {
        extract($_REQUEST);
        update_post_meta($post_id, '_et_pb_page_layout', 'et_no_sidebar');
        update_post_meta($post_id, '_et_pb_use_builder', 'on');
        $user_id = get_current_user_id();
        $admin_bar_front = get_user_meta($user_id, 'show_admin_bar_front', true);
        update_user_meta($user_id, 'adminbar_usersetting', $admin_bar_front);

        if ($meta_type == "enable") {
            update_user_meta($user_id, 'show_admin_bar_front', false);
            update_post_meta($post_id, 'neo_builder', 'enable');
            $result = array(
                'code' => 200,
                'message' => "Kitzpro builder ready to launch"
            );
        } else if ($meta_type == "disable") {
            $usersetting=get_user_meta($user_id, "adminbar_usersetting", true);
            update_user_meta($user_id, 'show_admin_bar_front', $usersetting);
            update_post_meta($post_id, 'neo_builder', 'disable');
            update_post_meta($post_id, '_wp_page_template', 'default');
            $result = array(
                'code' => 200,
                'message' => "Kitzpro builder exit successfully"
            );
        } else {
            $result = array(
                'code' => 202,
                'message' => "Failed to load Kitzpro builder"
            );
        }
        echo json_encode($result);
        die();
    }
    
    
    
    
    //Save Page Code Here
    public function saveproject() {
        global $wpdb;
        if (isset($_POST['json_content']) && !empty($_POST['json_content'])) {
            $project_nm = $_REQUEST['builder_prj_title'];
            $project_id = $_REQUEST['builder_prj_id'];
            $project_title= get_the_title($project_id);
            
            $rm_cache= WP_CONTENT_DIR."/et-cache/$project_id";
            exec("rm -rf $rm_cache");
            
            $action_to_perform = $_REQUEST['action_to_perform'];
            $json_content = $_POST['json_content'];
            $ajax_content = "";
            foreach ($json_content as $drop_content):
                $ajax_content .= $drop_content;                
            endforeach;

            $remove_farward_slash = str_replace('\\', '', $ajax_content);
            
            
            $my_post = array(
                'ID' => $project_id,
                'post_content' => $remove_farward_slash,
            );
            wp_update_post($my_post);
            
            update_post_meta($project_id, 'page_referesh', 'yes');
            
            $result = array(
                'code' => 200,
                'project_id' => $pid,
                'message' => "$project_title page updated successfully"
            );
            
        } else {
            $result = array(
                'code' => 202,
                'message' => 'Please select layout before create save project'
            );
        }

        echo json_encode($result);
        die();
    }
    


    function savedropbox(){
        extract($_REQUEST);
        db_refresh_token();
        if(!get_option('kitz_dropbox', true)){
            $dropbox= "disable";
            $result=array(
                "code"=> 202,
                "message"=> "Dropbox is disable, Please enable now for continue"
            );
        } else {
            $dropbox= get_option('kitz_dropbox', true);
            $dropbox_token_detail= get_option('dropbox_token_detail');
            $access_token= $dropbox_token_detail['access_token'];

            if($save_type=="layout"){
                $project_title= get_the_title($builder_prj_id);
                $ajax_content = "";
                foreach ($json_content as $drop_content):
                    $ajax_content .= $drop_content;                
                endforeach;
                //$remove_farward_slash = str_replace('\\', '', $ajax_content);
                $post = get_post($builder_prj_id); 
                $page_slug = $post->post_name;
                $filename= $page_slug."_".time().".json";
                $file_path= kitz_path."dropbox/layouts/$filename";
                $drop_type="kitzbuilder/layouts";
            } else {
                $ajax_content= $json_content;
                //Section name get in the variable of $builder_prj_id
                $filename= $builder_prj_id."_".time().".json";
                $file_path= kitz_path."dropbox/sections/$filename";
                $drop_type="kitzbuilder/sections";
            }


            $save_file = fopen($file_path,"wb");
            fwrite($save_file, $ajax_content);
            fclose($save_file);


            $fp = fopen($file_path, 'rb');
            $size = filesize($file_path);

            $cheaders = array(
                'Authorization: Bearer '.$access_token,
                'Content-Type: application/octet-stream',
                'Dropbox-API-Arg: {"path":"/'.$drop_type."/".$filename.'", "mode":"add"}'
            );

            // echo "<pre>";
            // print_r($cheaders);
            // die();

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

            $decode_resp=json_decode($response, true);

            // echo "<pre>";
            // print_r($decode_resp);
            

            if(@$decode_resp['error']['.tag']=="expired_access_token"){
                db_refresh_token();
            } else {
                $result=array(
                    "code"=> 200,
                    "message"=> "$filename has been saved to dropbox"
                );
            }
        }

        echo json_encode($result);
        die();
    }
    
    
    public function et_builder_load_css() {
        extract($_REQUEST);
        $time = time();

        $link_url = get_the_permalink($page_id);

        $shot_nm = "project_$time.png";   //Demo Variable
        $version = $link_url . "?ver=" . $time;

        //$scriptpath = "node " . siteblox_path . "/runpage_nodescript.js {$version} {$shot_nm}";

        //exec($scriptpath, $output);
        //$myJSON = $output;
        //$node_result = implode($myJSON);

        if ($version != "") {
            //$homepage = file_get_contents($cache_css);
            $page_html = file_get_contents($version);
            $resp = array(
                "page_html" => $page_html,
                "code" => 200,
                "message" => "Page css loaded successfully"
            );
        } else {
            $resp = array(
                "code" => 202,
                "message" => "Failed to load page css"
            );
        }

        echo json_encode($resp);
        die();
    }
    
    
    public function headfooter_assign(){
        extract($_REQUEST);
        global $wpdb;   
        $user_id = get_current_user_id();

        $page_content = str_replace('\\', '', $page_content);
        
        $wp_posts = $wpdb->prefix . 'posts';
        $pages_query = "SELECT ID FROM $wp_posts where post_type='page' and post_status='publish'";
        $pages_result= $wpdb->get_results($pages_query);

        foreach($pages_result as $allpages):
            $page_ids=$allpages->ID;
            update_post_meta($page_ids, 'page_referesh', 'yes');
        endforeach;
        
        
        
        if ($assign_type == "assign_header") {
            $post_type = 'et_header_layout';
            $meta_name = '_et_header_layout_id';
            $meta_enable = '_et_header_layout_enabled';
            $page_name= "header_assign";
            //$msg = "$page_name header";
            $msg = "Header";
            $guid = site_url() . "/?post_type=et_header_layout&p=";
        } else {
            $post_type = 'et_footer_layout';
            $meta_name = '_et_footer_layout_id';
            $page_name= "footer_assign";
            //$msg = "$page_name footer";
            $msg = "Footer";
            $meta_enable = '_et_footer_layout_enabled';
            $guid = site_url() . "/?post_type=et_footer_layout&p=";
        }
        
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

        $tb_name = $wpdb->prefix . 'posts'; 

        $page_row = $wpdb->get_row($main_query, 'ARRAY_A');
        if (null === $page_row) {            
            $create_post = array(                
                'post_title' => $page_name,
                'post_content' => $page_content,
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
            
            $result = array(
                "code" => 200,
                "message" => "$msg set globally for all pages"
            );
        } else {
            $page_id = $page_row['ID'];            

            //Delete Before Insert
            $post_name=$page_id."-revision-v1";
            $wpdb->delete( $tb_name, array( 'id' => $page_id ) );
            $wpdb->delete( $tb_name, array( 'post_name' => $post_name ) );  //Also Delete revision


            $create_post = array(                
                'post_title' => $page_name,
                'post_content' => $page_content,
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
            

            
            $result = array(
                "code" => 200,
                "message" => "$msg updated globally for all pages"
            );
        }
        echo json_encode($result);
        die();
    }

}

$bloxx_dashboard = new Bloxx_dashboard_api();