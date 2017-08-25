<?php
namespace MJ\Path;

function expand_path($path, $base){
  if($path[0] === '/'){
    return $path;
  }
  return $base . '/' . $path;
}

function add_serial_number($path, $table){
  if(!array_key_exists($path, $table)) {
    return $path;
  }
  return $path . '-' . $table[$path];
}

?>