<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 30.03.2018
 * Time: 13:58
 */

namespace rtPiwikBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;

/**
 * @MongoDB\EmbeddedDocument
 */
class PercentageChangeLastWeekMetric {

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("float")
	 * @MongoDB\Field(type="float")
	 */
	protected $visits;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("float")
	 * @MongoDB\Field(type="float")
	 */
	protected $pageViews;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("float")
	 * @MongoDB\Field(type="float")
	 */
	protected $interactions;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("float")
	 * @MongoDB\Field(type="float")
	 */
	protected $avgTimeSpent;

	/**
	 * Set visits
	 *
	 * @param float $visits
	 * @return $this
	 */
	public function setVisits($visits) {
		$this->visits = $visits;
		return $this;
	}

	/**
	 * Get visits
	 *
	 * @return float $visits
	 */
	public function getVisits() {
		return $this->visits;
	}

	/**
	 * Set pageViews
	 *
	 * @param float $pageViews
	 * @return $this
	 */
	public function setPageViews($pageViews) {
		$this->pageViews = $pageViews;
		return $this;
	}

	/**
	 * Get pageViews
	 *
	 * @return float $pageViews
	 */
	public function getPageViews() {
		return $this->pageViews;
	}

	/**
	 * Set interactions
	 *
	 * @param float $interactions
	 * @return $this
	 */
	public function setInteractions($interactions) {
		$this->interactions = $interactions;
		return $this;
	}

	/**
	 * Get interactions
	 *
	 * @return float $interactions
	 */
	public function getInteractions() {
		return $this->interactions;
	}

	/**
	 * Set avgTimeSpent
	 *
	 * @param float $avgTimeSpent
	 * @return $this
	 */
	public function setAvgTimeSpent($avgTimeSpent) {
		$this->avgTimeSpent = $avgTimeSpent;
		return $this;
	}

	/**
	 * Get avgTimeSpent
	 *
	 * @return float $avgTimeSpent
	 */
	public function getAvgTimeSpent() {
		return $this->avgTimeSpent;
	}
}
