<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';

 /*$guzzle = new GuzzleHttp\Client([
     'base_uri' => 'https://Pageberry.spp.io/api/v1/',
     'auth' => ['spp_api_HnvzDaxP23MkdKl6EwISyVA1JpOCUgG8', null],
 ]);

 try {

 	/*$body = ['email'=>'sitebloxx7@mailinator.com'];
    $request = $guzzle->request('POST', 'https://Pageberry.spp.io/api/v1/clients/17', [
    'form_params' => [
        'email' => 'sitebloxx7@mailinator.com',
        'name_f' => 'Test user',
    ]
]);
     $data = json_decode($request->getBody());

     // Do something with the response data
     echo "<pre>";
     print_r($data);
     echo "</pre>";*\

    //ABF7B6A2

    /*$body = ['email'=>'sitebloxx7@mailinator.com'];
    $request = $guzzle->request('POST', 'https://Pageberry.spp.io/api/v1/orders/ABF7B6A2', [
    'form_params' => [
        'order' => 'ABF7B6A2',
        'tags[]' => 'Recurring Stopped',
    ]
]);
     $data = json_decode($request->getBody());

     // Do something with the response data
     echo "<pre>";
     print_r($data);
     echo "</pre>";*/

     /*$request = $guzzle->request('POST', 'https://Pageberry.spp.io/api/v1/clients', [
         'form_params' => [
             'email' => 'sitebloxx3@mailinator.com',
         ]
     ]);

     $data = json_decode($request->getBody());

     // Do something with the response data
     print_r($data);


 } catch (Exception $e) {
    print_r($e);
     // Handle request exception
 }*/

 $guzzle = new GuzzleHttp\Client([
     'base_uri' => 'https://Pageberry.spp.io/api/v1/',
     'auth' => ['spp_api_HnvzDaxP23MkdKl6EwISyVA1JpOCUgG8', null],
 ]);

$id = 24;
$post_url = 'https://Pageberry.spp.io/api/v1/login_links/'.$id;

 try {
     $request = $guzzle->request('POST', $post_url, [
         'form_params' => [
             'client' => 25,
         ]
     ]);

     $data = json_decode($request->getBody());

     // Do something with the response data
     echo "<pre>";
     print_r($data);

 } catch (Exception $e) {
    echo "<pre>";
    print_r($e);
     // Handle request exception
 }

?>