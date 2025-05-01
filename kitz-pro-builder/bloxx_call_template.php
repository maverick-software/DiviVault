<?php
/*
 * Template Name: Bloxx Builder
 */

get_header();

$page_id = get_queried_object_id();

add_action('admin_head', 'my_custom_fonts');

function my_custom_fonts() {
  echo '<style>
  #wpadminbar {
      display: none;
    } 
  </style>';
}
?>

<style>
header {
    top: 56px;
    position: relative;
    z-index: 99;
    margin-left: 60px;
}
/*#category-page .topWrapmenu, #diviBuilder .topWrapmenu {
    top: 31px;
}

.left-aside{
    top: 13px;
}
*/
/*.left-category-aside {
    top: 25px;
}*/
#main-header, #main-footer {
    display: none;
}

.bloxx_et_builder #main-header {
    position: relative;
    z-index: 9;
}
.bloxx_et_builder #main-header .container {
    width: 87%;
}
.bloxx_et_builder #main-footer .container {
    width: 87%;
}
</style>

    


<div id="et-main-area" class="bloxx_et_builder">
    <header class="custom_header" id="main-header" style="top: 57px;">
        <div class="header_resp"></div>
    </header>

    <div id="main-content"> 
        <div class="container">
            <div id="content-area" class="clearfix">
                <div id="left-area" style="margin: 0; padding: 0;width: 100%;">
                    <!-- //sidebar  --> 
                    <?php while (have_posts()) : the_post(); ?>
                        <?php $post_id = get_the_id(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('et_pb_post'); ?> style="margin: 0;">    
                            <div class="entry-content">
                                <div class="contentWrapper inside" id="category-page">
                                    <div class="builder_desktop_sidebar">

                                        <style>
                                            .switch {
                                                position: relative;
                                                display: inline-block;
                                                width: 30px;
                                                height: 17px;
                                            }

                                            .switch input { 
                                                opacity: 0;
                                                width: 0;
                                                height: 0;
                                            }

                                            .slider {
                                                position: absolute;
                                                cursor: pointer;
                                                top: 0;
                                                left: -3px;
                                                right: 0;
                                                bottom: 0;
                                                background-color: #ccc;
                                                -webkit-transition: .4s;
                                                transition: .4s;
                                            }

                                            .slider:before {
                                                position: absolute;
                                                content: "";
                                                height: 13px;
                                                width: 13px;
                                                left: 0px;
                                                bottom: 1px;
                                                background-color: #930abc;
                                                -webkit-transition: .4s;
                                                transition: .4s;
                                                border: 1px solid #fff;
                                            }

                                            input:checked + .slider {
                                                background-color: #fff;
                                            }

                                            input:focus + .slider {
                                                box-shadow: 0 0 1px #2196F3;
                                            }

                                            input:checked + .slider:before {
                                                -webkit-transform: translateX(11px);
                                                -ms-transform: translateX(11px);
                                                transform: translateX(11px);
                                            }

                                            /* Rounded sliders */
                                            .slider.round {
                                                border-radius: 17px;
                                            }

                                            .slider.round:before {
                                                border-radius: 50%;
                                            }
                                        </style>


                                        <div class="left-aside">
                                            <ul class="left-list">
                                                <li>
                                                    <a href="javascript:void(0)" title="Site bloxx" class="left-list-img">
                                                        <img src="<?php echo kitz_url; ?>images/Sidebar-dash-icon.png" alt="Bloxx" width="50">
                                                    </a>        
                                                </li>

                                                <li class="switch-sidebar">
                                                    <a data-type="page" href="javascript:void(0)" title="Add New" data-img="<?php echo kitz_url; ?>images/pages.png" data-old-img="<?php echo kitz_url; ?>images/pages-actiavte.png">
                                                        <img src="<?php echo kitz_url; ?>images/pages-actiavte.png" alt="Upload">
                                                    </a>
                                                </li>

                                                <li class="open-sidebar-layouts">
                                                    <a data-type="layout"  href="javascript:void(0)" title="Add New Layout" data-img="<?php echo kitz_url; ?>images/layout.png" data-old-img="<?php echo kitz_url; ?>images/layout-activate.png">
                                                        <img src="<?php echo kitz_url; ?>images/layout-activate.png" alt="Upload">
                                                    </a>
                                                </li>

                                                <li class="open-sidebar">
                                                    <a data-type="section" href="javascript:void(0);" title="Add Section" data-img="<?php echo kitz_url; ?>images/assetsnew.png" data-old-img="<?php echo kitz_url; ?>images/asset-activate.png">
                                                        <img src="<?php echo kitz_url; ?>images/asset-activate.png" alt="Bloxx">
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>



                                        <!-- User Pages When click on project pancil icon -->

                                        <div class="left-category-aside" id="left_project">
                                            <div class="wrapCategoryMenu">
                                                <ul class="builder_categories websites_pages">                                                    
                                                    <h2 class="heading2 text-white">Pages</h2>
                                                    <?php
                                                    $user = wp_get_current_user();
                                                    $current_user_id = $user->ID;
                                                    $args = array(
                                                        'post_type' => 'page',
                                                        'sort_order' => 'asc',
                                                        'sort_column' => 'post_title',
                                                        'posts_per_page' => 10,
                                                        'post_status' => 'publish'
                                                    );

                                                    $query = new WP_Query($args);
                                                    ?>
                                                    <?php if ($query->have_posts()) { ?>
                                                        <?php while ($query->have_posts()) { ?>
                                                            <?php
                                                            $query->the_post();
                                                            $switchid = get_the_id();
                                                            $switch_title = get_the_title();
                                                            $switch_url = get_the_permalink()."?kitz_builder=enable";
                                                            ?>
                                                            <?php if ($switchid == $post_id) { ?>
                                                                <li>
                                                                    <a href="javascript:void(0)" class="current_active"><?php echo get_the_title(); ?></a>
                                                                </li>
                                                            <?php } else { ?>
                                                                <li>
                                                                    <a href="<?php echo $switch_url; ?>" data-id="<?= $switchid; ?>" data-nm="<?= $post_id ?>" class="other_pages"><?php echo get_the_title(); ?></a>
                                                                </li>
                                                            <?php } ?>
                                                        <?php } ?>
                                                    <?php } else { ?>

                                                        <li>
                                                            <a href="javascript:void(0)" class="builder_cats builder_cat_active">No Page Found</a>                                                              
                                                        </li>
                                                    <?php } ?>
                                                    <li class="builder_page user_action">
                                                        <a href="javascript:void(0)" class="addNew add_page_restriction" data-name="<?php echo $term_id; ?>" data-title="builder">Add Blank Page <i class="fa fa-plus"></i></a>
                                                    </li>
                                                    <?php wp_reset_postdata(); ?>
                                                </ul>
                                            </div>
                                        </div>

                                        <!-- End User Pages When click on project pancil icon -->




                                        <!-- Get Cats By Args Query -->
                                        <!-- End Get Cats by Args Query -->




                                        <!-- All Section Displayed while click on plus icon -->
                                        <div class="left-category-aside" id="leftCategorySidebar">
                                            <div class="wrapCategoryMenu">
                                                <h2 class="heading2 text-white">Sections</h2>

                                                <ul class="builder_categories">
                                                    <!-- Section Load through Ajax -->
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="sections_lists"><!-- Section lists --></div>


                                        <!-- End All Section Displayed while click on plus icon -->




                                        <!-- All Layout Displayed while click on plus icon -->
                                        <div class="left-category-aside" id="leftCategorySidebar_layouts">
                                            <div class="wrapCategoryMenu">
                                                <h2 class="heading2 text-white">Layouts</h2>
                                                <hr />
                                                <h2 class="heading2 text-white industry_h2_heading">Choose Industry</h2>
                                                <div class="layout_industries">
                                                    <!-- Load Layout Industries through Ajax -->
                                                </div>
                                                
                                                <ul class="builder_categories">
                                                    <!-- Layout Categories Load through Ajax -->
                                                </ul>
                                            </div>
                                        </div>

                                        <div class="layouts_lists"><!-- LAyout lists --></div>


                                        <!-- End All Layout Displayed while click on plus icon -->



                                    </div>


                                    <div class="wrapContent">
                                        <div class="topWrapmenu">
                                            <ul class="builder_bredcumbs">              
                                                <li><a href="javascript:void(0)"><?php echo the_title(); ?></a></li>
                                                <li class="modeOption">
                                                    <a class="move_2divi" href="<?php echo the_permalink(); ?>?et_fb=1&PageSpeed=off" title="Enable Divi Editor" id="<?= $post_id; ?>">
                                                        <span class="letter">D</span> Enable Divi Builder
                                                    </a>
                                                </li>
                                                <!-- <li><span>NewTestApp</span></li>    -->
                                            </ul>           

                                            <div class="see_global">
                                                Global Assets: <input type="checkbox" id="global_radio" /><label class="assets_global" for="global_radio">Assets</label>
                                            </div>

                                            <ul class="project_details_menu" id="slideNav"> 

                                            </ul>
                                            <ul class="headerButton">
                                                <li class="builder_layout_save">
                                                    <a href="javascript:void(0)" data-id="<?php echo $post_id; ?>" title="Save">
                                                        <img src="<?php echo kitz_url; ?>images/floppy-icon.png" alt="Save"> Save</a>
                                                </li>

                                                <li class="neo_save_layout">
                                                    <a href="javascript:void(0)" data-id="<?php echo $post_id; ?>" title="Save Layout">
                                                        <img src="<?php echo kitz_url; ?>images/floppy-icon.png" alt="Save"> Save Layout</a>
                                                </li>

                                                
<!--                                                <li class="builder_live_preview">
                                                    <a target="_blank" href="<?php echo the_permalink(); ?>" title="Preview">
                                                        <img src="<?php echo kitz_url; ?>images/view-icon.png" alt="Bloxx"> Preview</a>
                                                </li>-->
                                            </ul>
                                            <ul class="topMenuUser">
                                                <li class="builder_layout_exit">
                                                    <a href="<?php echo the_permalink(); ?>" data-id="<?= $post_id ?>" class="exit_builder" title="Exit builder">
                                                        <img src="<?php echo kitz_url; ?>images/doorway.png" alt="Close"> Exit</a>
                                                </li>
                                            </ul>
                                        </div>


                                        <div class="builder_create_template variation_desktop">
                                            <script>
                                                jQuery(function ($) {
                                                    var changed_array = [];
                                                    $(".builder_inner_dropable .card > .builder-dragpost").each(function () {
                                                        var get_content = $(this).attr('id')
                                                        changed_array.push(get_content);
                                                    });
                                                    $("#section_count_default").val(changed_array);
                                                });
                                            </script>
                                            

                                            <!-- Header Data -->
                                            <div class="header_resp">

                                            </div>
                                            <!-- End Header Data -->


                                            <!-- Body Dragable Data -->
                                            <div class="builder_inner_dropable connectedSortable ui-sortable">

                                                <?php $post_content = get_the_content(); ?>

                                                <?php if ($post_content == "") { ?>
                                                    <div class="dropable_area test">
                                                            <h1><span><i class="fas fa-expand-arrows-alt"></i></span>Drag & Drop <br> Sections</h1>                 
                                                    </div>

                                                <?php } else { ?>

                                                    <?php
                                                    $page_content = get_the_content();
                                                    $explode_content = explode("[et_pb_section", $page_content);
                                                    $pg = (rand(-10, -100));
                                                    $array_number=0;
                                                    foreach ($explode_content as $pg_content) {
                                                        if ($pg_content != "") {
                                                            $page_shortcode = "[et_pb_section" . $pg_content;
                                                            ?>

                                                            <div class="card">
                                                                <div class="action_btns">
                                                                    <a href="javascript:void(0)" class="builder_uparrow" id="<?php echo $pg; ?>">&#8593;</a>
                                                                    <a href="javascript:void(0)" class="builder_downarrow" id="<?php echo $pg; ?>">&#8595;</a>
                                                                    <a href="javascript:void(0)" class="save_section" id="<?= $array_number; ?>" title="Save Section" data-id="<?php echo $post_id; ?>" data-pgid="<?= $pg; ?>">
                                                                        <img src="<?php echo kitz_url; ?>images/floppy-icon.png" alt="Save section">
                                                                    </a>
                                                                    <a href="javascript:void(0)" class="builder_remove_layout" id="<?php echo $pg; ?>"><i class="far fa-trash-alt" aria-hidden="true"></i></a>
                                                                </div>

                                                                <div class="builder-dragpost builder_<?php echo $pg; ?>" id="<?php echo $pg; ?>" data-id='<?php echo $term_id; ?>'>
                                                                    <div class="builder_inner_area">                                
                                                                        <input type="hidden" class="builder_layout" value="<?php echo strip_tags(htmlspecialchars($page_shortcode)); ?>"/>
                                                                        <div class="show_clone_html">

                                                                            <?php if ( is_plugin_active( 'divi-builder/divi-builder.php' ) ) { ?>
                                                                                <div id="et-boc" class="et-boc">
                                                                                    <div id="et_builder_outer_content" class="et_builder_outer_content">
                                                                                        <div class="et-l et-l--post">
                                                                                            <div class="et_builder_inner_content et_pb_gutters3">
                                                                                                <?php echo do_shortcode("$page_shortcode"); ?>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            <?php } else { ?>
                                                                                <?php echo do_shortcode("$page_shortcode"); ?>
                                                                            <?php } ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <?php $pg++; ?>
                                                            <?php $array_number++; ?>
                                                        <?php } ?>
                                                    <?php } ?>
                                                <?php } ?>
                                            </div>

                                            <!-- End Body Dragable Data -->
                                        </div>
                                    </div>


                                    <!-- Footer Tab For Mobile -->
                                    <div id="footer-nav">
                                        <ul class="mob-collapsible" style="height:50px;">
                                            <li>
                                                <a href="javascript:void(0);" data-id="menu-mobile-menu" class="mob-togglebar"><i class="fa fa-bars"></i></a>
                                            </li>
                                        </ul>

                                        <ul class="mob-builder-menu" id="menu-mobile-menu" style="display:none;">
                                            <li class="builder_layout_new builder_page mob-switch-sidebar">
                                                <a href="javascript:void(0)" data-id="https://app.gobloxx.io/page-builder/?create=1" title="Add New">
                                                    <img src="<?php echo kitz_url; ?>images/page-new.png" alt="Upload"></a>
                                            </li>

                                            <li class="mob-open-sidebar">
                                                <a href="javascript:void(0);" title="Add Section">
                                                    <img src="<?php echo kitz_url; ?>images/add-new.png" alt="Bloxx">
                                                </a>
                                            </li>

                                            <li class="builder_export_json">
                                                <a href="javascript:void(0)" class="export_json" title="Export Json">
                                                    <img src="<?php echo kitz_url; ?>images/download-icon.png" alt="Download"></a>
                                                <a class="click_download" href="javascript:void(0)" download="" style="visibility: hidden; position: absolute;"><img src="<?php echo kitz_url; ?>images/download.png" alt="Bloxx" width="50"></a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0)" title="Switch to Divi editor" class="move_2divi" data-id="72315" data-href="https://app.gobloxx.io/customer_templates/home-2/?update=1&amp;et_fb=1&amp;PageSpeed=off&amp;pcat=734">
                                                    <img src="<?php echo kitz_url; ?>images/divi-icon.png">                                                    
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
									
									<div id="layoutModal" class="modal_layout">
										<div class="modal-header">
										  <span class="modal_close">&times;</span>
										  <h2></h2>
										</div>
                                        <!-- Modal content -->
                                        <div class="modal-content">
                                            <div class="modal-body">
                                                <!-- Layout Image POP UP BOX -->
                                            </div>
                                        </div>

                                    </div>
                                    <!-- Footer Tab For Mobile -->
                                </div>



                            </div> <!-- .et_post_meta_wrapper -->
                        </article> <!-- .et_pb_post -->

                    <?php endwhile; ?>

                </div> <!-- #left-area -->

            </div> <!-- #content-area -->
        </div> <!-- .container -->

    </div> <!-- #main-content -->
    
    
    <footer class="custom_footer" style="top: 57px;">
        <div class="footer_resp"></div>
    </footer>

</div>



<?php get_footer(); ?>

<script>
jQuery(function($){
    $("body").addClass("enable_neo_builder");
    if($("header").hasClass('et-l et-l--header')) {
        $(".bloxx_et_builder header .header_resp").hide();
        $(".bloxx_et_builder footer .footer_resp").hide();
    } else {
        var get_header=$("#main-header").html();
        var get_footer=$("#main-footer").html();
        $(".bloxx_et_builder #main-header").show();        
        $(".bloxx_et_builder header .header_resp").html(get_header);
        $(".bloxx_et_builder footer").attr('id', 'main-footer')
        $(".bloxx_et_builder footer .footer_resp").html(get_footer);
        $(".bloxx_et_builder .custom_footer").show();
    }
});
</script>