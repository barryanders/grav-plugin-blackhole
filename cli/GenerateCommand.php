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
      'destination',
      InputArgument::OPTIONAL,
      'The destination of your static site'
    )
    ->setHelp('The <info>gen</info> generates a static copy of the website.')
    ;
  }

  protected function serve()
  {
    define(GRAV_HTTP, 'http:/'.str_replace($_SERVER['HOME'], '', GRAV_ROOT));
    function pull($url) {
      $pull = curl_init();
      curl_setopt($pull, CURLOPT_URL, GRAV_HTTP . $url);
      curl_setopt($pull, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($pull, CURLOPT_FOLLOWLOCATION, 1);
      $emit = curl_exec($pull);
      curl_close($pull);
      return $emit;
    }
    // Set build dir
    $event_horizon;
    $this->options = [
      'destination' => $this->input->getArgument('destination')
    ];
    if ($this->options['destination']) {
      $event_horizon = GRAV_ROOT . '/' . $this->options['destination'];
    } else {
      $event_horizon = GRAV_ROOT . '/_site';
    }
    // Make build dir
    if (!is_dir(dirname($event_horizon))) {
      mkdir(dirname($event_horizon), 0755, true);
    }
    // Get page routes
    $pull_routes = pull('/routes/?grav=pages');
    preg_match("/`\[(.*?)\]`/", $pull_routes, $json);
    $pages = json_decode('[' . $json[1] . ']', true);
    // Make pages in build dir
    foreach ($pages as $page) {
      $page_dir = $event_horizon . $page['route'];
      $this->output->writeln('<green>GENERATING</green> ' . $page_dir);
      if (!is_dir($page_dir)) { mkdir($page_dir, 0755, true); }
      $static_page = fopen($page_dir . '/index.html', "a");
      $pull_page = pull($page['route']);
      fwrite($static_page, $pull_page);
      fclose($static_page);
    }
  }
}