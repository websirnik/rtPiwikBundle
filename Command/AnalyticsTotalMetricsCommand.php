<?php

namespace rtPiwikBundle\Command;

use rtPiwikBundle\Services\TotalMetrics;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyticsTotalMetricsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('analytics:total-metrics')
            ->setDescription(
                'Getting total aggregate analytics data for all documents on R/. This command will be run just ones'
            )
            ->setHelp('Create a console command to get historic metrics for documents')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $this->getContainer()->get('doctrine_mongodb');

        $localConn = $db->getManager('conn1');
        $remoteConn = $db->getManager('conn2');

        $totalMetrics = new TotalMetrics($localConn, $remoteConn);
        $totalMetrics->execute();

        $output->writeln('ok');
    }
}
