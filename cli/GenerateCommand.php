<?php
namespace Grav\Plugin\Console;

use Grav\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;

class GenerateCommand extends ConsoleCommand
{
  protected $options = [];

  protected function configure()
  {
    // `generate` command settings
    $this
    ->setName("g")
    ->setName("gen")
    ->setName("generate")
    ->setDescription("Generates static site")
    ->addArgument(
      'domain',
      InputArgument::REQUIRED,
      'Set the domain name'
    )
    ->addArgument(
      'destination',
      InputArgument::OPTIONAL,
      'Set the output directory'
    );
  }

  protected function serve()
  {
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

    // get options
    $this->options = [ 'domain' => $this->input->getArgument('domain') ];
    $domain = $this->options['domain'];

    $this->options = [ 'destination' => $this->input->getArgument('destination') ];
    $destination = $this->options['destination'];

    // set output dir
    $event_horizon = GRAV_ROOT . '/';
    if ($destination) {
      $event_horizon .= $destination;
    } elseif (pull($domain . '/?pages=all&destination=true')) {
      $event_horizon .= pull($domain . '/?pages=all&destination=true');
    }

    // make output dir
    if (!is_dir(dirname($event_horizon))) { mkdir(dirname($event_horizon), 0755, true); }

    // get page routes
    $pages = json_decode(pull($domain . '/?pages=all'));

    // make pages in output dir
    if ($pages) {
      foreach ($pages as $page) {
        $page_dir = preg_replace( '/\/\/+/', '/', $event_horizon . $page);
        $this->output->writeln('<green>GENERATING</green> ' . $page_dir);
        if (!is_dir($page_dir)) { mkdir($page_dir, 0755, true); }
        file_put_contents($page_dir . '/index.html', pull($domain . $page));
      }
    } else {
      $this->output->writeln('<red>ERROR</red> Blackhole failed to start.');
    }
  }
}