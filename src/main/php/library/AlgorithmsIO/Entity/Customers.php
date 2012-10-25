<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
//require_once ("AlgorithmsIO/Entity/DataSourceAttribute.php");
/**
 *  
 * @ORM\Table(name="customers")
 * @ORM\Entity
 */
class Customers extends EntityBase
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
     * @ORM\OneToMany(targetEntity="Roles", mappedBy="customer", cascade={"ALL"}, indexBy="role_id")
     */
    protected $roles;
    
    /**
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=true)
     */
    protected $name;
    
    /**
     *
     * @ORM\Column(name="longName", type="string", length=256, nullable=true)
     */
    protected $longName;

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
     * @ORM\OneToMany(targetEntity="CustomerAttribute", mappedBy="customer", cascade={"ALL"}, indexBy="attribute")
     */
    protected $attributes;


}