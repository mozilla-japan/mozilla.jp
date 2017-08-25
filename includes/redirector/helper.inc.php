<?php
namespace MJ\Redirector;

function to($url){
  header('Pragma: no-cache');
  header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, private');
  header('Location: ' . $url);
  exit;
}

function to_beta($product, $version, $document){
    $path = '/' . $product . '/' . $version . 'beta/' . $document . '/';
    to($path);
}

?>