<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
require_once ("AlgorithmsIO/Entity/VisualizationAttribute.php");
/**
 *  
 * @ORM\Table(name="visualizations")
 * @ORM\Entity
 */
class Visualizations extends EntityBase
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
     * @ORM\ManyToOne(targetEntity="Users", inversedBy="created_visualizations")
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
     * @ORM\Column(name="default_cost", type="float", nullable=true)
     */
    protected $default_cost;

    /**
     * @var string $defaultCostMethod
     *
     * @ORM\Column(name="default_cost_method", type="string", length=256, nullable=true)
     */
    protected $default_cost_method;

    /**
     * @var string $published
     *
     * @ORM\Column(name="published", type="string", length=256, nullable=true)
     */
    protected $published;
    
    /**
     * @var string $visualizationData
     *
     * @ORM\Column(name="visualization_data", type="text", nullable=true)
     */
    protected $visualization_data;    
    
     /**
     * @var datetime $created
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    protected $created;

    /**
     * @var datetime $lastModified
     *
     * @ORM\Column(name="last_modified", type="datetime", nullable=true)
     */
    protected $last_modified;

    /**
     * @ORM\OneToMany(targetEntity="VisualizationAttribute", mappedBy="visualization", cascade={"ALL"}, indexBy="attribute")
     */
    protected $attributes;


}