<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
require_once ("AlgorithmsIO/Entity/JobAttribute.php");
/**
 *  
 * @ORM\Table(name="jobs")
 * @ORM\Entity
 */
class Jobs extends EntityBase
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
     * @ORM\ManyToOne(targetEntity="Users", inversedBy="created_jobs")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="DataSources", inversedBy="jobs")
     */
    protected $datasource;
    
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
     * @var string $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;
    
    /**
     * @var string $job_id
     *
     * @ORM\Column(name="job_id", type="string", nullable=true)
     */
    protected $job_id; // FIXME: This name is confusing, should use uuid instead for clarity
    
    public function set_uuid($uuid) {
        return $this->job_id = $uuid;
    }
    
    public function get_uuid() {
        return $this->job_id;
    }
    
    /**
     * @var integer $implementation_class
     *
     * @ORM\Column(name="implementation_class", type="integer", nullable=false)
     */
    protected $implementation_class;

    /**
     * @var string $status
     *
     * @ORM\Column(name="status", type="string", nullable=true)
     */
    protected $status;

    /**
     * @var string $additional_info
     *
     * @ORM\Column(name="additional_info", type="string", nullable=true)
     */
    protected $additional_info;
    
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

    /**
     * @ORM\OneToMany(targetEntity="JobAttribute", mappedBy="job", cascade={"ALL"}, indexBy="attribute")
     */
    protected $attributes;


}