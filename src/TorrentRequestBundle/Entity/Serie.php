<?php

namespace TorrentRequestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="serie")
 * @ORM\Entity(repositoryClass="TorrentRequestBundle\Entity\SerieRepository")
 */
class Serie
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @ORM\Column(type="string", length=100)
     */
    protected $name;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $season;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $episode;
    
    /**
     * @ORM\Column(type="integer")
     */
    protected $status = 0;
    
    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $original_filename;
    
    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $transmission_id;
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Request
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set season
     *
     * @param integer $season
     * @return Request
     */
    public function setSeason($season)
    {
        $this->season = $season;

        return $this;
    }

    /**
     * Get season
     *
     * @return integer 
     */
    public function getSeason()
    {
        return $this->season;
    }

    /**
     * Set episode
     *
     * @param integer $episode
     * @return Request
     */
    public function setEpisode($episode)
    {
        $this->episode = $episode;

        return $this;
    }

    /**
     * Get episode
     *
     * @return integer 
     */
    public function getEpisode()
    {
        return $this->episode;
    }

    /**
     * Set resolution
     *
     * @param integer $resolution
     * @return Request
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * Get resolution
     *
     * @return integer 
     */
    public function getResolution()
    {
        return $this->resolution;
    }
    
    /**
     * Set created
     *
     * @param datetime $created
     * @return Request
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return datetime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Serie
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return "serie";    
    }
    
    /**
     * Set original_filename
     *
     * @param string $original_filename
     * @return Movie
     */
    public function setOriginalFilename($original_filename)
    {
        $this->original_filename = $original_filename;

        return $this;
    }

    /**
     * Get original_filename
     *
     * @return original_filename 
     */
    public function getOriginalFilename()
    {
        return $this->original_filename;
    }
    
    /**
     * Set transmission_id
     *
     * @param string $transmission_id
     * @return Movie
     */
    public function setTransmissionId($transmission_id)
    {
        $this->transmission_id = $transmission_id;

        return $this;
    }

    /**
     * Get transmission_id
     *
     * @return transmission_id 
     */
    public function getTransmissionId()
    {
        return $this->transmission_id;
    }
    
    public function __toString()
    {
        $str = $this->getName()." S%02d E%02d";
        return sprintf($str,$this->season,$this->episode);    
    }
}
