<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
require_once ("AlgorithmsIO/Entity/DataSourceAttribute.php");
require_once ("AlgorithmsIO/Entity/Users.php");
require_once ("AlgorithmsIO/Entity/Jobs.php");

/**
 *  
 * @ORM\Table(name="datasources")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class DataSources extends EntityBase
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    //protected $idSeq;

    /**
     * @ORM\ManyToOne(targetEntity="Users", inversedBy="created_datasources")
     */
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="Jobs", mappedBy="datasource", cascade={"ALL"}, indexBy="id")
     */
    protected $jobs;
    
    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", nullable=true)
     */
    protected $name;

    /**
     *
     * @ORM\Column(name="type", type="string", length=512, nullable=true)
     */
    protected $type;

    /**
     *
     * @ORM\Column(name="location", type="string", length=512, nullable=true)
     */
    protected $location;

    /**
     *
     * @ORM\Column(name="filesystem_name", type="string", length=512, nullable=true)
     */
    protected $filesystem_name;    

    /**
     *
     * @ORM\Column(name="version", type="string", nullable=true)
     */
    protected $version;    
    
    /**
     *
     * @ORM\Column(name="ip_address", type="string", nullable=true)
     */
    protected $ip_address;    
    

    /**
     * @var string $size
     *
     * @ORM\Column(name="size", type="string", nullable=true)
     */
    protected $size;
    
    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;
    
    /**
     * @var string $defaultCost
     *
     * @ORM\Column(name="defaultCost", type="float", nullable=true)
     */
    protected $defaultcost;


    /**
     * @var string $defaultCostMethod
     *
     * @ORM\Column(name="defaultCostMethod", type="string", length=256, nullable=true)
     */
    protected $defaultcostmethod;
    
    /**
     * @var string $published
     *
     * @ORM\Column(name="published", type="string", length=256, nullable=true)
     */
    protected $published;
    
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
    protected $lastmodified;

    /**
     * @ORM\OneToMany(targetEntity="DataSourceAttribute", mappedBy="datasource", cascade={"ALL"}, indexBy="attribute")
     */
    protected $attributes;

    /** ORM\PrePersist  */
    public function doPrePersist() {
        parent::doPrePersist();
    }  
    
    /** ORM\PostPersist  */
    public function doPostPersist() {
        parent::doPostPersist();
    }        
}