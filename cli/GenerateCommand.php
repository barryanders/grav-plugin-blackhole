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
      'website',
      InputArgument::REQUIRED,
      'Set the URL to your Grav website.'
    )
    ->addArgument(
      'destination',
      InputArgument::OPTIONAL,
      'Set the output directory for your static website.'
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
    $this->options = [ 'website' => $this->input->getArgument('website') ];
    $website = $this->options['website'];

    $this->options = [ 'destination' => $this->input->getArgument('destination') ];
    $destination = $this->options['destination'];

    // set output dir
    $event_horizon = GRAV_ROOT . '/';
    if ($destination) {
      $event_horizon .= $destination;
    } elseif (pull($website . '/?pages=all&destination=true')) {
      $event_horizon .= pull($website . '/?pages=all&destination=true');
    }

    // make output dir
    if (!is_dir(dirname($event_horizon))) { mkdir(dirname($event_horizon), 0755, true); }

    // get page routes
    $pages = json_decode(pull($website . '/?pages=all'));

    // make pages in output dir
    if (count($pages)) {
      foreach ($pages as $page) {
        $page_dir = preg_replace( '/\/\/+/', '/', $event_horizon . $page);
        $this->output->writeln('<green>GENERATING</green> ' . $page_dir);
        if (!is_dir($page_dir)) { mkdir($page_dir, 0755, true); }
        file_put_contents($page_dir . '/index.html', pull($website . $page));
      }
    } else {
      $this->output->writeln('<red>ERROR</red> Blackhole failed to start.');
      $this->output->writeln('<red>ERROR</red> The website must match the location of your Grav installation.');
      $this->output->writeln('<red>ERROR</red> You must have at least one page.');
    }
  }
}