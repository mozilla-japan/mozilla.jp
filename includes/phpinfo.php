<?php

// Mozilla Japan オフィスかローカルホストからアクセスされた場合のみ phpinfo を表示

$allowed = array('202.221.217.73', '127.0.0.1');

if (in_array($_SERVER['REMOTE_ADDR'], $allowed)) {
  phpinfo();
}

?>
