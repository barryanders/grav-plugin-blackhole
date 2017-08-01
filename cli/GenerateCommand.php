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
    );
  }

  protected function serve() {

    // get options
    $this->options = [ 'input-url' => $this->input->getArgument('input-url') ];
    $input_url = $this->options['input-url'];

    $this->options = [ 'output-url' => $this->input->getOption('output-url') ];
    $output_url = $this->options['output-url'];

    $this->options = [ 'output-path' => $this->input->getOption('output-path') ];
    $output_path = $this->options['output-path'];

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

    // swaps input_url with output_url in page code
    function portal($in, $out, $content) {
      $wormhole = str_ireplace($in, $out, $content);
      return $wormhole;
    }

    // write files
    function generate($bh_route, $bh_file_path, $grav_page_data) {
      if (!is_dir($bh_route)) { mkdir($bh_route, 0755, true); }
      file_put_contents($bh_file_path, $grav_page_data);
    }

    // set output path
    $event_horizon = GRAV_ROOT . '/'; // defaults to grav_root
    if (!empty($output_path)) {
      $event_horizon .= $output_path; // appends user defined output path in CL
    } elseif (!empty(pull($input_url . '/?pages=all&output_path=true'))) {
      $event_horizon .= pull($input_url . '/?pages=all&output_path=true'); // appends user defined output path in plugin settings
    }
    // make output path
    if (!is_dir(dirname($event_horizon))) { mkdir(dirname($event_horizon), 0755, true); }

    // get page routes
    $pages = json_decode(pull($input_url . '/?pages=all'));

    // make pages in output path
    if (count($pages)) {
      $rollingCurl = new \RollingCurl\RollingCurl();
      foreach ($pages as $grav_route => $grav_file_path) {
        $request = new \RollingCurl\Request($input_url . $grav_route);
        $request->grav_file_path = $grav_file_path;
        $request->bh_route = preg_replace('/\/\/+/', '/', $event_horizon . $grav_route);
        $request->bh_file_path = preg_replace('/\/\/+/', '/', $request->bh_route . '/index.html');
        $rollingCurl->add($request);
      }

      $start = microtime(true);
      $rollingCurl
        ->setCallback(function(\RollingCurl\Request $request, \RollingCurl\RollingCurl $rollingCurl) {
          $grav_page_data = (!empty($output_url)
            ? portal($input_url, $output_url, $request->getResponseText())
            : $request->getResponseText()
          );
          // page exists
          if (file_exists($request->bh_file_path)) {
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
          // page doesn't exist
          } else {
            // copy the new page
            generate($request->bh_route, $request->bh_file_path, $grav_page_data);
            $this->output->writeln('<green>GENERATING</green> ➜ ' . realpath($request->bh_route));
          }
          // clear list of completed requests and prune pending request queue to avoid memory growth
          $rollingCurl->clearCompleted();
          $rollingCurl->prunePendingRequestQueue();
        })
        ->setSimultaneousLimit(20)
        ->execute()
      ;
      $this->output->writeln("Done in " . (microtime(true) - $start) . ' seconds');

    } else {
      $this->output->writeln('<red>ERROR</red> No pages were found');
    }
  }
}