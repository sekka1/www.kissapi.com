<?php

namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
/**
 *
 * @ORM\Table(name="flowattribs")
 * @ORM\Entity
 */
require_once ("AlgorithmsIO/Entity/EntityAttributeBase.php");
class FlowAttribute extends EntityAttributeBase {

    /**
     * @var integer $id
     *
     * ORM\Column(name="id", type="integer", nullable=false)
     * ORM\Id
     * ORM\GeneratedValue(strategy="IDENTITY")
     */
    //protected $id;    
    
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Flows", inversedBy="attributes")
     */ // Removed (at)Id
    protected $flow;
    
    /** 
     * @ORM\Id @ORM\Column(type="string") // Removed (at)Id
     */
    protected $attribute;
    
    /** @ORM\Column(type="text") */
    protected $value;
    
    public function __construct($attribName, $value, $entity)
    {
        $this->attribute = $attribName;
        $this->value = $value;
        $this->datasource = $entity;
    }    
}

?>
