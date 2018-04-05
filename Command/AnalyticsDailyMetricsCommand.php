<?php

namespace rtPiwikBundle\Command;

use rtPiwikBundle\Services\LastDayMetrics;
use rtPiwikBundle\Services\LastWeekMetrics;
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
        $conn = $this->getContainer()->get('doctrine_mongodb')->getManager('conn1');

        $this->getLastDayMetrics($conn);

//        $lasWeekMetrics = new LastWeekMetrics($localConn, $remoteConn);
//        $lasWeekMetrics->execute();

        $output->writeln('ok');
    }


    public function getLastDayMetrics($conn)
    {
        $date = new \DateTime();
        $ts = $date->getTimestamp() - 60 * 60 * 24;
        $yesterday = $date->setTimestamp($ts);

        $boardsRepository = $conn
            ->getRepository('rtPiwikBundle:Board')
            ->findBy(
                array('updated' => array('$gt' => $yesterday)),
                array('created' => 'desc'),
                null,
                null
            );

        foreach ($boardsRepository as $board) {
            $lasDayMetrics = new LastDayMetrics($board, $yesterday);
            // get metrics for this board
            $metrics = $lasDayMetrics->get($board);
            // update metrics for this board
            $board->setMetrics($metrics);
            // TODO update bord
        }
    }


}
