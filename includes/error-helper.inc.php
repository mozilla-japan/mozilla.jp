<?php
namespace MJ\Error;

function send_header($code, $message){
    header('Status: ' . $code . ' ' . $message, true, $code);
    header('Pragma: no-cache');
    header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, private');
}

function send_403(){
    return send_header(403, 'Forbidden');
}

function send_404(){
    return send_header(404, 'Not Found');
}

function send_410(){
    return send_header(410, 'Gone');
}

function send_error_header($code){
    switch ($code) {
    case 403:
        send_403();
        break;
    case 410:
        send_410();
        break;
    default:
        send_404();
        break;
    }
}

function error_page_template($code){
    return '/includes/templates/sandstone/error-' . $code . '.html';
}

function normalize_error_code($path){
    if ($path !== '/.htaccess' || !isset($_GET['error'])) {
        return 404;
    }
    return $_GET['error'];
}
?>