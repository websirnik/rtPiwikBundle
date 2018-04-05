<?php
namespace rtPiwikBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;


/**
 * @MongoDB\Document
 */
class Board
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\EmbedOne(targetDocument="rtPiwikBundle\Document\Metrics")
     */
    protected $metrics;

    /**
     * Set metrics
     *
     * @param \rtPiwikBundle\Document\Metrics $metrics
     * @return $this
     */
    public function setMetrics(\rtPiwikBundle\Document\Metrics $metrics)
    {
        $this->metrics = $metrics;
        return $this;
    }

    /**
     * Get metrics
     *
     * @return \rtPiwikBundle\Document\Metrics $metrics
     */
    public function getMetrics()
    {
        return $this->metrics;
    }

    /**
     * @MongoDB\Field(type="string")
     */
    protected $slug;

    /**
     * @MongoDB\Date
     */
    protected $created;

    /**
     * @MongoDB\Date
     */
    protected $updated;

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
     * Set created
     *
     * @param date $created
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime $created
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param date $updated
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    /**
     * Get updated
     *
     * @return date $updated
     */
    public function getUpdated()
    {
        return $this->updated;
    }
}
