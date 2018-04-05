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
        $userIds = [
            "51dbeae06a0239d21f3b436e",
            "52529972c301bf6604e7931d",
            "5697b7c6296fd3cc638b476e",
            "59a01dfc1d76fc545528aba4",
            "597e3bf71d76fc393954d4aa",
            "5a4d12ea1d76fc5fdc0e8c6d",
            "5a27531f1d76fc42895dff82",
            "587d4a8e0c87bbca148b482d",
            "5a3823181d76fc50c975c18b",
            "566946fbbe562b4f588b4641",
            "574dcde4a7e80ea4118b7847",
            "u5a4e38771d76fc61796b4ba3",
        ];

        $boardsRepository = $conn->getRepository('rtPiwikBundle:Board')->findBy(
            array(),
            array('created' => 'desc'),
            $limit,
            $skip
        );

        foreach ($boardsRepository as $board) {
            $totalMetrics = new TotalMetrics($board, $userIds);

            // get metrics for this board
            $metrics = $totalMetrics->get($board->getCreated());
            // update metrics for this board
            $board->setMetrics($metrics);
            // TODO update board
        }
    }
}
