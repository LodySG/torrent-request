<?php

namespace TorrentRequestBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="movie")
 * @ORM\Entity(repositoryClass="TorrentRequestBundle\Entity\MovieRepository")
 */
class Movie
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
     * @param \DateTime $created
     *
     * @return Movie
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
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
     * @return Movie
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
    public function getType()
    {
        return "movie";   
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
        return $this->getName();    
    }
}
