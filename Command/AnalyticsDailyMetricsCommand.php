<?php

namespace PiwikBundle\Command;

use PiwikBundle\Services\LastDayMetrics;
use PiwikBundle\Services\LastWeekMetrics;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyticsDailyMetricsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('analytics:daily-metrics')
            ->setDescription(
                'Getting daily analytics data for all documents on R/. We will setup cron job that will execute this.command daily'
            )
            ->setHelp('Create a console command to get daily metrics for documents')
            ->addArgument('argument', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $db = $this->getContainer()->get('doctrine_mongodb');

        $localConn = $db->getManager('conn1');
        $remoteConn = $db->getManager('conn2');

        $lasDayMetrics = new LastDayMetrics($localConn, $remoteConn);
        $lasDayMetrics->execute();

        $lasWeekMetrics = new LastWeekMetrics($localConn, $remoteConn);
        $lasWeekMetrics->execute();

        $output->writeln('ok');
    }
}
