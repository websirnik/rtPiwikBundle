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
        $conn = $this->getContainer()->get('doctrine_mongodb')->getManager('conn1');

        $boardsTotal = $conn->createQueryBuilder('rtPiwikBundle:Board')->getQuery()->execute()->count();
        $i = 0;
        while ($boardsTotal > $i) {
            $this->getBatchTotalMetrics($conn, 100, $i);
            $i += 100;
        }

        $this->getBatchTotalMetrics($conn, $boardsTotal - $i, $i);
    }


    /**
     * Get all metrics form repository
     * @param $conn
     * @param null $limit - batch of items
     * @param null $skip - start from item
     */
    private function getBatchTotalMetrics($conn, $limit = null, $skip = null)
    {
        $boardsRepository = $conn->getRepository('rtPiwikBundle:Board')->findBy(
            array(),
            array('created' => 'desc'),
            $limit,
            $skip
        );

        foreach ($boardsRepository as $board) {
            $totalMetrics = new TotalMetrics;
            // get metrics for this board
            $metrics = $totalMetrics->get($board);
            // update metrics for this board
            $board->setMetrics($metrics);
            // TODO update bord
        }
    }
}
