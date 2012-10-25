<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
require_once ("AlgorithmsIO/Entity/FlowAttribute.php");
/**
 *  
 * @ORM\Table(name="flows")
 * @ORM\Entity
 */
class Flows extends EntityBase
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
     * @ORM\ManyToOne(targetEntity="Users", inversedBy="created_flows")
     */
    protected $user;

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
     * @ORM\Column(name="version", type="string", nullable=true)
     */
    protected $version;    
    
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
    protected $defaultCost;


    /**
     * @var string $defaultCostMethod
     *
     * @ORM\Column(name="defaultCostMethod", type="string", length=256, nullable=true)
     */
    protected $defaultCostMethod;
    
    /**
     * @var string $published
     *
     * @ORM\Column(name="published", type="string", length=256, nullable=true)
     */
    protected $published;
    
    /**
     * @var string $flowData
     *
     * @ORM\Column(name="flowData", type="text", nullable=true)
     */
    protected $flowData;    
    
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
     * @ORM\OneToMany(targetEntity="FlowAttribute", mappedBy="flow", cascade={"ALL"}, indexBy="attribute")
     */
    protected $attributes;


}