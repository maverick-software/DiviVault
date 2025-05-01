<?php
$builder_connect="no";
if(get_option('bloxxbuilder_connect')!=""){
    $builder_connect = get_option('bloxxbuilder_connect');
}

$siteblox_username = get_option('builder_username');
$builder_key = get_option('siteblox_key');
$bloxx_term_id = get_option('bloxx_term_id');

$dropbox_key= $dropbox_secret ="";


$dropbox_token_detail= get_option('dropbox_token_detail');

// echo "<pre>";
// print_r($dropbox_token_detail);
// echo "</pre>";

$kitz_dropbox_url = esc_url( add_query_arg(
    'page',
    'kitz_dropbox',
    get_admin_url() . 'admin.php'
) );



if(get_option('kitz_dropbox_detail')!=""){
	$dropbox_details= get_option('kitz_dropbox_detail');
	$dropbox_key= $dropbox_details['dropbox_key'];
	$dropbox_secret= $dropbox_details['dropbox_secret'];
}


if(isset($_REQUEST['dropBox_connect'])){
	if(get_option('dropbox', true)=="enable"){
		$dropbox_key= get_option('drop_api', true);
		$dropbox_redirect= get_option('kitz_drop_returnuri', true);
		$dropbox_allow_url= "https://www.dropbox.com/oauth2/authorize?client_id=$dropbox_key&token_access_type=offline&redirect_uri=$dropbox_redirect&response_type=code";
		?>
		<script>
			window.location.href="<?= $dropbox_allow_url; ?>";
		</script>
		<?php
		exit;
	} else {
		$resp_message="Please deactivate and activate plugin again for continue";
	}
}



if(isset($_REQUEST['dropBox_disconnect'])){
	update_option('kitz_dropbox', "disable");
}


