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
        $conn = $this->getContainer()->get('doctrine_mongodb')->getManager('conn2');

        $this->getLastDayMetrics($conn);

        $this->getLastWeekMetrics($conn);

        $output->writeln('ok');
    }


    public function getLastDayMetrics($conn)
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
            $lasDayMetrics = new LastDayMetrics($board, $userIds);
            // get metrics for this board
            $metrics = $lasDayMetrics->get($yesterday);
            // update metrics for this board
            dump($metrics);
        }
    }

    public function getLastWeekMetrics($conn)
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

        $date = new \DateTime();
        $ts = $date->getTimestamp() - 60 * 60 * 24 * 6;
        $lastWeek = $date->setTimestamp($ts);

        $boardsRepository = $conn
            ->getRepository('rtPiwikBundle:Board')
            ->findBy(
                array('updated' => array('$gt' => $lastWeek)),
                array('created' => 'desc'),
                null,
                null
            );

        foreach ($boardsRepository as $board) {
            $lastWeekMetrics = new LastWeekMetrics($board, $userIds);
            // get metrics for this board
            $metrics = $lastWeekMetrics->get($lastWeek);
            // update metrics for this board
            dump($metrics);
        }
    }


}
