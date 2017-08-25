<?php 
echo $shared_footers;

foreach ($js as $path) {
  $with_serial = MJ\Path\add_serial_number($path, $mj->js);
  $full_path = MJ\Path\expand_path($with_serial, '/static/scripts');
  echo '    <script src="' . $full_path . '"></script>' . PHP_EOL;
}

if (!empty($extra_footers)) {
  echo $extra_footers . PHP_EOL;
}

?>
  </body>
</html>
<?php 
ob_get_flush();

unset($extra_headers, $breadcrumbs, $css, $js, $page_title, $meta_robots, $body_id, $body_class, $extra_footer_links, $extra_footers);
