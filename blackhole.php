<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Page\Page;

class BlackholePlugin extends Plugin {
  public $content;
  public static function getSubscribedEvents() {
    return [
      'onPageInitialized' => ['onPageInitialized', 0],
      'onOutputRendered' => ['onOutputRendered', 0]
    ];
  }

  public function onPageInitialized() {
    if (!defined('GRAV_URL')) {
      define('GRAV_URL', $this->grav['uri']->base() . $this->grav['pages']->baseUrl());
    }

    if (!empty($_GET['pages']) && $_GET['pages'] == 'all') {
      ob_start();
      // get all routes from grav
      $routes = $this->grav['pages']->routes();
      // unset modular pages
      foreach ($routes as $path => $dir) {
        if (strpos($path, '/_') !== false) {
          unset($routes[$path]);
        }
      }
      $this->content = json_encode($routes, JSON_UNESCAPED_SLASHES);

    } else if (!empty($_GET['generate']) && $_GET['generate'] == 'true') {
      // get generate_command from plugin settings
      $output_url   = $this->config->get('plugins.blackhole.generate.output_url');
      $output_path  = $this->config->get('plugins.blackhole.generate.output_path');
      $routes       = $this->config->get('plugins.blackhole.generate.routes');
      $simultaneous = $this->config->get('plugins.blackhole.generate.simultaneous');
      $assets       = $this->config->get('plugins.blackhole.generate.assets');
      $force        = $this->config->get('plugins.blackhole.generate.force');
      $this->content =
        'bin/plugin blackhole generate ' . GRAV_URL .
        ($output_url   ? ' --output-url '   . $output_url   : '') .
        ($output_path  ? ' --output-path '  . $output_path  : '') .
        ($routes       ? ' --routes '       . $routes       : '') .
        ($simultaneous ? ' --simultaneous ' . $simultaneous : '') .
        ($assets       ? ' --assets'                        : '') .
        ($force        ? ' --force'                         : '')
      ;
    }
  }

  public function onOutputRendered() {
    // publish page routes
    if (!empty($_GET['pages']) && $_GET['pages'] == 'all') {
      ob_end_clean();
      echo $this->content;
    }
    // action for generate button
    if (!empty($_GET['generate']) && $_GET['generate'] == 'true') {
      shell_exec($this->content);
    }
  }
}
