<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
//require_once ("AlgorithmsIO/Entity/DataSourceAttribute.php");
/**
 *  
 * @ORM\Table(name="credits")
 * @ORM\Entity
 */
class Credits extends EntityBase
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
     * @var string $credits
     *
     * @ORM\Column(name="credits", type="bigint", nullable=true)
     */
    protected $credits;
    
    /**
     * @ORM\Column(name="API_Calls_id", type="bigint", nullable=true)
     */
    protected $API_Calls;

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
     * @ORM\OneToMany(targetEntity="DataSourceAttribute", mappedBy="datasource", cascade={"ALL"}, indexBy="attribute")
     */
    protected $attributes;


}