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
      $generate_command = $this->config->get('plugins.blackhole.generate_command');

      $this->content = $generate_command;
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
      shell_exec('bin/plugin blackhole generate ' . $this->content);
    }
  }
}
