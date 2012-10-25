<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
require_once ("AlgorithmsIO/Entity/AlgorithmAttribute.php");
/**
 * Algorithms
 * 
 * @ORM\Table(name="algorithms")
 * @ORM\Entity
 */
class Algorithms extends EntityBase
{
    /**
     * @var integer $idSeq
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    //protected $idSeq;

    /**
     * @var string $type
     *
     * @ORM\Column(name="type", type="string", length=256, nullable=true)
     */
    protected $type;

    /**
     * @var string $class
     *
     * @ORM\Column(name="class", type="string", length=256, nullable=true)
     */
    protected $class;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=true)
     */
    protected $name;

    /**
     * @var string $implementationClass
     *
     * @ORM\Column(name="implementation_class", type="string", length=256, nullable=true)
     */
    protected $implementationClass;

    /**
     * @var string $implementationType
     *
     * @ORM\Column(name="implementation_type", type="string", length=256, nullable=true)
     */
    protected $implementationType;

    /**
     * @var bigint $creditsCost
     *
     * @ORM\Column(name="credits_cost", type="bigint", nullable=true)
     */
    protected $creditsCost;
    
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
     * @var text $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;
    
    /**
     * @ORM\ManyToOne(targetEntity="Users", inversedBy="created_algorithms")
     */
    protected $user;
    
    /**
     * @var datetime $datetimeCreated
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @var datetime $datetimeModified
     *
     * @ORM\Column(name="lastModified", type="datetime", nullable=true)
     */
    protected $lastModified;

    /**
     * OneToMany(targetEntity="AlgorithmAttribute", mappedBy="algorithms", cascade={"ALL"}, indexBy="attributeID")
     * @ORM\OneToMany(targetEntity="AlgorithmAttribute", mappedBy="algorithm", cascade={"ALL"}, indexBy="attribute")
     */
    protected $attributes;


}