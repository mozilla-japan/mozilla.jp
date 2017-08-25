<?php
function load_json($filename){
    $json = file_get_contents($filename);
    $product = json_decode($json);
    return $product;
}
?>