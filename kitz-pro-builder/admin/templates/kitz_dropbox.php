<?php
if(!get_option('kitz_dropbox', true)){
	$dropbox= "disable";
} else {
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
		    "include_has_explicit_shared_members": false,
		    "include_media_info": false,
		    "include_mounted_folders": true,
		    "include_non_downloadable_files": true,
		    "path": "/kitzbuilder",
		    "recursive": false
		}',
  		CURLOPT_HTTPHEADER => array(
		    'Content-Type: application/json',
		    'Authorization: Bearer '.$access_token
  		),
	));

	$response = curl_exec($curl);
	

	$get_directory= json_decode($response, true);

	if($get_directory['entries']){
		$entry_folder= "/kitzbuilder/".$get_directory['entries'][0]['name']."/";

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

		// echo "<pre>";
		// print_r($curl_folder);
		// die();

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
	} 
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
								<p>Welcome to Dropbox functionality</p>
							</div>
						</div>
					</div>


					<div class="row">
						<div class="col-sm-6 col-md-6 col-lg-5 col-4">
							<div class="bloxx_box blox_box_form">
								<div class="blx_logo">
									<img src="<?php echo kitz_url; ?>/images/dropbox.jpg?v=<?= time(); ?>" style="max-width: 175px;" />
									<h3>Access Drop Box</h3>
									<p>Here you can upload your .json file into Dropbox</p>

									<form class="kitz_form" method="POST" id="dropbox_import" enctype="multipart/form-data">
									    <section class="object-meta-data taxonomy-meta-data">
									        <table class="widefat fixed siteblox-table" cellspacing="0">
									            <tbody>
									            	<tr class="alternate">
														<td>
															<input type="text" name="section_title" id="section_title" placeholder="Section Title">
														</td>
													</tr>

													<tr class="alternate">
														<td>
															<select name="kitz" class="select-box" id="kitz_directory">
																<?php if(!empty($get_directory['entries'])){ ?>
																	<?php foreach ($get_directory['entries'] as $folders) { ?>
																		<option value="<?= $folders['name'] ?>"><?= $folders['name'] ?></option>	
																	<?php } ?>
																<?php } ?>
															</select>	
														</td>
													</tr>

													<tr class="alternate">
														<td>

															<select name="kitz_subdirectory" id="kitz_subdirectory" class="select-box">
																<?php if(!empty($get_direct['entries'])){ ?>
																	<?php foreach ($get_direct['entries'] as $sub_folders) { ?>
																		<option value="<?= $sub_folders['name'] ?>"><?= $sub_folders['name'] ?></option>	
																	<?php } ?>
																<?php } ?>
															</select>	
															
															<a class="kitz_button kitz_dropbox_directory" href="javascript:void(0)">Create Directory</a>
														</td>
													</tr>

									                <tr class="alternate">
														<td>
															<div class="wrapper">
																<div class="drop">
																	<div class="cont" style="color: rgb(142, 153, 165);">
																			<i class="fa fa-cloud-upload"></i>
																			<div class="tit">
																				Drag &amp; Drop
																			</div>

																			<div class="desc">
																				your files to Assets, or 
																			</div>

																			<div class="browse">
																				click here to browse
																			</div>
																	</div>
																	<output id="list"></output>
																	<input id="json_files" multiple="true" name="json_url" type="file">
																</div>
															</div>
															
															<input type="hidden" name="action" value="kitzdropbox_upload">
															<button type="submit" id="kitz_import_json" style="visibility: hidden; position:absolute;">Upload Json</button>
														</td>
													</tr>

													
									            </tbody>
									        </table>
									    </section>

									    <div class="submit-buttons">
								            <button type="submit" class="kitz_btn button">Submit</button>
									    </div>
									</form>

									
								</div>
							</div>
						</div>
					</div>
					<div class="height40px"></div>
					
				<?php
			//} ?>
					

		</div>
	</div>
</div>