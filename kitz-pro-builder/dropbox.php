<?php

require('../../../wp-load.php');
$drop_key= get_option('drop_api', true);
$drop_secret= get_option('drop_secret', true);


$code= $_REQUEST['code'];

update_option('db_authcode', $code);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://api.dropbox.com/oauth2/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "code=$code&grant_type=authorization_code&redirect_uri=https://phpstack-589117-1915647.cloudwaysapps.com/kitzpro/wp-content/plugins/kitz-pro-builder/dropbox.php");
curl_setopt($ch, CURLOPT_USERPWD, $drop_key . ':' . $drop_secret);

$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
}
curl_close($ch);



$decode_drop_box= json_decode($result, true);


update_option('kitz_dropbox', "enable");
update_option('dropbox_token_detail', $decode_drop_box);



$dropbox_token_detail= get_option('dropbox_token_detail');
$access_token= $dropbox_token_detail['access_token'];
$refresh_token= $dropbox_token_detail['refresh_token'];
update_option('db_refresh_token', $refresh_token);




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
  CURLOPT_POSTFIELDS =>'{
    "autorename":false,
    "path":"/kitzbuilder"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer '.$access_token
  ),
));

$response = curl_exec($curl);

curl_close($curl);


$curl1 = curl_init();
curl_setopt_array($curl1, array(
  CURLOPT_URL => 'https://api.dropboxapi.com/2/files/create_folder_v2',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "autorename":false,
    "path":"/kitzbuilder/Sections"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer '.$access_token
  ),
));

$response = curl_exec($curl1);

curl_close($curl1);




$curl2 = curl_init();
curl_setopt_array($curl2, array(
  CURLOPT_URL => 'https://api.dropboxapi.com/2/files/create_folder_v2',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "autorename":false,
    "path":"/kitzbuilder/Layouts"
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer '.$access_token
  ),
));

$response = curl_exec($curl2);

curl_close($curl2);







$kitz_dropbox_url = esc_url( add_query_arg(
    'page',
    'kitz_settings',
    get_admin_url() . 'admin.php'
) );

wp_safe_redirect($kitz_dropbox_url);
exit();
?>