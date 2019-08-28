<?php

namespace rtPiwikBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;

/**
 * @MongoDB\EmbeddedDocument
 */
class Metrics {

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("rtPiwikBundle\Document\DailyMetric")
	 * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\DailyMetric")
	 */
	protected $dailyMetric;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("rtPiwikBundle\Document\WeeklyMetric")
	 * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\WeeklyMetric")
	 */
	protected $weeklyMetric;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("rtPiwikBundle\Document\TotalMetric")
	 * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\TotalMetric")
	 */
	protected $totalMetric;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("rtPiwikBundle\Document\DailyPercentageChangeMetric")
	 * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\DailyPercentageChangeMetric")
	 */
	protected $dailyPercentageChange;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("rtPiwikBundle\Document\WeeklyPercentageChangeMetric")
	 * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\WeeklyPercentageChangeMetric")
	 */
	protected $weeklyPercentageChange;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("DateTime")
	 * @MongoDB\Field(type="date")
	 */
	protected $createdAt;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("DateTime")
	 * @MongoDB\Field(type="date")
	 */
	protected $updatedAt;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("DateTime")
	 * @MongoDB\Field(type="date")
	 */
	protected $lastCalculated;

	function __construct() {
		$this->createdAt = new \DateTime();
	}

	/**
	 * Set dailyMetric
	 *
	 * @param \rtPiwikBundle\Document\DailyMetric $dailyMetric
	 * @return $this
	 */
	public function setDailyMetric(\rtPiwikBundle\Document\DailyMetric $dailyMetric) {
		$this->dailyMetric = $dailyMetric;

		return $this;
	}

	/**
	 * Get dailyMetric
	 *
	 * @return \rtPiwikBundle\Document\DailyMetric $dailyMetric
	 */
	public function getDailyMetric() {
		return $this->dailyMetric;
	}

	/**
	 * Set weeklyMetric
	 *
	 * @param \rtPiwikBundle\Document\WeeklyMetric $weeklyMetric
	 * @return $this
	 */
	public function setWeeklyMetric(\rtPiwikBundle\Document\WeeklyMetric $weeklyMetric) {
		$this->weeklyMetric = $weeklyMetric;

		return $this;
	}

	/**
	 * Get weeklyMetric
	 *
	 * @return \rtPiwikBundle\Document\WeeklyMetric$weeklyMetric
	 */
	public function getWeeklyMetric() {
		return $this->weeklyMetric;
	}

	/**
	 * Set totalMetric
	 *
	 * @param \rtPiwikBundle\Document\TotalMetric $totalMetric
	 * @return $this
	 */
	public function setTotalMetric(\rtPiwikBundle\Document\TotalMetric $totalMetric) {
		$this->totalMetric = $totalMetric;

		return $this;
	}

	/**
	 * Get totalMetric
	 *
	 * @return \rtPiwikBundle\Document\TotalMetric $totalMetric
	 */
	public function getTotalMetric() {
		return $this->totalMetric;
	}

	/**
	 * Set createdAt
	 *
	 * @param \DateTime $createdAt
	 * @return $this
	 */
	public function setCreatedAt($createdAt) {
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * Get createdAt
	 *
	 * @return \DateTime $createdAt
	 */
	public function getCreatedAt() {
		return $this->createdAt;
	}

	/**
	 * Set updatedAt
	 *
	 * @param \DateTime $updatedAt
	 * @return $this
	 */
	public function setUpdatedAt($updatedAt) {
		$this->updatedAt = $updatedAt;

		return $this;
	}

	/**
	 * Get updatedAt
	 *
	 * @return \DateTime $updatedAt
	 */
	public function getUpdatedAt() {
		return $this->updatedAt;
	}

	/**
	 * Set lastCalculated
	 *
	 * @param \DateTime $lastCalculated
	 * @return $this
	 */
	public function setLastCalculated($lastCalculated) {
		$this->lastCalculated = $lastCalculated;

		return $this;
	}

	/**
	 * Get lastCalculated
	 *
	 * @return \DateTime $lastCalculated
	 */
	public function getLastCalculated() {
		return $this->lastCalculated;
	}

	/**
	 * Set dailyPercentageChange
	 *
	 * @param \rtPiwikBundle\Document\DailyPercentageChangeMetric $dailyPercentageChange
	 * @return $this
	 */
	public function setDailyPercentageChange(
		\rtPiwikBundle\Document\DailyPercentageChangeMetric $dailyPercentageChange
	) {
		$this->dailyPercentageChange = $dailyPercentageChange;

		return $this;
	}

	/**
	 * Get dailyPercentageChange
	 *
	 * @return \rtPiwikBundle\Document\DailyPercentageChangeMetric $dailyPercentageChange
	 */
	public function getDailyPercentageChange() {
		return $this->dailyPercentageChange;
	}

	/**
	 * Set weeklyPercentageChange
	 *
	 * @param \rtPiwikBundle\Document\WeeklyPercentageChangeMetric $weeklyPercentageChange
	 * @return $this
	 */
	public function setWeeklyPercentageChange(
		\rtPiwikBundle\Document\WeeklyPercentageChangeMetric $weeklyPercentageChange
	) {
		$this->weeklyPercentageChange = $weeklyPercentageChange;

		return $this;
	}

	/**
	 * Get weeklyPercentageChange
	 *
	 * @return \rtPiwikBundle\Document\WeeklyPercentageChangeMetric $weeklyPercentageChange
	 */
	public function getWeeklyPercentageChange() {
		return $this->weeklyPercentageChange;
	}
}
