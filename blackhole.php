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
    // get page routes
    if (!empty($_GET['pages']) && $_GET['pages'] == 'all') {
      ob_start();
      // get output_path from plugin settings
      $output_path = $this->config->get('plugins.blackhole.output-path');
      // get all routes from grav
      $grav_routes = $this->grav['pages']->routes();
      $routes = array();
      // add root first, otherwise it will be last
      $routes[] = '/';
      foreach ($grav_routes as $route => $path) {
        // if route not in routes
        if (!in_array($route, $routes)) {
          $routes[] = $route;
        }
      }
      if (!empty($_GET['output-path'])) {
        $this->content = $output_path;
      } else {
        $this->content = json_encode($routes, JSON_UNESCAPED_SLASHES);
      }
    }
  }

  public function onOutputRendered() {
    // Push routes to ?pages=all
    if (!empty($_GET['pages']) && $_GET['pages'] == 'all') {
      ob_end_clean();
      echo $this->content;
    }
  }
}