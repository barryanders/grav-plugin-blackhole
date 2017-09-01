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
    if (!empty($_GET['pages']) && $_GET['pages'] == 'all') {
      ob_start();

      // get output_path from plugin settings
      $output_path = $this->config->get('plugins.blackhole.output_path');

      // get all routes from grav
      $routes = $this->grav['pages']->routes();

      // unset modular pages
      foreach ($routes as $path => $dir) {
        if (strpos($path, '/_') !== false) {
          unset($routes[$path]);
        }
      }

      if (!empty($_GET['output_path'])) {
        $this->content = $output_path;
      } else {
        $this->content = json_encode($routes, JSON_UNESCAPED_SLASHES);
      }
    }
  }

  public function onOutputRendered() {
    // publish page routes
    if (!empty($_GET['pages']) && $_GET['pages'] == 'all') {
      ob_end_clean();
      echo $this->content;
    }
  }
}
