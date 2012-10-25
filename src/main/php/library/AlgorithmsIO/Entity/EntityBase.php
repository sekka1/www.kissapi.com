<?php
/** MappedSuperclass */
/** @HasLifecycleCallbacks */
/*
// * Note: See http://docs.doctrine-project.org/en/latest/reference/association-mapping.html#one-to-many-unidirectional-with-join-table
 * for an example of how to use a cross reference table. - MRR 20120715
 */

use Doctrine\ORM\Mapping AS ORM;
/**
 * ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */

namespace AlgorithmsIO\Entity;
require_once('EntitySerializer.php');

class EntityBase extends EntitySerializer implements \JsonSerializable
{
    private $debug = false; //true;

    /** @ORM\PrePersist  */
    public function doPrePersist() {
        $this->debug("DEBUG201207271810: Running doPrePersist()");
        $this->set_created(new \DateTime("now"));
        $this->set_lastModified(new \DateTime("now"));
    }  
    
    /** @ORM\PostPersist  */
    public function doPostPersist() {
        $this->debug("DEBUG201207271810: Running doPostPersist()");        
        $this->lastModified = new \DateTime("now");
    }    
    
    // I prefer using methods instead of setting $this vars. This way we can override. MRR20120701
    // Use $obj->get_<column>() to get a given column record
    // Use $obj->set_<column>() to set a given column record
    public function __call($name, $arguments) {
            if (isset($arguments[0])) {
                $value = $arguments[0];
            }

            // Seperate the method from the fieldname
            if (strpos($name, "_") === false) {
                list($method,$fieldname) = array("","");
            } else {
                preg_match('/([^_]*)_(.*)/',$name, $matches);
                $method = $matches[1];
                $fieldname = $matches[2];
            }

            $this->debug("DEBUG201207171957: NAME=%s fieldname=%s",$name,$fieldname);
            if($method == 'get' && property_exists($this, $fieldname)) {
                return $this->$fieldname;
            } else if($method == 'get' && is_callable(array($this, "getAttribute"))) {
                // Get it from the attributes table
                return $this->getAttribute($fieldname);
            } else if($method == 'set' && property_exists($this, $fieldname)) {                
                if(!isset($value)) {
                    $this->error("ERROR201208161738: set was called without a value");
                } else {
                    $this->$fieldname = $value;
                }
                return $this->$fieldname;
            } else if ($method == 'set' && is_callable(array($this, "setAttribute"), true)) {
                $this->attributes[$fieldname] = $this->setAttribute($fieldname, $value);
                return $this->attributes[$fieldname];
            }

            $this->warning("WARNING201206152016: $name ($fieldname) could not find the field -- ignoring");
    }

    public function setAttribute($name, $value) {
        if(gettype($value)=="array" || gettype($value)=="object") {
            $value = json_encode($value); // We store objects as json encoding
        }        
        if (isset($this->attributes[$name])) {
            // Attribute already exists, need to update it.
            $this->attributes[$name]->set_value($value);
        } else {
            $myClass = substr(get_class($this),0,-1);
            $myAttribClass = $myClass."Attribute";
            //$this->debug("DEBUG201206161920: Creating class = $myAttribClass");
            $this->attributes[$name] = new $myAttribClass($name,$value,$this);
        }
        return $this->attributes[$name];
        //echo "algorithm_id $name =".$this->attributes[$name]->get_algorithm_id();
    }
    
    public function getAttribute($name) {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name]->value;
        }
        return null;
    }
    
    public function getAttributes() {
        return $this->attributes;
    }
    
    public function setFromArray($data) {
        foreach ($data as $key=>$value) {
            $myfunc = "set_".$key;
            $this->$myfunc($value);
        }
    }
    
    public function __construct() {
        $this->attributes = new \Doctrine\Common\Collections\ArrayCollection();
    }             

    public function jsonSerialize() {
        $doctrine = \Zend_Registry::get("doctrine");
        $em = $doctrine->getEntityManager();
        $this->setEntityManager($em);
        return (object) $this->_serializeEntity($this);
    }
    
    public function toArray($entity=null) {
        $doctrine = \Zend_Registry::get("doctrine");
        $em = $doctrine->getEntityManager();
        $this->setEntityManager($em);
        $array = (array) $this->_serializeEntity($this);
        return $array;
    }
    
    public function _debug_to_string($argarray = array()) {
            //$argarray = func_get_args();
            if(count($argarray)>0) {
                return vsprintf(array_shift($argarray), $argarray);
            } else {
                error_log("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!".count($argarray));
                return $argarray[0];
            }  
    }    
    public function debug($msg) {
            if($this->debug) {
                    error_log($this->_debug_to_string(func_get_args()));
            }
    }
    public function warning($msg) {
        error_log($this->_debug_to_string(func_get_args()));
    }
    public function error($msg) {
            $msg .= " *** Backtrace: ".print_r(debug_backtrace(null,5),true);
            error_log($msg);
            if($this->debug) {
                    echo $msg;
            }
    }
}

?>
