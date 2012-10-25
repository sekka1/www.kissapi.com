<?php


namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
require_once ("AlgorithmsIO/Entity/UserAttribute.php");
require_once ("AlgorithmsIO/Entity/DataSources.php");
require_once ("AlgorithmsIO/Entity/Roles.php");
require_once ("AlgorithmsIO/Entity/Dashboards.php");
require_once ("AlgorithmsIO/Entity/Flows.php");
require_once ("AlgorithmsIO/Entity/Algorithms.php");
require_once ("AlgorithmsIO/Entity/Jobs.php");
require_once ("AlgorithmsIO/Entity/Visualizations.php");

/**
 * Users
 * 
 * @ORM\Table(name="users")
 * @ORM\Entity
 */
class Users extends EntityBase
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
     * @ORM\ManyToMany(targetEntity="Roles", mappedBy="users")
     * @ORM\JoinTable(name="usertoroles_xref",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", unique=true)}
     *      )
     **/
    private $roles;  

    /**
     * @ORM\OneToMany(targetEntity="Dashboards", mappedBy="user", cascade={"ALL"}, indexBy="dashboard_id")
     */
    protected $dashboards;   
    
    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=256, nullable=true)
     */
    //TODO: Deprecate this in favor of firstName lastName
    protected $name;    

    public function get_name() {
        if($this->get_firstName()) {
            return $this->get_firstName()." ".$this->get_lastName();
        } else {
            return $this->name;
        }
    }
    /**
     * @var string $firstName
     *
     * @ORM\Column(name="firstName", type="string", length=256, nullable=true)
     */
    protected $firstName;

    /**
     * @var string $lastName
     *
     * @ORM\Column(name="lastName", type="string", length=256, nullable=true)
     */
    protected $lastName;
    
    /**
     * @var string $username
     *
     * @ORM\Column(name="username", type="string", length=256, nullable=true)
     */
    protected $username;
    
    /**
     * @var string $password
     *
     * @ORM\Column(name="password", type="string", length=256, nullable=true)
     */
    protected $password;

    public function set_password($password) {
        $this->password = md5($password);
    }
    
    public function validate_password($password) {
        if(md5($password) == $this->get_password()) {
            return true;
        }
        return false;
    }
    
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
     * TODO: This currently gets all the datasource created by the user, not the ones in a basket, or ones they have access to.
     * @ORM\OneToMany(targetEntity="DataSources", mappedBy="user", cascade={"ALL"}, indexBy="id")
     */
    protected $created_datasources;

    /**
     * TODO: This currently gets all the datasource created by the user, not the ones in a basket, or ones they have access to.
     * @ORM\OneToMany(targetEntity="Visualizations", mappedBy="user", cascade={"ALL"}, indexBy="id")
     */
    protected $created_visualizations;
    
    /**
     * TODO: This currently gets all the datasource created by the user, not the ones in a basket, or ones they have access to.
     * @ORM\OneToMany(targetEntity="Flows", mappedBy="user", cascade={"ALL"}, indexBy="id")
     */
    protected $created_flows;

    /**
     * TODO: This currently gets all the datasource created by the user, not the ones in a basket, or ones they have access to.
     * @ORM\OneToMany(targetEntity="Algorithms", mappedBy="user", cascade={"ALL"}, indexBy="id")
     */
    protected $created_algorithms;

    /**
     * @ORM\OneToMany(targetEntity="Jobs", mappedBy="user", cascade={"ALL"}, indexBy="id")
     */
    protected $created_jobs;
    
    /**
     * @ORM\OneToMany(targetEntity="UserAttribute", mappedBy="user", cascade={"ALL"}, indexBy="attribute")
     */
    protected $attributes;
}