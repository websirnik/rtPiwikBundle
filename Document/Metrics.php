<?php

namespace PiwikBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;


/**
 * @MongoDB\Document
 */
class Metrics
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $slug;

    /**
     * @MongoDB\EmbedOne(targetDocument="PiwikBundle\Document\LastDayMetric")
     */
    protected $lastDayMetric;

    /**
     * @MongoDB\EmbedOne(targetDocument="PiwikBundle\Document\LastWeekMetric")
     */
    protected $lastWeekMetric;

    /**
     * @MongoDB\EmbedOne(targetDocument="PiwikBundle\Document\TotalMetric")
     */
    protected $totalMetric;


    /**
     * @MongoDB\EmbedOne(targetDocument="PiwikBundle\Document\PercentageChangeLastDayMetric")
     */
    protected $percentageChangeLastDay;


    /**
     * @MongoDB\EmbedOne(targetDocument="PiwikBundle\Document\PercentageChangeLastWeekMetric")
     */
    protected $percentageChangeLastWeek;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $createdAt;

    /**
     * @MongoDB\Field(type="date")
     */
    protected $updatedAt;

    function __construct()
    {
        $this->createdAt = new \DateTime();
    }

   

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set slug
     *
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * Get slug
     *
     * @return string $slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set lastDayMetric
     *
     * @param \PiwikBundle\Document\LastDayMetric $lastDayMetric
     * @return $this
     */
    public function setLastDayMetric(\PiwikBundle\Document\LastDayMetric $lastDayMetric)
    {
        $this->lastDayMetric = $lastDayMetric;
        return $this;
    }

    /**
     * Get lastDayMetric
     *
     * @return \PiwikBundle\Document\LastDayMetric $lastDayMetric
     */
    public function getLastDayMetric()
    {
        return $this->lastDayMetric;
    }

    /**
     * Set lastWeekMetric
     *
     * @param \PiwikBundle\Document\LastWeekMetric $lastWeekMetric
     * @return $this
     */
    public function setLastWeekMetric(\PiwikBundle\Document\LastWeekMetric $lastWeekMetric)
    {
        $this->lastWeekMetric = $lastWeekMetric;
        return $this;
    }

    /**
     * Get lastWeekMetric
     *
     * @return \PiwikBundle\Document\LastWeekMetric $lastWeekMetric
     */
    public function getLastWeekMetric()
    {
        return $this->lastWeekMetric;
    }

    /**
     * Set totalMetric
     *
     * @param \PiwikBundle\Document\TotalMetric $totalMetric
     * @return $this
     */
    public function setTotalMetric(\PiwikBundle\Document\TotalMetric $totalMetric)
    {
        $this->totalMetric = $totalMetric;
        return $this;
    }

    /**
     * Get totalMetric
     *
     * @return \PiwikBundle\Document\TotalMetric $totalMetric
     */
    public function getTotalMetric()
    {
        return $this->totalMetric;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime $updatedAt
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set percentageChangeLastDay
     *
     * @param \PiwikBundle\Document\PercentageChangeLastDayMetric $percentageChangeLastDay
     * @return $this
     */
    public function setPercentageChangeLastDay(\PiwikBundle\Document\PercentageChangeLastDayMetric $percentageChangeLastDay)
    {
        $this->percentageChangeLastDay = $percentageChangeLastDay;
        return $this;
    }

    /**
     * Get percentageChangeLastDay
     *
     * @return \PiwikBundle\Document\PercentageChangeLastDayMetric $percentageChangeLastDay
     */
    public function getPercentageChangeLastDay()
    {
        return $this->percentageChangeLastDay;
    }

    /**
     * Set percentageChangeLastWeek
     *
     * @param \PiwikBundle\Document\PercentageChangeLastWeekMetric $percentageChangeLastWeek
     * @return $this
     */
    public function setPercentageChangeLastWeek(\PiwikBundle\Document\PercentageChangeLastWeekMetric $percentageChangeLastWeek)
    {
        $this->percentageChangeLastWeek = $percentageChangeLastWeek;
        return $this;
    }

    /**
     * Get percentageChangeLastWeek
     *
     * @return \PiwikBundle\Document\PercentageChangeLastWeekMetric $percentageChangeLastWeek
     */
    public function getPercentageChangeLastWeek()
    {
        return $this->percentageChangeLastWeek;
    }
}
