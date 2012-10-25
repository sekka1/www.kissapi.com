<?php

namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
require_once ("AlgorithmsIO/Entity/EntityBase.php");
require_once ("AlgorithmsIO/Entity/Users.php");

/**
 * Description of AuthToken
 *
 * @author mark
 * @ORM\Table(name="authtokens")
 * @ORM\Entity
 */
class AuthTokens extends EntityBase {
    
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var string $firstName
     *
     * @ORM\Column(name="token", type="string", length=256, nullable=true)
     */
    protected $token;
    
    public function get_tokenid() {
        return $this->token;
    }
    
    /**
     * @var string $firstName
     *
     * @ORM\Column(name="description", type="string", length=256, nullable=true)
     */
    protected $description;

    /**
     * @ORM\OneToOne(targetEntity="Users")
     */
    protected $user;    
    
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

?>
