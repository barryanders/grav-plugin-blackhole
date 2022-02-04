<?php
namespace Grav\Plugin\Console;

use Grav\Common\Grav;
use Grav\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

require __DIR__ . '/../functions.php';
require __DIR__ . '/../vendor/RollingCurl/Request.php';
require __DIR__ . '/../vendor/RollingCurl/RollingCurl.php';

class GenerateCommand extends ConsoleCommand {
  protected $options = [];

  protected function configure() {
    $this
    ->setName("g")
    ->setName("gen")
    ->setName("generate")
    ->setDescription("Generate your static site.")
    ->addArgument(
      'input-url',
      InputArgument::REQUIRED,
      'Enter the URL to your live Grav site.'
    )
    ->addOption(
      'output-url',
      'd',
      InputOption::VALUE_REQUIRED,
      'The URL of your static site. This determines the domain used in the absolute path of your links.'
    )
    ->addOption(
      'output-path',
      'p',
      InputOption::VALUE_REQUIRED,
      'The directory to which your static site will be written (relative to Grav root).'
    )
    ->addOption(
      'routes',
      'r',
      InputOption::VALUE_REQUIRED,
      'Limit generation to a select list of page routes.'
    )
    ->addOption(
      'use-sitemap',
      'm',
      InputOption::VALUE_OPTIONAL,
      'Use a xml sitemap generated with the grav sitemap plugin.'
    )
    ->addOption(
      'simultaneous',
      's',
      InputOption::VALUE_OPTIONAL,
      'Determine how many files will generate at the same time.',
      10
    )
    ->addOption(
      'assets',
      'a',
      InputOption::VALUE_NONE,
      'Copy assets to the output path.'
    )
    ->addOption(
      'force',
      'f',
      InputOption::VALUE_NONE,
      'Overwrite previously generated files.'
    )
    ->addOption(
      'verbose-mode',
      '',
      InputOption::VALUE_NONE,
      'Enable verbose mode.'
    );
  }

  protected function serve() {

    $start = microtime(true);
    $grav = Grav::instance();
    $grav['pages']->init();

    $this->options = [
      'input-url'    => $this->input->getArgument('input-url'),
      'output-url'   => $this->input->getOption('output-url'),
      'output-path'  => $this->input->getOption('output-path'),
      'routes'       => $this->input->getOption('routes'),
      'use-sitemap'  => $this->input->getOption('use-sitemap'),
      'simultaneous' => $this->input->getOption('simultaneous'),
      'assets'       => $this->input->getOption('assets'),
      'force'        => $this->input->getOption('force'),
      'verbose'      => $this->input->getOption('verbose-mode')
    ];
    $input_url    = $this->options['input-url'];
    $output_url   = $this->options['output-url'];
    $output_path  = $this->options['output-path'];
    $routes       = $this->options['routes'];
    $use_sitemap  = $this->options['use-sitemap'];
    $simultaneous = $this->options['simultaneous'];
    $assets       = $this->options['assets'];
    $force        = $this->options['force'];
    $verbose      = $this->options['verbose'];

    // default output path
    $event_horizon = GRAV_ROOT . '/';
    // set user defined output path
    if (!empty($output_path)) $event_horizon .= $output_path;
    // make output path
    if (!is_dir(dirname($event_horizon))) mkdir(dirname($event_horizon), 0755, true);
    // get page routes

    if ($use_sitemap) {
      $sitemap_url = $input_url . '/' . trim($use_sitemap, '/');
      if ($verbose) $this->output->writeln('<green>Reading Sitemap</green> ' . $sitemap_url);
      if (!($sitemap = pull($sitemap_url))) {
        $this->output->writeln('<red>ERROR</red> Sitemap not found.');
        die();
      }
      if (!($sitemap = simplexml_load_string($sitemap))) {
        $this->output->writeln('<red>ERROR</red> Sitemap not valid xml');
        die();
      }
      $pages = [];
      foreach ($sitemap->url as $url) {
        $src_url = (string) $url->loc;
        $len = strlen($input_url);
        if (substr($src_url, 0, $len) !== $input_url) {
          $this->output->writeln('<red>ERROR</red> url on external site: ' . $url->loc);
          continue;
        }

        $dst_route = substr($src_url, $len);
        $dst_path = $dst_route;
        $info = (object) pathinfo($dst_route);
        if ($info->extension) {
          $dst_route = $info->dirname;
        }
        else {
          $dst_path .= '/index.html';
          if ($info->dirname == '/') $src_url .= '/home.html';
        }
        $pages[] = [
          'src_url' => $src_url,
          'src_file_path' => '', //used for checking timestamp,
          'dst_route' => $event_horizon . $dst_route,
          'dst_file_path' => $event_horizon . $dst_path
        ];
      }
    }
    else {
      $grav_routes = $grav['pages']->routes();
      foreach ($grav_routes as $path => $dir) {
        if (strpos($path, '/_') !== false) {
          unset($grav_routes[$path]);
        } elseif (!empty($routes)) {
          $pages2 = array(); $pages2['/'.$path] = '';
          $pages = array_intersect_key((array)$grav_routes, $pages2);
        }
      }
      $pages = [];
      foreach ($grav_routes as $grav_slug => $grav_file_path) {
        $dst_route = preg_replace('/\/\/+/', '/', $event_horizon . $grav_slug);
        $pages[] = [
          'src_url' => $input_url . $grav_slug,
          'src_file_path' => $grav_file_path, //used for checking timestamp,
          'dst_route' => $dst_route,
          'dst_file_path' => preg_replace('/\/\/+/', '/', $dst_route . '/index.html')
        ];
      }
    }

    $pageCount = count((array)$pages);
    /* generate pages
    ----------------- */
    if ($pageCount) {
      $rollingCurl = new \RollingCurl\RollingCurl();
      foreach ($pages as $page) {
        $request = new \RollingCurl\Request($page['src_url']);
        $request->event_horizon = $event_horizon;
        $request->grav_file_path = $page['src_file_path'];
        $request->bh_route = $page['dst_route'];
        $request->bh_file_path = $page['dst_file_path'];
        $request->input_url = $input_url;
        $request->output_url = $output_url;
        $request->simultaneous = (int)$simultaneous < 1 ? 1 : (int)$simultaneous;
        $request->assets = $assets;
        $request->force = $force;
        $request->verbose = $verbose;
        $rollingCurl->add($request);
      }
      $rollingCurl
        ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
          $grav_page_data = $request->getResponseText();
          // swap links
          $grav_page_data_swapped = portal($request->input_url, $request->output_url, $grav_page_data);
          // generate pages
          pages($this->output, $request->bh_route, $request->bh_file_path, $request->grav_file_path, $grav_page_data_swapped, $request->force, $request->verbose);
          // generate assets
          if ($request->assets) assets($this->output, $request->event_horizon, $request->input_url, $grav_page_data, $request->force, $request->verbose);
          // clear list of completed requests and prune pending request queue to avoid memory growth
          $rollingCurl->clearCompleted();
          $rollingCurl->prunePendingRequestQueue();
        })
        ->setSimultaneousLimit($request->simultaneous)
        ->execute()
      ;
    } else {
      $this->output->writeln('<red>ERROR</red> No pages were found');
      die();
    }

    /* done
    ------- */
    $this->output->writeln(
      '⚫ Blackhole processed ' .
      $pageCount . ' page' . ($pageCount !== 1 ? 's' : '') .
      ' in ' . (microtime(true) - $start) . ' seconds'
    );
  }
}
