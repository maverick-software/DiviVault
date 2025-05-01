<?php

class Kitz_dropbox {

    public function __construct() {
        //When user clicks on Section then display all folder from dropbox
        add_action("wp_ajax_kitz_load_ajax_cats", array($this, "kitz_load_ajax_cats"));
        add_action("wp_ajax_nopriv_kitz_load_ajax_cats", array($this, "kitz_load_ajax_cats"));


        //When user dropbox folder and display all sections under selected folder
        add_action("wp_ajax_kitz_load_sections", array($this, "kitz_load_sections"));
        add_action("wp_ajax_nopriv_kitz_load_sections", array($this, "kitz_load_sections"));


        //When user clicks on Layout
        add_action("wp_ajax_load_ajax_industries", array($this, "load_ajax_industries"));
        add_action("wp_ajax_nopriv_load_ajax_industries", array($this, "load_ajax_industries"));
    }

    //Sections
    public function kitz_load_ajax_cats(){
        extract($_REQUEST);
        if(get_option('drop_api', true)!="" && get_option('drop_api', true)!=1){
            db_refresh_token_function();
            $dropbox= "enable";
            $dropbox_token_detail= get_option('dropbox_token_detail');
            $access_token= $dropbox_token_detail['access_token'];

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.dropboxapi.com/2/files/list_folder',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>'{
                    "include_deleted": false,
                    "path": "/kitzbuilder/Sections/",
                    "recursive": false
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$access_token
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            $get_directory= json_decode($response, true);

            if($get_directory['entries']){
                if(!empty($get_directory['entries'])){
                    foreach ($get_directory['entries'] as $folders) {
                        $folder_name= $folders['name'];
                        ?>
                        <li class="project_section" id="<?= $folder_name; ?>">
                            <a href="javascript:void(0)" class="builder_cats" id="<?= $folder_name; ?>"><?= $folder_name; ?></a>
                        </li>
                        <?php
                    }
                }
            } else {
                echo "Dropbox_not_found";
            }
        } else {
            echo "Dropbox_not_found";
        }
        die();
    }


    public function kitz_load_sections() {
        extract($_REQUEST);
        $post_field=array(
            "include_deleted"=> false,
            "path" => "/kitzbuilder/Sections/$dropbox_folder",
            "recursive"=> false
        );

        db_refresh_token_function();

        $dropbox_token_detail= get_option('dropbox_token_detail');
        $access_token= $dropbox_token_detail['access_token'];
        

        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.dropboxapi.com/2/files/list_folder',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($post_field),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$access_token
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $kitz_files= json_decode($response, true);
        

        $section_html="<section data-count_collection-section_ajax_load='$dropbox_folder' data-section_limit-section_ajax_load='5' class='builder_posts active_slide' id='cat_post_$dropbox_folder' style='display:none;'>";
        
        if(!empty($kitz_files['entries'])) {
            $j=0;
            foreach ($kitz_files['entries'] as $kdb_files) {
                $filename= $kdb_files['name'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $in_filepath="/kitzbuilder/Sections/$dropbox_folder/$filename";

                
                if($ext=="json") {
                    $path_lower= $folders['path_display'];
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://content.dropboxapi.com/2/files/download');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_POST, 1);

                    $headers = array();
                    $headers[] = 'Authorization: Bearer '.$access_token;
                    $headers[] = 'Content-Type:';
                    //$headers[] = 'Dropbox-Api-Arg: {\"path\":\"/kitzbuilder/Sections/Banner/1682088211.json\"}';
                    $headers[] = 'Dropbox-API-Arg: {"path":"' . $in_filepath . '"}';
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                    $json_data = curl_exec($ch);
                    curl_close($ch);

                    $json_image=explode(".", $in_filepath);
                    $json_image_path=$json_image[0].".png";


                    $path= array(
                        "path"=> $json_image_path
                    );

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://api.dropboxapi.com/2/files/get_temporary_link',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS =>json_encode($path),
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer '.$access_token
                        ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);
                    $thumb_data= json_decode($response, true);
                    
                    $thumb_image= $thumb_data['link'];


                    $section_html .='<div data-sectiontype="1" data-usertype="" data-useremail="" id="builder_inner_dragpost_104229" class="builder_inner_dragpost_sel builder_inner_dragpost connectedSortable">';
                    $section_html .='<div class="section_type is_free"><!-- <h3>Free</h3> --></div>';
                    $section_html .='<div class="card" data-sectiontype="1" data-usertype="">';
                    $section_html .='<div class="builder-dragpost builder-dragpost-sel builder-dragpost-sidebar" id="104229" data-id=""  data-sectiontype="1" data-usertype="">';
                    // $section_html .='<div class="action_btns" style="display:none;">';
                    // $section_html .='<a href="javascript:void(0)" class="builder_uparrow" id="104229">&#8593;</a>';
                    // $section_html .='<a href="javascript:void(0)" class="builder_downarrow" id="104229">&#8595;</a>';
                    // $section_html .='<a href="javascript:void(0)" class="builder_remove_layout" id="104229"><i class="far fa-trash-alt" aria-hidden="true"></i></a>';
                    // $section_html .='</div>';

                    $section_html .="<img class ='show_clone_img' src='$thumb_image' style='display:block;margin: auto;'>";
                    $section_html .="<input type='hidden' class='builder_layout' value='$json_data'/>";
                    $section_html .="<div class='show_clone_html' style='display: none;'></div>";
                    $section_html .="</div>";
                    $section_html .="</div>";
                    $section_html .="</div>";
                }
            }
            
            $section_html .="</section>";
            echo $section_html;
        } else {
            echo "Dropbox_not_found";
        }
        die();
    }


    //Layouts
    public function load_ajax_industries(){
        extract($_REQUEST);
        // echo "<pre>";
        // print_r($_REQUEST);
        echo "Dropbox_not_found";
        die();
    }

    

}

$kitz_dropbox = new Kitz_dropbox();