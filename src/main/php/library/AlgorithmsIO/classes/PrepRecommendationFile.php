<?php
// This class combines the 2 steps in prepping a recommendation file for Mahout
// 1) Map the Field
// 2 ) Create Arbitrary Mapping

class PrepRecommendationFile
{
    private $generic;

    public function __construct(){

        // Model for generic database table
        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic = new Generic();
    }
    public function doPrep( $request_vars ){
    // Runs the 2 steps to prep the file

        require_once('AlgorithmsIO/classes/Mapping.php');
        $mapping = new Mapping();

        require_once('AlgorithmsIO/classes/ArbitraryMaping.php');
        $arbitraryMapping = new ArbitraryMaping();

        // Set Field Mapping
        $field_map_id = $mapping->userFields( $request_vars );

        // Create Arbitrary Mapping
        $arbitrary_mapping_output = $arbitraryMapping->createXToIdMapping( $request_vars ); 

        $output['field_map_id'] = json_decode( $field_map_id, true );
        $output['mapping'] = json_decode( $arbitrary_mapping_output, true );

        return json_encode( $output );
    }
}
