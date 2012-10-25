<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");

/**
 *  
 * @ORM\Table(name="permissions")
 * @ORM\Entity
 */
class permissions extends EntityBase
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /** @ORM\ManyToOne(targetEntity="Features", inversedBy="permissions")
     */
    protected $feature;

    /**
     * @ORM\OneToMany(targetEntity="RoleRights", mappedBy="permission", cascade={"ALL"}, indexBy="role_id")
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