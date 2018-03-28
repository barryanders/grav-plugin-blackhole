<?php
namespace Grav\Plugin\Console;

use Grav\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

require __DIR__ . '/../vendor/RollingCurl/RollingCurl.php';
require __DIR__ . '/../vendor/RollingCurl/Request.php';

class GenerateCommand extends ConsoleCommand {
  protected $options = [];

  protected function configure() {
    // `generate` command settings
    $this
    ->setName("g")
    ->setName("gen")
    ->setName("generate")
    ->setDescription("Generates static site")
    ->addArgument(
      'input-url',
      InputArgument::REQUIRED,
      'Set the URL of your live Grav site (ex. http://localhost/grav)'
    )
    ->addOption(
      'output-url',
      'd',
      InputOption::VALUE_REQUIRED,
      'Set the URL of your static site. This determines the domain used in the absolute path of your links (ex. https://website.com)'
    )
    ->addOption(
      'output-path',
      'p',
      InputOption::VALUE_REQUIRED,
      'Set the directory to which your static site will be written. Relative to Grav root (ex. ../)'
    )
    ->addOption(
      'routes',
      'r',
      InputOption::VALUE_REQUIRED,
      'Define a list of routes to only generate certain pages.'
    )
    ->addOption(
      'simultaneous',
      's',
      InputOption::VALUE_OPTIONAL,
      'Set how many files to generate at the same time.',
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
    );
  }

  protected function serve() {

    // get options
    $this->options = [
      'input-url'    => $this->input->getArgument('input-url'),
      'output-url'   => $this->input->getOption('output-url'),
      'output-path'  => $this->input->getOption('output-path'),
      'routes'       => $this->input->getOption('routes'),
      'simultaneous' => $this->input->getOption('simultaneous'),
      'assets'       => $this->input->getOption('assets'),
      'force'        => $this->input->getOption('force')
    ];
    $input_url    = $this->options['input-url'];
    $output_url   = $this->options['output-url'];
    $output_path  = $this->options['output-path'];
    $routes       = $this->options['routes'];
    $simultaneous = $this->options['simultaneous'];
    $assets       = $this->options['assets'];
    $force        = $this->options['force'];

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

    // swaps input_url with output_url in page code
    function portal($in, $out, $content) {
      $wormhole = str_ireplace($in, $out, $content);
      return $wormhole;
    }

    // get links to assets
    function tidal_disruption($data, $elements, $attribute) {
      $doc = new \DOMDocument();
      @$doc->loadHTML($data);
      $links = array();
      foreach($doc->getElementsByTagName($elements) as $element) {
        $links[] = $element->getAttribute($attribute);
      }
      return $links;
    }

    // write files
    function generate($route, $file_path, $data) {
      if (!is_dir($route)) { mkdir($route, 0755, true); }
      file_put_contents($file_path, $data);
    }

    // set output path
    $event_horizon = GRAV_ROOT . '/'; // defaults to grav_root
    if (!empty($output_path)) {
      $event_horizon .= $output_path; // appends user defined output path in CL
    }
    // make output path
    if (!is_dir(dirname($event_horizon))) { mkdir(dirname($event_horizon), 0755, true); }

    // get page routes
    $pages = json_decode(pull($input_url . '/?pages=all'));
    if (!empty($routes)) {
      $pages2 = array(); foreach (explode(',', $routes) as $path) { $pages2['/'.$path] = ''; }
      $pages = array_intersect_key((array)$pages, $pages2);
    }

    // make pages in output path
    $pageCount = count((array)$pages);
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
        $request->assets = $assets;
        $request->simultaneous = (int)$simultaneous;
        $request->force = $force;
        $rollingCurl->add($request);
      }
      $start = microtime(true);
      $rollingCurl
        ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
          $grav_page_data = (!empty($request->output_url)
            ? portal($request->input_url, $request->output_url, $request->getResponseText())
            : $request->getResponseText()
          );
          // page exists
          if (file_exists($request->bh_file_path) && !$request->force) {
            switch (true) {
              // page was changed: copy the new one
              case filemtime($request->grav_file_path) > filemtime($request->bh_file_path):
                generate($request->bh_route, $request->bh_file_path, $grav_page_data);
                $this->output->writeln('<green>REGENERATING</green> ➜ ' . realpath($request->bh_route));
                break;
              // no page changes: skip it
              default:
                $this->output->writeln('<cyan>SKIPPING</cyan> No changes ➜ ' . realpath($request->bh_route));
                break;
            }
          // page doesn't exist or force option is enabled
          } else {
            // copy the page
            generate($request->bh_route, $request->bh_file_path, $grav_page_data);
            $this->output->writeln('<green>GENERATING</green> ➜ ' . realpath($request->bh_route));
          }
          // copy assets
          if ($request->assets) {
            $asset_links = array();
            $asset_links[] = tidal_disruption($grav_page_data, 'link', 'href');
            $asset_links[] = tidal_disruption($grav_page_data, 'script', 'src');
            $asset_links[] = tidal_disruption($grav_page_data, 'img', 'src');
            $input_url_parts = parse_url($request->input_url);
            foreach (array_flatten($asset_links) as $asset) {
              if (strpos($asset, '/') === 0 || $input_url_parts['host'] === parse_url($asset)['host']) {
                $asset_url = $input_url_parts['scheme'] . '://' . $input_url_parts['host'] . $asset;
                $asset_file_origin = str_replace($input_url_parts['path'], '', GRAV_ROOT) . $asset;
                $asset_file_destination = $request->event_horizon . str_replace($input_url_parts['path'], '', $asset);
                $asset_route = str_replace(basename($asset_file_destination), '', $asset_file_destination);
                // asset doesn't exist
                if (file_exists($asset_file_destination)) {
                  switch (true) {
                    // asset was changed: copy the new one
                    case filemtime($asset_file_origin) > filemtime($asset_file_destination):
                      generate($asset_route, $asset_file_destination, file_get_contents($asset_url));
                      break;
                    // no asset changes: skip it
                    default:
                      break;
                  }
                // asset doesn't exist
                } else {
                  generate($asset_route, $asset_file_destination, file_get_contents($asset_url));
                }
              }
            }
          }
          // clear list of completed requests and prune pending request queue to avoid memory growth
          $rollingCurl->clearCompleted();
          $rollingCurl->prunePendingRequestQueue();
        })
        ->setSimultaneousLimit($request->simultaneous)
        ->execute()
      ;
      $this->output->writeln('<green>DONE</green> Blackhole processed ' . $pageCount . ' pages in ' . (microtime(true) - $start) . ' seconds');

    } else {
      $this->output->writeln('<red>ERROR</red> No pages were found');
    }
  }
}
