<?php

// flatten a multidimensional array
function array_flatten($array = null) {
  $result = array();
  if (!is_array($array)) {
    $array = func_get_args();
  }
  foreach ($array as $key => $value) {
    if (is_array($value)) {
      $result = array_merge($result, array_flatten($value));
    } else {
      $result = array_merge($result, array($key => $value));
    }
  }
  return $result;
}

// curl
function pull($light) {
  $pull = curl_init();
  curl_setopt($pull, CURLOPT_URL, $light);
  curl_setopt($pull, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($pull, CURLOPT_FOLLOWLOCATION, 1);
  $emit = curl_exec($pull);
  curl_close($pull);
  return $emit;
}

// swap links
function portal($in, $out, $content) {
  // input_url -> output_url
  $content = (!empty($out)
    ? str_ireplace($in, $out, $content)
    : $content
  );
  return $content;
}

// get links to assets
function tidal_disruption($data, $elements, $attribute) {
  $doc = new \DOMDocument();
  @$doc->loadHTML($data);
  $links = array();
  foreach($doc->getElementsByTagName($elements) as $element) {
    if ($element->getAttribute('rel') !== 'canonical') {
      $links[] = $element->getAttribute($attribute);
    }
  }
  return $links;
}

// generate files
function generate($route, $path, $data) {
  if (!is_dir($route)) { mkdir($route, 0755, true); }
  file_put_contents($path, $data);
}

// generate pages
function pages($that, $route, $path, $path_origin, $data, $force, $verbose) {
  // page exists
  if (file_exists($path) && !$force) {
    switch (true) {
      // page was changed: copy the new one
      case filemtime($path_origin) > filemtime($path):
        generate($route, $path, $data);
        if ($verbose) $that->writeln('<green>+ Page ➜ ' . $path . '</green>');
        break;
      // no page changes: skip it
      default:
        if ($verbose) $that->writeln('<cyan>• Page ➜ ' . $path . '</cyan>');
        break;
    }
  // page doesn't exist or force option is enabled
  } else {
    // copy the page
    generate($route, $path, $data);
    if ($verbose) $that->writeln('<green>+ Page ➜ ' . $path . '</green>');
  }
}

// generate assets
function assets($that, $event_horizon, $input_url, $data, $force, $verbose) {
  $asset_links = array();
  $asset_links[] = tidal_disruption($data, 'link', 'href');
  $asset_links[] = tidal_disruption($data, 'script', 'src');
  $asset_links[] = tidal_disruption($data, 'img', 'src');
  $asset_links[] = tidal_disruption($data, 'img', 'data-src'); //also fetch lazy loaded images
  $input_url_parts = parse_url($input_url);
  foreach (array_flatten($asset_links) as $asset) {
    if (
      strpos($asset, 'data:') !== 0 && // exclude data URIs
      (strpos($asset, '/') === 0 || $input_url_parts['host'] === parse_url($asset)['host']) // continue if asset is local
    ) {
      $asset_path = parse_url($asset)['path'];
      $asset_file_origin = rtrim(GRAV_ROOT, '/').$asset_path;
      $asset_file_destination = rtrim($event_horizon, '/').$asset_path;
      $asset_route = str_replace(basename($asset_file_destination), '', $asset_file_destination);
      // echo 'asset_file_origin: ' . $asset_file_origin . "\r\n";
      // echo 'asset_file_destination: ' . $asset_file_destination . "\r\n";
      // echo 'asset_route: ' . $asset_route . "\r\n";
      // echo 'asset: ' . $asset . "\r\n";
      // asset exists
      if (file_exists($asset_file_destination)) {
        switch (true) {
          // asset was changed: copy the new one
          case md5_file($asset_file_origin) !== md5_file($asset_file_destination):
            if (!is_dir($asset_route)) { mkdir($asset_route, 0755, true); }
            copy($asset_file_origin, $asset_file_destination);
            if ($verbose) $that->writeln('  <green>+ Asset ➜ ' . $asset_file_destination . '</green>');
            break;
          // if force, then copy it anyway
          case $force:
            copy($asset_file_origin, $asset_file_destination);
            if ($verbose) $that->writeln('  <green>+ Asset ➜ ' . $asset_file_destination . '</green>');
            break;
          // no asset changes and not forced: skip it
          default:
            if ($verbose) $that->writeln('  <cyan>• Asset ➜ ' . $asset_file_destination . '</cyan>');
            break;
        }
      // asset doesn't exist
      } else {
        if (!is_dir($asset_route)) { mkdir($asset_route, 0755, true); }
        copy($asset_file_origin, $asset_file_destination);
        $that->writeln('  <green>+ Asset ➜ ' . $asset_file_destination . '</green>');
      }
    }
  }
}