if(!get_option('kitz_dropbox', true)){
	$dropbox= "disable";
} else {
	$dropbox= get_option('kitz_dropbox', true);
}

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.3/css/bootstrap-grid.min.css" integrity="sha512-q0LpKnEKG/pAf1qi1SAyX0lCNnrlJDjAvsyaygu07x8OF4CEOpQhBnYiFW6YDUnOOcyAEiEYlV4S9vEc6akTEw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<div id="maincontent" class="main-content">
	<div class="page-content">
		<div class="container-fluid">
			
			<?php 
			//if($bloxx_term_id){
				?>
				<div class="row">
						<div class="col-12">
							<div class="main-title">
								<h1>Hello from Team Divikitz! </h1>
								<p>If you have a premium plan then you can connect your account below here, if you do not have you can get one <a href="<?php echo kitz_apiurl; ?>plans/">here</a>. Other wise you can enjoy our free services.</p>
							</div>
						</div>
					</div>


					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-4 col-4">
							<div class="bloxx_box">
								<div class="blx_logo">
									<img src="<?php echo kitz_url; ?>/images/Divi-Webkitz-Logo.png?v=<?= time(); ?>" style="max-width: 175px;" />
									<h3>Access Premium Features</h3>
									<p>Each of our plugins makes the Divi experience better and faster for developers.</p>

									<form class="kitz_form" method="POST" id="siteblox_connectivity">
									    <section class="object-meta-data taxonomy-meta-data">
									        <table class="widefat fixed siteblox-table" cellspacing="0">
									            <tbody>

									               <?php 
									               $readonly="";
									               if ($builder_connect == "yes" &&  isset($siteblox_username) && $siteblox_username!='') {
									               		$readonly="readonly='readonly'";
									               }
									               ?>
									                <tr class="alternate">
									                    <td>
									                        <input name="website_url" type="hidden" value="<?php echo site_url(); ?>">
									                        <input name="siteblox_username" type="text"  id="siteblox_username" value="<?php
									                        if (isset($siteblox_username)) {
									                            echo $siteblox_username;
									                        }
									                        ?>" placeholder="Enter Account Email" <?= $readonly; ?>>
									                        <input name="siteblox_key" id="siteblox_key" type="text" value="<?php
									                        if (isset($builder_key)) {
									                            echo $builder_key;
									                        }
									                        ?>" placeholder="Enter API Key" <?= $readonly; ?> required>
									                        <input type="hidden" name="action" value="siteblox_key_saved">
									                    </td>
									                </tr>
									            </tbody>
									        </table>
									    </section>

									    <div class="submit-buttons">

									        <?php if ($builder_connect == "yes" &&  isset($siteblox_username) && $siteblox_username!='') { ?>
									            <input type="hidden"  id="siteblox_status" name="siteblox_status" value="siteblox_disconnect">
									            <button type="submit" id="save_connectivity" class="button kitz_btn">Disconnect</button>
									            
									        <?php } else { ?>
									            <input type="hidden" id="siteblox_status" name="siteblox_status" value="siteblox_connect">
									            <button type="submit" id="save_connectivity" class="button kitz_btn button-pro">Connect</button>
											<?php } ?>
									    </div>
									</form>
								</div>
							</div>
						</div>



						<div class="col-sm-6 col-md-6 col-lg-4 col-4">
							<div class="bloxx_box">
								<div class="blx_logo">
									<img src="<?php echo kitz_url; ?>/images/dropbox.jpg?v=<?= time(); ?>" style="max-width: 175px;" />
									<h3>Access Drop Box</h3>
									<?php if(get_option('drop_api', true)!="" && get_option('drop_api', true)!=1){ ?>
										<p>You can connect/Disconnect DropBox with one click on "Activate Dropbox" Button.</p>
										<form class="kitz_form" method="POST" id="dropbox_auth_process">
											<?php if($dropbox=="enable"){ ?>
											    <div class="submit-buttons">
											    	<button type="submit" name="dropBox_disconnect" class="btn button kitz_btn">Account Linked</button>
										            
										            <a href="<?= $kitz_dropbox_url; ?>" class="btn button button-success">Upload Json Files</a>
											    </div>
											<?php } else { ?>
											    <div class="submit-buttons">
										            <button type="submit" name="dropBox_connect" class="btn button button-danger">Activate Dropbox</button>
										            <p style="color:red;"><?= $resp_message ?></p>
											    </div>
											<?php } ?>
										</form>
									<?php } else { ?>
										<p style="color:red;">Please deactivate and activate plugin again for continue</p>
									<?php } ?>
								</div>
							</div>
						</div>


						<!--<div class="col-sm-6 col-md-6 col-lg-3 col-3">
							<div class="bloxx_box">
								<div class="blx_logo">
									<div class="blx-top neo_admin_logos">
										<img src="<?php echo WP_PLUGIN_URL; ?>/buildr/images/Buildr-Logo.png" />
										<h2>Buildr.</h2>
									</div>

									<p>Buildr allows you access a vast library of premade sections inside our proprietary drop-in builder for Divi.</p>
									<?php 

										if ( is_plugin_active( 'buildr/neo_builder.php' ) ) {
										    echo '<a href="javascript:void(0);" class="btn btn-primary btn-lg btn-block">Installed</a>';
										}  else{
											echo '<a href="'.UPDATE_PLUGIN_URL.'buildr.zip" class="btn btn-primary btn-lg btn-block">Download</a>';
										}
									?>
									
									<!- <a href="#" class="btn btn-trans btn-block">View Demo</a> ->

								</div>
							</div>
						</div>


						<div class="col-sm-6 col-md-6 col-lg-3 col-3">
							<div class="bloxx_box">
								<div class="blx_logo">
									<div class="blx-top neo_admin_logos">
										<img src="<?php echo WP_PLUGIN_URL; ?>/buildr/images/WritrAI-Logo.png" />
										<h2>WritrAl.</h2>
									</div>
									<p>Writr wields the power of AI to bring you fast, quality copy for your web design project.</p>
									<?php 
										
										if ( is_plugin_active( 'WritrAI/Writr.php' ) ) {
										    echo '<a href="javascript:void(0);" class="btn btn-primary btn-lg btn-block">Installed</a>';
										}  else{
											echo '<a href="'.UPDATE_PLUGIN_URL.'WritrAI.zip" class="btn btn-primary btn-lg btn-block">Download</a>';
										}
									?>
									<!- <a href="#" class="btn btn-trans btn-block">View Demo</a> ->

								</div>
							</div>
						</div>

						<div class="col-sm-6 col-md-6 col-lg-3 col-3">
							<div class="bloxx_box">
								<div class="blx_logo">
									<div class="blx-top neo_admin_logos">
										<img src="<?php echo WP_PLUGIN_URL; ?>/buildr/images/Pixr-Logo.png" />
										<h2>Pixr.</h2>
									</div>
									<p>Pixr brings free stock photos access directly into your site with our awesome cropping tool.</p>
									<?php 
										
										if ( is_plugin_active( 'pixr/free_stock_images.php' ) ) {
										    echo '<a href="javascript:void(0);" class="btn btn-primary btn-lg btn-block">Installed</a>';
										}  else{
											echo '<a href="'.UPDATE_PLUGIN_URL.'pixr.zip" class="btn btn-primary btn-lg btn-block">Download</a>';
										}
									?>
									<!- <a href="#" class="btn btn-trans btn-block">View Demo</a> ->

								</div>
							</div>
						</div>-->
					</div>


					<div class="height40px"></div>
					
				<?php
			//} ?>
					

		</div>
	</div>
</div>