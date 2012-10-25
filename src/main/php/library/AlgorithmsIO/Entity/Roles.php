<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
require_once ("AlgorithmsIO/Entity/RoleAttribute.php");
require_once ("AlgorithmsIO/Entity/Customers.php");
require_once ("AlgorithmsIO/Entity/RoleRights.php");
/**
 *  
 * @ORM\Table(name="roles")
 * @ORM\Entity
 */
class Roles extends EntityBase
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /** @ORM\ManyToOne(targetEntity="Customers", inversedBy="roles")
     */
    protected $customer;

    /**
     * @ORM\ManyToMany(targetEntity="Users", inversedBy="roles")
     * @ORM\JoinTable(name="usertoroles_xref",
     *      joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    protected $users;

    /**
     * @ORM\OneToMany(targetEntity="RoleRights", mappedBy="role", cascade={"ALL"})
     */
    protected $rights;
       
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
     *
     * @ORM\Column(name="status", type="string", length=256, nullable=true)
     */
    protected $status;
    
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
     * @ORM\OneToMany(targetEntity="RoleAttribute", mappedBy="role", cascade={"ALL"}, indexBy="attribute")
     */
    protected $attributes;


}