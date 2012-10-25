<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
//require_once ("AlgorithmsIO/Entity/RoleAttribute.php");
/**
 *  
 * @ORM\Table(name="rolerights")
 * @ORM\Entity
 */
class RoleRights extends EntityBase
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

   /** @ORM\ManyToOne(targetEntity="Roles", inversedBy="rights")
     */
    protected $role;

   /** @ORM\ManyToOne(targetEntity="Permissions", inversedBy="rights")
     */
    protected $permission;    
    
    /**
     *
     * @ORM\Column(name="rights", type="string", length=256, nullable=true)
     */
    protected $rights;
    
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