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
      'taxonomy',
      't',
      InputOption::VALUE_NONE,
      'Process the taxonomy map.'
    )
    ->addOption(
      'force',
      'f',
      InputOption::VALUE_NONE,
      'Overwrite previously generated files.'
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
      'simultaneous' => $this->input->getOption('simultaneous'),
      'assets'       => $this->input->getOption('assets'),
      'taxonomy'     => $this->input->getOption('taxonomy'),
      'force'        => $this->input->getOption('force')
    ];
    $input_url    = $this->options['input-url'];
    $output_url   = $this->options['output-url'];
    $output_path  = $this->options['output-path'];
    $routes       = $this->options['routes'];
    $simultaneous = $this->options['simultaneous'];
    $assets       = $this->options['assets'];
    $taxonomy     = $this->options['taxonomy'];
    $force        = $this->options['force'];

    // default output path
    $event_horizon = GRAV_ROOT . '/';
    // set user defined output path
    if (!empty($output_path)) $event_horizon .= $output_path;
    // make output path
    if (!is_dir(dirname($event_horizon))) mkdir(dirname($event_horizon), 0755, true);
    // get page routes
    $pages = $grav['pages']->routes();
    $pageCount = count((array)$pages);
    foreach ($pages as $path => $dir) {
      if (strpos($path, '/_') !== false) {
        unset($pages[$path]);
      } elseif (!empty($routes)) {
        $pages2 = array(); $pages2['/'.$path] = '';
        $pages = array_intersect_key((array)$pages, $pages2);
      }
    }
    // get taxonomy map
    $taxonomies = $grav['taxonomy']->taxonomy();
    $taxonomyCount = count((array)$taxonomies);

    /* generate pages
    ----------------- */
    if ($pageCount) {
      $rollingCurl = new \RollingCurl\RollingCurl();
      foreach ($pages as $grav_slug => $grav_file_path) {
        $request = new \RollingCurl\Request($input_url . $grav_slug);
        $request->event_horizon = $event_horizon;
        $request->grav_file_path = $grav_file_path;
        $request->bh_route = preg_replace('/\/\/+/', '/', $event_horizon . $grav_slug);
        $request->bh_file_path = preg_replace('/\/\/+/', '/', $request->bh_route . '/index.html');
        $request->input_url = $input_url;
        $request->output_url = $output_url;
        $request->simultaneous = (int)$simultaneous < 1 ? 1 : (int)$simultaneous;
        $request->assets = $assets;
        $request->taxonomy = $taxonomy;
        $request->force = $force;
        $rollingCurl->add($request);
      }
      $rollingCurl
        ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
          $grav_page_data = $request->getResponseText();
          // generate assets
          if ($request->assets) assets($this->output, $request->event_horizon, $request->input_url, $grav_page_data);
          // swap links
          $grav_page_data = portal($request->input_url, $request->output_url, $grav_page_data);
          // generate pages
          pages($this->output, $request->bh_route, $request->bh_file_path, $request->grav_file_path, $grav_page_data, $request->force);
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

    /* generate taxonomies
    ---------------------- */
    if ($taxonomy) {
      if ($taxonomyCount) {
        $rollingCurl = new \RollingCurl\RollingCurl();
        foreach ($taxonomies as $taxonomy_parent => $taxonomy_children) {
          foreach ($taxonomy_children as $taxonomy_child => $pages_in_taxonomy) {
            $taxonomy_url  = $input_url . '/' . $taxonomy_parent . ':' . $taxonomy_child;
            $request = new \RollingCurl\Request($taxonomy_url);
            $request->taxonomy_path = $event_horizon . '/' . $taxonomy_parent . '/' . $taxonomy_child;
            $request->input_url = $input_url;
            $request->output_url = $output_url;
            $request->simultaneous = (int)$simultaneous < 1 ? 1 : (int)$simultaneous;
            $rollingCurl->add($request);
          }
        }
        $rollingCurl
          ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
            $grav_page_data = $request->getResponseText();
            // swap links
            $grav_page_data = portal($request->input_url, $request->output_url, $grav_page_data);
            // generate taxonomy
            taxonomies($this->output, $request->taxonomy_path, $request->taxonomy_path . '/index.html', $grav_page_data);
            // clear list of completed requests and prune pending request queue to avoid memory growth
            $rollingCurl->clearCompleted();
            $rollingCurl->prunePendingRequestQueue();
          })
          ->setSimultaneousLimit($request->simultaneous)
          ->execute()
        ;
      } else {
        $this->output->writeln('<red>ERROR</red> No taxonomies were found');
      }
    }

    $this->output->writeln(
      '<green>DONE</green> Blackhole processed ' .
      $pageCount . ' page' . ($pageCount !== 1 ? 's' : '') .
      ($taxonomy && $taxonomyCount ? ' and ' . $taxonomyCount . ' taxonom' . ($taxonomyCount !== 1 ? 'ies' : 'y') : '') .
      ' in ' . (microtime(true) - $start) . ' seconds'
    );
  }
}
