<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EntityAttributeBase
 *
 * @author mark
 */
namespace AlgorithmsIO\Entity;
require_once('EntitySerializer.php');

class EntityAttributeBase extends EntityBase
{
    /**
     * Overide serializeEntity to just return array(attribute, value)
     * MRR20120717
     * @param type $entity 
     */
    protected function _serializeEntity($entity)
    {
        if(!isset($entity)) { $entity=$this; }
        $this->debug(sprintf("DEBUG201207171138: Attrib %s=%s",$entity->attribute, $entity->value));
        $value = $entity->value;
        //if(preg_match("/^[\[\{].*[\]\}]$/", $value)) {
        if(preg_match("/^[\[\{]/", $value)) {            
            // The value is in JSON format, we want to turn it back into a PHP structure so that it doesn't get double-quoted during serialization
            // TODO: Should validate that this won't be subject to Code Injection via data stored in SQL - MRR20120814
            return array($entity->attribute=>json_decode($value));
        }
        return array($entity->attribute=>$entity->value);
    }
}

?>
