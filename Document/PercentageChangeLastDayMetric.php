<?php
/**
 * Created by PhpStorm.
 * User: skews
 * Date: 30.03.2018
 * Time: 13:58
 */

namespace PiwikBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\EmbeddedDocument
 */
class PercentageChangeLastDayMetric
{

    /**
     * @MongoDB\Field(type="float")
     */
    protected $visits;

    /**
     * @MongoDB\Field(type="float")
     */
    protected $pageViews;

    /**
     * @MongoDB\Field(type="float")
     */
    protected $interactions;

    /**
     * @MongoDB\Field(type="float")
     */
    protected $avgTimeSpent;



    /**
     * Set visits
     *
     * @param float $visits
     * @return $this
     */
    public function setVisits($visits)
    {
        $this->visits = $visits;
        return $this;
    }

    /**
     * Get visits
     *
     * @return float $visits
     */
    public function getVisits()
    {
        return $this->visits;
    }

    /**
     * Set pageViews
     *
     * @param float $pageViews
     * @return $this
     */
    public function setPageViews($pageViews)
    {
        $this->pageViews = $pageViews;
        return $this;
    }

    /**
     * Get pageViews
     *
     * @return float $pageViews
     */
    public function getPageViews()
    {
        return $this->pageViews;
    }

    /**
     * Set interactions
     *
     * @param float $interactions
     * @return $this
     */
    public function setInteractions($interactions)
    {
        $this->interactions = $interactions;
        return $this;
    }

    /**
     * Get interactions
     *
     * @return float $interactions
     */
    public function getInteractions()
    {
        return $this->interactions;
    }

    /**
     * Set avgTimeSpent
     *
     * @param float $avgTimeSpent
     * @return $this
     */
    public function setAvgTimeSpent($avgTimeSpent)
    {
        $this->avgTimeSpent = $avgTimeSpent;
        return $this;
    }

    /**
     * Get avgTimeSpent
     *
     * @return float $avgTimeSpent
     */
    public function getAvgTimeSpent()
    {
        return $this->avgTimeSpent;
    }
}
