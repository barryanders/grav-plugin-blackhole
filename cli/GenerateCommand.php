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
    } elseif (!empty(pull($input_url . '/?pages=all&output-path=true'))) {
      $event_horizon .= pull($input_url . '/?pages=all&output-path=true'); // appends user defined output path in plugin settings
    }

    // make output path
    if (!is_dir(dirname($event_horizon))) { mkdir(dirname($event_horizon), 0755, true); }

    // get page routes
    $pages = json_decode(pull($input_url . '/?pages=all'));

    // make pages in output path
    if (count($pages)) {
      foreach ($pages as $page) {
        $page_path = preg_replace('/\/\/+/', '/', $event_horizon . $page);
        $file_path = preg_replace('/\/\/+/', '/', $page_path . '/index.html');
        $star = $input_url . $page;
        $universe = !empty($output_url) ? portal(pull($star), $input_url, $output_url) : pull($star);
          $this->output->writeln('<green>GENERATING</green> ' . $page_path);
          if (!is_dir($page_path)) { mkdir($page_path, 0755); }
          file_put_contents($file_path, $universe);
      }
    } else {
      $this->output->writeln('<red>ERROR</red> No pages were found.');
    }
  }
}