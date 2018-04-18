<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 03.04.2018
 * Time: 18:01
 */

namespace rtPiwikBundle\Services;

use rtPiwikBundle\Document\Metrics;
use rtPiwikBundle\Document\TotalMetric;

class TotalMetrics {
	private $metricsService;

	function __construct($metricsService) {
		$this->metricsService = $metricsService;
	}

	/**
	 * Get total metrics
	 * @param $board
	 * @param $slug
	 * @param \DateTime $date
	 * @param $userIds
	 * @param bool $reCalculate
	 * @return Metrics
	 */

	public function get($board, $slug, \DateTime $date, $userIds, $reCalculate = false) {

		$dateFrom = clone $date;
		$dateTo = null;
		$yesterday = (new \DateTime())->setTime(0, 0)->modify('-1 day');

		if (!$reCalculate && $metrics = $board->getMetrics()) {

			if ($lastCalculated = $metrics->getLastCalculated()) {
				if ($lastCalculated >= $yesterday) {
					return $metrics;
				}
				$dateFrom = $lastCalculated;
			}

		} else {
			$metrics = new Metrics();
		}

		while ($dateTo !== $yesterday) {

			$dateTmp = clone $dateFrom;
			$dateTo = $dateTmp->modify('+1 month');

			if ($dateTo > $yesterday) {
				$dateTo = $yesterday;
			}
			$metrics = $this->updateMetricsByBoard(
				$slug,
				$metrics,
				$board,
				$dateFrom->format('Y-m-d'),
				$dateTo->format('Y-m-d'),
				$userIds
			);

			$dateFrom = $dateTo;
			$metrics->setLastCalculated($dateTo);

		}

		return $metrics;
	}

	/**
	 * @param $slug
	 * @param Metrics $metrics
	 * @param $board
	 * @param $dateFrom
	 * @param $dateTo
	 * @param $userIds
	 * @return Metrics
	 */
	private function updateMetricsByBoard($slug, Metrics $metrics, $board, $dateFrom, $dateTo, $userIds) {
		$metricData = $this->metricsService->getMetrics($slug, $dateFrom, $dateTo, $userIds);

		if (is_null($metrics)) {
			$metrics = new Metrics();
			$totalMetric = new TotalMetric();

			$totalMetric->setVisits($metricData->getVisits());
			$totalMetric->setInteractions($metricData->getInteractions());
			$totalMetric->setAvgTimeSpent($metricData->getAvgTimeSpent());
			$totalMetric->setPageViews($metricData->getPageViews());

			$metrics->setTotalMetric($totalMetric);

			dump(sprintf("created:total slug:%s, dateFrom:%s, dateTo:%s", $slug, $dateFrom, $dateTo));
		} else {
			$totalMetric = $metrics->getTotalMetric();
			if (is_null($totalMetric)) {
				$totalMetric = new TotalMetric();
			}

			$visits = $totalMetric->getVisits() + $metricData->getVisits();
			$totalMetric->setVisits($visits);

			$interactions = $totalMetric->getInteractions() + $metricData->getInteractions();
			$totalMetric->setInteractions($interactions);

			if ($metricData->getAvgTimeSpent() > 0) {
				$avgTimeSpent = round(($totalMetric->getAvgTimeSpent() + $metricData->getAvgTimeSpent()) / 2);
				$totalMetric->setAvgTimeSpent($avgTimeSpent);
			}

			$pageViews = $totalMetric->getPageViews() + $metricData->getPageViews();
			$totalMetric->setPageViews($pageViews);

			$metrics->setUpdatedAt(new \DateTime());
			$metrics->setTotalMetric($totalMetric);

			dump(sprintf("updated:total slug:%s, dateFrom:%s, dateTo:%s", $slug, $dateFrom, $dateTo));
		}

		return $metrics;
	}
}