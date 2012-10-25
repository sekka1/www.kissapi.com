<?php
//NOTE: http://docs.doctrine-project.org/en/latest/tutorials/composite-primary-keys.html#use-case-1-dynamic-attributes

namespace AlgorithmsIO\Entity;
use Doctrine\ORM\Mapping AS ORM;
/**
 * AlgorithmAttribs
 *
 * @ORM\Table(name="algorithmsattribs")
 * @ORM\Entity
 */
require_once ("AlgorithmsIO/Entity/EntityAttributeBase.php");
class AlgorithmAttribute extends EntityAttributeBase {

    /**
     * @var integer $id
     *
     * ORM\Column(name="id", type="integer", nullable=false)
     * ORM\Id
     * ORM\GeneratedValue(strategy="IDENTITY")
     */
    //protected $id;    
    
    /**
     * ORM\Column(name="algorithm_id", type="integer")
     * ORM\Id
     */
    //protected $algorithm_id;
    
    /** @ORM\Id @ORM\ManyToOne(targetEntity="Algorithms", inversedBy="attributes")
     */ // Removed (at)Id
    protected $algorithm;
    
    /** 
     * @ORM\Id @ORM\Column(type="string") // Removed (at)Id
     */
    protected $attribute;
    
    /** @ORM\Column(type="text") */
    protected $value;
    
    public function __construct($attribName, $value, $algorithm)
    {
        $this->attribute = $attribName;
        $this->value = $value;
        $this->algorithm = $algorithm;
        //$this->algorithm_id = $algorithm->get_id_seq();
        //echo "algorithm_id=".$this->algorithm_id;
    }    

}

?>
