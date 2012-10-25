<?php
/**
 * Bgy Library
 *
 * LICENSE
 *
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://sam.zoy.org/wtfpl/COPYING for more details.
 *
 * @category    Bgy
 * @package     Bgy\Doctrine
 * @author      Boris Guéry <guery.b@gmail.com>
 * @license     http://sam.zoy.org/wtfpl/COPYING
 * @link        http://borisguery.github.com/bgylibrary
 * @see         https://gist.github.com/1034079#file_serializable_entity.php
 */

namespace AlgorithmsIO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata,
    Doctrine\Common\Util\Inflector,
    Doctrine\ORM\EntityManager,
    Exception;

class EntitySerializer
{

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $_em;

    public function __construct($em)
    {
        $this->setEntityManager($em);
    }

    /**
     *
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        return $this->_em;
    }

    public function setEntityManager(EntityManager $em)
    {
        $this->_em = $em;

        return $this;
    }

    protected function _serializeEntity($entity)
    {
        if(!isset($entity)) { $entity=$this; } // MRR2010717
        
        $className = get_class($entity);
        $metadata = $this->_em->getClassMetadata($className);

        $data = array();

        foreach ($metadata->fieldMappings as $field => $mapping) {
            $value = $metadata->reflFields[$field]->getValue($entity);
            $field = Inflector::tableize($field);
            $this->debug("DEBUG201208031532: field=".$field);
            if ($value instanceof \DateTime) {
                // MRR20120717 - Changed to display in ISO format (instead of array)
                $data[$field] = (string)$value->format('Y-m-d H:i:s');
                // We cast DateTime to array to keep consistency with array result
                //$data[$field] = (array)$value;
            } elseif (is_object($value)) {
                $data[$field] = (string)$value;
            } else {
                $data[$field] = $value;
            }
        }

        foreach ($metadata->associationMappings as $field => $mapping) {
            $this->debug("DEBUG201207180902: working on $field");
            $key = Inflector::tableize($field);
            if ($mapping['isCascadeDetach']) {
                $data[$key] = $metadata->reflFields[$field]->getValue($entity);
                if (null !== $data[$key]) {
                    // BEGIN MRR20120717 - If $data[key] is a class of PersistentCollection, we need to call _serializeEnetity on everything within the collection
                    if(get_class($data[$key]) == "Doctrine\ORM\PersistentCollection") {
                        $this->debug("DEBUG201206171108: Serializing a PersistentCollection");
                        foreach ($data[$key]->toArray() as $subEntity) {
                            $this->debug("DEBUG201206171109: Calling serialize on ".get_class($subEntity));
                            if($key == "attributes") {
                                // attributes are a special case where we want to merge the keys and values back into the parent mix
                                $tarray = $subEntity->_serializeEntity($subEntity);
                                $tkey = key($tarray);
                                $tvalue = $tarray[$tkey];
                                $this->debug("DEBUG201206171154: %s=%s",$tkey,$tvalue);
                                $data[$tkey] = $tvalue;
                            } else {
                                // We provide the same structure back (like an array)
                                $data[$key] = $subEntity->_serializeEntity($subEntity);
                            }
                        }
                        if($key == "attributes") { unset($data[$key]); } // We remove the attributes since we merged them in above
                    } else {
                        $data[$key] = $this->_serializeEntity($data[$key]);
                    }
                    // END MRR20120717
                }
            } elseif ($mapping['isOwningSide'] && $mapping['type'] & ClassMetadata::TO_ONE) {
                if (null !== $metadata->reflFields[$field]->getValue($entity)) {
                    $data[$key] = $this->getEntityManager()
                        ->getUnitOfWork()
                        ->getEntityIdentifier(
                            $metadata->reflFields[$field]
                                ->getValue($entity)
                            );
                } else {
                    // In some case the relationship may not exist, but we want
                    // to know about it
                    $data[$key] = null;
                }
            }
        }

        return $data;
    }

    /**
     * Serialize an entity to an array
     *
     * @param The entity $entity
     * @return array
     */
    public function toArray($entity)
    {
        return $this->_serializeEntity($entity);
    }


    /**
     * Convert an entity to a JSON object
     *
     * @param The entity $entity
     * @return string
     */
    public function toJson($entity)
    {
        return json_encode($this->toArray($entity));
    }

    /**
     * Convert an entity to XML representation
     *
     * @param The entity $entity
     * @throws Exception
     */
    public function toXml($entity)
    {
        throw new Exception('Not yet implemented');
    }
}
