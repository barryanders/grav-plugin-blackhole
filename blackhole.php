<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;

class BlackholePlugin extends Plugin {
  public $content;
  public static function getSubscribedEvents() {
    return [
      'onPageInitialized' => ['onPageInitialized', 0],
      'onOutputRendered' => ['onOutputRendered', 0]
    ];
  }

  public function onPageInitialized() {
    if (!defined('ROOT_URL')) define('ROOT_URL', $this->grav['uri']->rootUrl(true));

    if (!empty($_GET['blackhole']) && $_GET['blackhole'] === 'generate') {
      $output_url   = $this->config->get('plugins.blackhole.generate.output_url');
      $output_path  = $this->config->get('plugins.blackhole.generate.output_path');
      $use_sitemap  = $this->config->get('plugins.blackhole.generate.use_sitemap');
      $routes       = $this->config->get('plugins.blackhole.generate.routes');
      $simultaneous = $this->config->get('plugins.blackhole.generate.simultaneous');
      $assets       = $this->config->get('plugins.blackhole.generate.assets');
      $force        = $this->config->get('plugins.blackhole.generate.force');
      $this->content =
        'bin/plugin blackhole generate ' . ROOT_URL .
        ($output_url   ? ' --output-url '   . $output_url   : '') .
        ($output_path  ? ' --output-path '  . $output_path  : '') .
        ($routes       ? ' --routes '       . $routes       : '') .
        ($use_sitemap  ? ' --use-sitemap '  . $use_sitemap  : '') .
        ($simultaneous ? ' --simultaneous ' . $simultaneous : '') .
        ($assets       ? ' --assets'                        : '') .
        ($force        ? ' --force'                         : '')
      ;
    }
  }

  public function onOutputRendered() {
    // action for generate button
    if (!empty($_GET['blackhole']) && $_GET['blackhole'] === 'generate') {
      shell_exec($this->content);
    }
  }
}
