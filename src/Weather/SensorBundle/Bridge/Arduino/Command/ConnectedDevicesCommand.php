<?php
namespace Weather\SensorBundle\Bridge\Arduino\Command;

use Symfony\Component\Console\Command\Command;
use Weather\SensorBundle\Bridge\Arduino\SensorManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConnectedDevicesCommand extends Command
{
    /** @var SensorManager $sensorManager */
    protected $sensorManager;

    public function __construct(SensorManager $sensorManager)
    {
        $this->sensorManager = $sensorManager;

        parent::__construct();
    }
    protected function configure()
    {
        $this
            ->setName('arduino:devices:refresh')
            ->setDescription('Refresh state of all connected devices')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Checking for connected devices.');
        $this->sensorManager->refreshConnectedDevices();
        $output->writeln('State of connected devices refreshed.');
    }
}