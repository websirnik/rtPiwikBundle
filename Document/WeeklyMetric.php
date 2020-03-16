<?php
namespace rtPiwikBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use JMS\Serializer\Annotation as JMS;

/**
 * @MongoDB\EmbeddedDocument
 */
class WeeklyMetric {

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("integer")
	 * @MongoDB\Field(type="int")
	 */
	protected $visits;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("integer")
	 * @MongoDB\Field(type="int")
	 */
	protected $pageViews;

	/**
	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("integer")
	 * @MongoDB\Field(type="int")
	 */
	protected $interactions;

	/**

	 * @JMS\Groups({"metrics"})
	 * @JMS\Type("integer")
	 * @MongoDB\Field(type="int")
	 */
	protected $avgTimeSpent;

    /**
     * @JMS\Groups({"metrics"})
     * @JMS\Type("integer")
     * @MongoDB\Field(type="int")
     */
    protected $sumTimeSpent;

    /**
     * @JMS\Groups({"metrics"})
     * @JMS\Type("integer")
     * @MongoDB\Field(type="int")
     */
    protected $experienceViewed;

	/**
	 * Set visits
	 *
	 * @param int $visits
	 * @return $this
	 */
	public function setVisits($visits) {
		$this->visits = $visits;
		return $this;
	}

	/**
	 * Get visits
	 *
	 * @return int $visits
	 */
	public function getVisits() {
		return $this->visits;
	}

	/**
	 * Set pageViews
	 *
	 * @param int $pageViews
	 * @return $this
	 */
	public function setPageViews($pageViews) {
		$this->pageViews = $pageViews;
		return $this;
	}

	/**
	 * Get pageViews
	 *
	 * @return int $pageViews
	 */
	public function getPageViews() {
		return $this->pageViews;
	}

	/**
	 * Set interactions
	 *
	 * @param int $interactions
	 * @return $this
	 */
	public function setInteractions($interactions) {
		$this->interactions = $interactions;
		return $this;
	}

	/**
	 * Get interactions
	 *
	 * @return int $interactions
	 */
	public function getInteractions() {
		return $this->interactions;
	}

	/**
	 * Set avgTimeSpent
	 *
	 * @param int $avgTimeSpent
	 * @return $this
	 */
	public function setAvgTimeSpent($avgTimeSpent) {
		$this->avgTimeSpent = $avgTimeSpent;
		return $this;
	}

	/**
	 * Get avgTimeSpent
	 *
	 * @return int $avgTimeSpent
	 */
	public function getAvgTimeSpent() {
		return $this->avgTimeSpent;
	}

	/**
	 * Get id
	 *
	 * @return id $id
	 */
	public function getId() {
		return $this->id;
	}

    /**
     * @return mixed
     */
    public function getSumTimeSpent()
    {
        return $this->sumTimeSpent;
    }

    /**
     * @param mixed $sumTimeSpent
     */
    public function setSumTimeSpent($sumTimeSpent): void
    {
        $this->sumTimeSpent = $sumTimeSpent;
    }

    /**
     * @return mixed
     */
    public function getExperienceViewed()
    {
        return $this->experienceViewed;
    }

    /**
     * @param mixed $experienceViewed
     */
    public function setExperienceViewed($experienceViewed): void
    {
        $this->experienceViewed = $experienceViewed;
    }
}
