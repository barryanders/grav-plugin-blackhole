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
      $grav_routes = $this->grav['pages']->routes();
      $routes = array();
      $routes[] = (object) array('route' => '/');
      foreach ($grav_routes as $route => $path) {
        $routes[] = (object) array('route' => $route);
      }
      $this->content = json_encode($routes);
    }
  }
  public function onOutputRendered()
  {
    // Push routes to ?pages=all
    if ($_GET['pages'] == 'all') {
      echo "<script>document.querySelector('html').innerHTML = `".$this->content."`;</script>";
    }
  }
}