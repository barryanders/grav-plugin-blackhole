<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Grav;
use Grav\Common\Page\Page;

class BlackholePlugin extends Plugin
{
  public $content;

  public static function getSubscribedEvents()
  {
    return [
      'onPageInitialized' => ['onPageInitialized', 0],
      'onOutputRendered' => ['onOutputRendered', 0]
    ];
  }
  public function onPageInitialized()
  {
    // get page routes
    if ($_GET['pages'] == 'all') {
      ob_start();
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
      $this->content = json_encode($routes, JSON_UNESCAPED_SLASHES);
    }
  }
  public function onOutputRendered()
  {
    // Push routes to ?pages=all
    if ($_GET['pages'] == 'all') {
      ob_end_clean();
      echo $this->content;
    }
  }
}