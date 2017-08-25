<?php
namespace MJ\Redirector\Notes;

function base_path($product){
    $product = strtolower($product);
    if($product == "firefox"){
        return "/firefox/releases";
    }
    if($product == "android"){
        return "/firefox/android/releases";
    }
    if($product == "thunderbird"){
        return "/thunderbird/releases/";
    }
    return null;
}

function expand($product, $resolved){
    $base = base_path($product);
    if(isset($base)){
        return $base . "/" . $resolved;
    }
    return null;
}

function expand_path($product, $resolved){
    $resolved = expand($product, $resolved);
    if(isset($resolved)){
        return $_SERVER['DOCUMENT_ROOT'] . $resolved;
    }
    return null;
}

function is_beta_version($version){
    return preg_match('/beta$/', $version);
}

function is_unreleased_version($version, $major_version, $beta_version){
    return preg_match("/^\d+(\.\d+)*$/", $version) &&
        floatval($version) !== floatval($major_version) &&
        floatval($version) === floatval($beta_version);
}

function is_release_before_version1($version){
    return floatval($version) < 1.0;
}

function resolve_beta_notes($product, $version){
    $version = preg_replace('/beta/', '', $version);
    $path = intval($version) . '/releasenotes-' . $version . '-beta.html';
    if(file_exists(expand_path($product, $path))){
        return $path;
    }
    return intval($version) . '/releasenotes-beta.html';
}

function resolve_releasenotes($product, $version){
    if(is_release_before_version1($version)){
        return '0/releasenotes-' . $version . '.html';
    }
    if (floatval($version) === 1.5 ||
        floatval($version) === 3.5 ||
        floatval($version) === 3.6) {
        return floatval($version) . '/releasenotes-' . $version . '.html';
    }
    return intval($version) . '/releasenotes-' . $version . '.html';
}

function resolve($product, $version){
    if(is_beta_version($version)){
        return resolve_beta_notes($product, $version);
    }
    return resolve_releasenotes($product, $version);
}

?>