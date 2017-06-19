<?php
namespace Grav\Plugin\Console;

use Grav\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

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

    // curl function
    function pull($light) {
      $pull = curl_init();
      curl_setopt($pull, CURLOPT_URL, $light);
      curl_setopt($pull, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($pull, CURLOPT_FOLLOWLOCATION, 1);
      $emit = curl_exec($pull);
      curl_close($pull);
      return $emit;
    }

    // used to complete the output_url option
    function portal($content, $in, $out) {
      $wormhole = str_replace($in, $out, $content);
      return $wormhole;
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
      foreach ($pages as $route => $path) {
        $page_url = $input_url . $route;
        $page_route = preg_replace('/\/\/+/', '/', $event_horizon . $route);
        $page_path = preg_replace('/\/\/+/', '/', $page_route . '/index.html');
        $page_data = (!empty($output_url)
          ? portal(pull($page_url), $input_url, $output_url)
          : pull($page_url)
        );

        // if file exists
        if (file_exists($page_path)) {

          // if changes made, write a new copy
          if (filemtime($path) > filemtime($page_path)) {
            $this->output->writeln('<green>REGENERATING</green> ➜ ' . $page_route);
            file_put_contents($page_path, $page_data);

          // else do nothing
          } else {
            $this->output->writeln('<cyan>SKIPPING</cyan> No changes ➜ ' . $page_route);
          }

        // else create the file
        } else {
          $this->output->writeln('<green>GENERATING</green> ➜ ' . $page_route);
          if (!is_dir($page_route)) { mkdir($page_route, 0755); }
          file_put_contents($page_path, $page_data);
        }
      }
    } else {
      $this->output->writeln('<red>ERROR</red> No pages were found');
    }
  }
}