<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
require_once ("AlgorithmsIO/Entity/DashboardAttribute.php");
/**
 *  
 * @ORM\Table(name="dashboards")
 * @ORM\Entity
 */
class Dashboards extends EntityBase
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
     * @ORM\ManyToOne(targetEntity="Users", inversedBy="dashboards")
     */
    protected $user;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=true)
     */
    protected $name;
    
    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="items", type="object", nullable=true) 
     */
    
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
     * @ORM\OneToMany(targetEntity="DashboardAttribute", mappedBy="dashboard", cascade={"ALL"}, indexBy="attribute")
     */
    protected $attributes;


}