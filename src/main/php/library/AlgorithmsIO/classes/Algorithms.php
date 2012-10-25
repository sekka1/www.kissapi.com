<?php
// Runs the various Algorithms

class Algorithms
{
    private $auth;
    private $generic;
    private $debug;
    private $recURL;

    public function __construct(){

        $this->debug = false;

        // Init Auth Class
        require_once('AlgorithmsIO/classes/Auth.php');
        $this->auth = new Auth();

        // Model for generic database table
        require_once('AlgorithmsIO/model/Generic.php');
        $this->generic = new Generic();

        // Grab the application.ini params
        $config = new Zend_Config_Ini(
                APPLICATION_PATH . '/configs/application.ini',
                APPLICATION_ENV
                );

        $this->recURL = $config->app->params->url->reccommendationURL;
    }
    public function setDebugTrue(){

        $this->debug = true;
    }
    public function simItemLogLikelihood( $request_vars ){
        // Runs the item similarity recommendation 
        // INPUT: datasource_id_seq, itemId

        $authToken = $request_vars->getParam( 'authToken' );                                                                  
        $this->auth->setAuthToken( $authToken );                                                                             
        $servlet = 'RunnerMahoutTaste?action=SimItemLogLikelihood&';
                                                                                                                             
        $returnVal = '';                                                                                                     
                                                                                                                             
        if( $this->auth->isValid() ){                                                                                        
                                                                                                                             
            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
            $itemId = $request_vars->getParam( 'field_mapped_value' );

            $options = 'file=rec_'.$datasource_id_seq.'&itemId='.$itemId.'&numRec=15';

            $results = file_get_contents( $this->recURL.$servlet.$options );

            $returnVal = $this->fixJSON( $results );
        }
        return $returnVal;
    }
    public function simItemLogLikelihoodNoPref( $request_vars ){
        // Runs the item similarity recommendation 
        // INPUT: datasource_id_seq, itemId

        $authToken = $request_vars->getParam( 'authToken' );                                                                  
        $this->auth->setAuthToken( $authToken );                                                                             
        $servlet = 'RunnerMahoutTaste?action=SimItemLogLikelihoodNoPref&';
                                                                                                                             
        $returnVal = '';                                                                                                     
                                                                                                                             
        if( $this->auth->isValid() ){                                                                                        
                                                                                                                             
            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
            $itemId = $request_vars->getParam( 'field_mapped_value' );

            $options = 'file=rec_'.$datasource_id_seq.'&itemId='.$itemId.'&numRec=15';

            $results = file_get_contents( $this->recURL.$servlet.$options );

            $returnVal = $this->fixJSON( $results );
        }
        return $returnVal;
    }
    public function simItemLogLikelihoodNoPrefEvaluator( $request_vars ){
        // Runs the item evaluator
        // INPUT: datasource_id_seq, itemId

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );
        $servlet = 'RunnerMahoutTaste?action=SimItemLogLikelihoodNoPrefEvaluator&';

        $returnVal = '';

        if( $this->auth->isValid() ){

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
            $itemId = $request_vars->getParam( 'field_mapped_value' );

            $options = 'file=rec_'.$datasource_id_seq.'&itemId='.$itemId.'&numRec=15';

            $results = file_get_contents( $this->recURL.$servlet.$options );

            $temp['value'] = $results;
            $returnVal = json_encode( $temp );
        }
        return $returnVal;
    }
    public function simUserLogLikelihood( $request_vars ){
        // Runs the user similarity recommendation 
        // INPUT: datasource_id_seq, itemId

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );
        $servlet = 'RunnerMahoutTaste?action=SimUserLogLikelihood&';

        $returnVal = '';

        if( $this->auth->isValid() ){

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
            $itemId = $request_vars->getParam( 'field_mapped_value' );
            $neighborhoodSize = $request_vars->getParam( 'neighborhoodSize' );

            $options = 'file=rec_'.$datasource_id_seq.'&userId='.$itemId.'&numRec=15&neighborhoodSize='.$neighborhoodSize;

            $results = file_get_contents( $this->recURL.$servlet.$options );

            $returnVal = $this->fixJSON( $results );
        }
        return $returnVal;
    }
    public function simUserLogLikelihoodNoPref( $request_vars ){
        // Runs the user similarity recommendation 
        // INPUT: datasource_id_seq, itemId

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );
        $servlet = 'RunnerMahoutTaste?action=SimUserLogLikelihoodNoPref&';
        
        $returnVal = '';
        
        if( $this->auth->isValid() ){
    
            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );                                                        
            $itemId = $request_vars->getParam( 'field_mapped_value' );
            $neighborhoodSize = $request_vars->getParam( 'neighborhoodSize' );

            $options = 'file=rec_'.$datasource_id_seq.'&userId='.$itemId.'&numRec=15&neighborhoodSize='.$neighborhoodSize;

            $results = file_get_contents( $this->recURL.$servlet.$options );

            $returnVal = $this->fixJSON( $results );
        }
        return $returnVal;
    }
    public function simUserLogLikelihoodNoPrefEvaluator( $request_vars ){
        // Runs the user similarity recommendation 
        // INPUT: datasource_id_seq, itemId

        $authToken = $request_vars->getParam( 'authToken' );
        $this->auth->setAuthToken( $authToken );
        $servlet = 'RunnerMahoutTaste?action=SimUserLogLikelihoodNoPrefEvaluator&';

        $returnVal = '';

        if( $this->auth->isValid() ){

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );
            $itemId = $request_vars->getParam( 'field_mapped_value' );
            $neighborhoodSize = $request_vars->getParam( 'neighborhoodSize' );

            $options = 'file=rec_'.$datasource_id_seq.'&userId='.$itemId.'&numRec=15&neighborhoodSize='.$neighborhoodSize;

            $results = file_get_contents( $this->recURL.$servlet.$options );

            $temp['value'] = $results;
            $returnVal = json_encode( $temp );
        }
        return $returnVal;
    }
    public function simUserPearsonCorrelation( $request_vars ){
        // Runs the user similarity recommendation                                                                     
        // INPUT: datasource_id_seq, itemId                                                                            

        $authToken = $request_vars->getParam( 'authToken' );                                                           
        $this->auth->setAuthToken( $authToken );                                                                       
        $servlet = 'RunnerMahoutTaste?action=SimUserPearsonCorrelation&';

        $returnVal = '';                                                                                               

        if( $this->auth->isValid() ){                                                                                  

            $datasource_id_seq = $request_vars->getParam( 'datasource_id_seq' );                                       
            $itemId = $request_vars->getParam( 'field_mapped_value' );                                                 
            $neighborhoodSize = $request_vars->getParam( 'neighborhoodSize' );                                         

            $options = 'file=rec_'.$datasource_id_seq.'&userId='.$itemId.'&numRec=15&neighborhoodSize='.$neighborhoodSize;

            $results = file_get_contents( $this->recURL.$servlet.$options );                                            

            $temp['value'] = $results;                                                                                 
            $returnVal = json_encode( $temp );                                                                         
        }
        return $returnVal;                                                                                             
    }
    private function fixJSON( $string ){
        // Temp to remove the trailing comman and add brackets around the whole thing

        //$string = preg_replace( '/\,$/', '', $string );
        //$string = substr( $string, 0, -3 );

        //$string = '['.$string.']';

        // Recommendation system might return a broekn json if there are no results.  Fixing this here.
        $string = preg_replace( '/^]/', '[]', $string );

        return $string;
    }
}
