<?php
// This Class combines the 3 steps in a recomenation call into 1 step.
//
// The 3 steps is pass in an item/user and get the mapping, then use that
// mapping to feed the recommendation call, then reverse the mapping from the
// recommendation back into the real user/item

class GetRecommendation
{
    private $generic;

    public function __construct(){

        // Model for generic database table
        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic = new Generic();
    }
    public function simItemLogLikelihoodNoPref( $request_vars ){
    // Runs the 2 steps to prep the file

        require_once('AlgorithmsIO/classes/Mapping.php'); 
        $mapping = new Mapping();

        require_once('AlgorithmsIO/classes/Algorithms.php');
        $algorithms = new Algorithms();

        $output = array();

        // Get the user/item mapping for the mahout file
        $data['searchField'] = 'original_name';
        $request_vars->setParams( $data );
        $mapping_result = json_decode( $mapping->getValueMapping( $request_vars ), true );

       if( count( $mapping_result ) > 0 ){

            // Set the params needed to get the recommendation
            // Use the first result returned
            $data['field_mapped_value'] = $mapping_result[0]['field_mapped_value'];
            $request_vars->setParams( $data );
    
            // Get the recommendations
            $output['recommendation'] = array();
            $rec_results = json_decode( $algorithms->simItemLogLikelihoodNoPref( $request_vars ), true );
            
            // Remap the id's returned by the recommendation back into the real user/item name
            foreach( $rec_results as $aResult ){

                // Set params
                // Setting both the user and item b/c it doesnt matter there is already a flag in there
                // for which one the user wants. 'type'
                $data['searchField'] = 'field_mapped_value';
                $data['user'] = $aResult['id'];
                $data['item'] = $aResult['id'];
                $request_vars->setParams( $data );

                $data['searchField'] = 'id';
                $request_vars->setParams( $data );
                $remap_result = json_decode( $mapping->getValueMapping( $request_vars ), true );

                if( count( $remap_result ) > 0 ){
                // Found the reverse mapping.  Add it into the return rec array

                    $rec['id'] = $remap_result[0]['field_original_name'];
                    $rec['value'] = $aResult['value'];
                    array_push( $output['recommendation'], $rec );
                }
            }
        }else{
            $output['error'] = 'Did not find this item';
        } 

        return json_encode( $output );
    }
}
