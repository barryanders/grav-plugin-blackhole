<?php
namespace Grav\Plugin\Console;

use Grav\Console\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;

class GenerateCommand extends ConsoleCommand
{
  protected $options = [];

  protected function configure()
  {
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
    )
    ;
  }

  protected function serve()
  {
    // GRAV_HTTP converts GRAV_ROOT into an http link
    $this->options = [ 'domain' => $this->input->getArgument('domain') ];
    $domain = $this->options['domain'];
    define(GRAV_HTTP, 'http://' . $domain . str_replace(dirname(getcwd()), '', GRAV_ROOT));
    function pull($url) {
      $pull = curl_init();
      curl_setopt($pull, CURLOPT_URL, GRAV_HTTP . $url);
      curl_setopt($pull, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($pull, CURLOPT_FOLLOWLOCATION, 1);
      $emit = curl_exec($pull);
      curl_close($pull);
      return $emit;
    }

    // set build dir
    $event_horizon = GRAV_ROOT . '/';
    $this->options = [ 'destination' => $this->input->getArgument('destination') ];
    if ($this->options['destination']) {
      $event_horizon .= $this->options['destination'];
    } elseif (pull('/?pages=all&destination=true')) {
      $event_horizon .= pull('/?pages=all&destination=true');
    }

    // make build dir
    if (!is_dir(dirname($event_horizon))) { mkdir(dirname($event_horizon), 0755, true); }

    // get page routes
    $pages = json_decode(pull('/?pages=all'));

    // make pages in build dir
    if ($pages) {
      foreach ($pages as $page) {
        $page_dir = $event_horizon . $page;
        $this->output->writeln('<green>GENERATING</green> ' . $page_dir);
        if (!is_dir($page_dir)) { mkdir($page_dir, 0755, true); }
        file_put_contents($page_dir . '/index.html', pull($page));
      }
    } else {
      $this->output->writeln('<red>ERROR</red> Blackhole failed to start.');
    }
  }
}