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
	 * @JMS\Type("rtPiwikBundle\Document\LastDayMetric")
	 * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\LastDayMetric")
	 */
	protected $lastDayMetric;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("rtPiwikBundle\Document\LastWeekMetric")
	 * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\LastWeekMetric")
	 */
	protected $lastWeekMetric;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("rtPiwikBundle\Document\TotalMetric")
	 * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\TotalMetric")
	 */
	protected $totalMetric;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("rtPiwikBundle\Document\PercentageChangeLastDayMetric")
	 * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\PercentageChangeLastDayMetric")
	 */
	protected $percentageChangeLastDay;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("rtPiwikBundle\Document\PercentageChangeLastWeekMetric")
	 * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\PercentageChangeLastWeekMetric")
	 */
	protected $percentageChangeLastWeek;

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
	 * Set lastDayMetric
	 *
	 * @param \rtPiwikBundle\Document\LastDayMetric $lastDayMetric
	 * @return $this
	 */
	public function setLastDayMetric(\rtPiwikBundle\Document\LastDayMetric $lastDayMetric) {
		$this->lastDayMetric = $lastDayMetric;

		return $this;
	}

	/**
	 * Get lastDayMetric
	 *
	 * @return \rtPiwikBundle\Document\LastDayMetric $lastDayMetric
	 */
	public function getLastDayMetric() {
		return $this->lastDayMetric;
	}

	/**
	 * Set lastWeekMetric
	 *
	 * @param \rtPiwikBundle\Document\LastWeekMetric $lastWeekMetric
	 * @return $this
	 */
	public function setLastWeekMetric(\rtPiwikBundle\Document\LastWeekMetric $lastWeekMetric) {
		$this->lastWeekMetric = $lastWeekMetric;

		return $this;
	}

	/**
	 * Get lastWeekMetric
	 *
	 * @return \rtPiwikBundle\Document\LastWeekMetric $lastWeekMetric
	 */
	public function getLastWeekMetric() {
		return $this->lastWeekMetric;
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
	 * Set percentageChangeLastDay
	 *
	 * @param \rtPiwikBundle\Document\PercentageChangeLastDayMetric $percentageChangeLastDay
	 * @return $this
	 */
	public function setPercentageChangeLastDay(
		\rtPiwikBundle\Document\PercentageChangeLastDayMetric $percentageChangeLastDay
	) {
		$this->percentageChangeLastDay = $percentageChangeLastDay;

		return $this;
	}

	/**
	 * Get percentageChangeLastDay
	 *
	 * @return \rtPiwikBundle\Document\PercentageChangeLastDayMetric $percentageChangeLastDay
	 */
	public function getPercentageChangeLastDay() {
		return $this->percentageChangeLastDay;
	}

	/**
	 * Set percentageChangeLastWeek
	 *
	 * @param \rtPiwikBundle\Document\PercentageChangeLastWeekMetric $percentageChangeLastWeek
	 * @return $this
	 */
	public function setPercentageChangeLastWeek(
		\rtPiwikBundle\Document\PercentageChangeLastWeekMetric $percentageChangeLastWeek
	) {
		$this->percentageChangeLastWeek = $percentageChangeLastWeek;

		return $this;
	}

	/**
	 * Get percentageChangeLastWeek
	 *
	 * @return \rtPiwikBundle\Document\PercentageChangeLastWeekMetric $percentageChangeLastWeek
	 */
	public function getPercentageChangeLastWeek() {
		return $this->percentageChangeLastWeek;
	}
}
