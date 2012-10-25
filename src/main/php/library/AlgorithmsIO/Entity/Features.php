<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");

/**
 *  
 * @ORM\Table(name="features")
 * @ORM\Entity
 */
class Features extends EntityBase
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Permissions", mappedBy="feature", cascade={"ALL"})
     * Need to add an indexBy?
     */
    protected $permissions;
    
    /**
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=true)
     */
    protected $name;
    
    /**
     *
     * @ORM\Column(name="description", type="string", length=256, nullable=true)
     */
    protected $description;
    
    /**
     * @var datetime $created
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="lastModified", type="datetime", nullable=true)
     */
    protected $lastModified;

}