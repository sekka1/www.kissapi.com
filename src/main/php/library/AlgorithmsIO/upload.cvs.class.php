<?php
/*
 * Created by MRR on 2012-05-29
 * Extends the jQuery upload handler class
 */

require_once("upload.class.php");
require_once("Entity/DataSources.php");
//include_once("datasource.class.php");

class CVS_UploadHandler extends UploadHandler
{
        public function setZendController($zendController) {
            $this->_zendController = $zendController;
        }
        
	public function upload_finished($file) {

		$this->process_csv($file);

		error_log("DEBUG201205291151: Finished file ".$file->name);	
	}

	public function process_csv($file) {

            require_once("Entity/DataSources.php");
            $datasource = new \AlgorithmsIO\Entity\DataSources();
            $entityManager = $this->_zendController->entityManager();
            $entityManager->persist($datasource);
            $entityManager->flush();
            $datasource->set_status("pending");
            $datasource->set_filesystem_name($datasource->get_id());
            $datasource->set_originalFilename($file->name);
            $datasource->set_name($file->name);
            $datasource->set_privacy("private");
            $datasource->set_size($file->size);
            $datasource->set_user($this->_zendController->user());
            $datasource->set_type("csv");
            $doctrine = \Zend_Registry::get("doctrine");
            $entityManager = $doctrine->getEntityManager();           
            $entityManager->persist($datasource);
            $entityManager->flush();

            $tfilename = "datasource_".$datasource->get_id().".csv"; //MRR20120803 - Hardcoded csv extension for now. That the way to go?
            rename("/tmp/".$file->name, "/tmp/".$tfilename);
            Zend_Loader::loadClass( 'S3Usage' );
            $this->s3 = new S3Usage();
            $basename = $this->s3->upload( "/tmp/".$tfilename );

            $datasource->set_filesystem_name($tfilename);
            $datasource->set_status("ready"); // Mark the dataset as ready since upload to S3 has completed.
            $entityManager->persist($datasource);
            $entityManager->flush();
            
            $maxlines = 50000;
            list($params, $linesprocessed) = $this->processDataSourceParams("/tmp/".$tfilename, $maxlines);
            $datasource->set_outputParams(json_encode($params));
            $datasource->set_columns(count($params));
            if($linesprocessed < $maxlines) {
                // We processed all of the rows available
                $datasource->set_rows($linesprocessed);
                // If rows is not set, then we know min/max field values may be unreliable
            } 
            $datasource->set_rowsProcessed($linesprocessed);
            
            $entityManager->persist($datasource);
            $entityManager->flush();            
            return;		
	}

        public function processDataSourceParams($source_file, $maxlines=50000) {
            $linecount = 0;
            date_default_timezone_set('UTC');
            $columnDefs=array();

            if (($handle = fopen("$source_file", "r")) !== FALSE) {
                $columns = fgetcsv($handle);

                foreach ($columns as &$column) {
                    $columnDefs[$column]["datatype"] = "";
                    //$column = str_replace(".","",$column); 
                }

                // Process the first bunch of lines to see if we can guess the best type for each field
                while ((($data = fgetcsv($handle)) !== FALSE) && (($maxlines==0) or ($linecount < $maxlines))) {
                    for ($i=0; $i < count($data); $i++) {
                        $field = $data[$i];

                        $type = "unknown";
                        if($field == "") {
                                $type = "";
                        } else if(preg_match("/[^0-9\.e\-]/i",$field)) {
                                // Not a Number or Date
                                $type = "string";
                        } else if(preg_match("/^[0-9]*$/",$field)) {
                                $type = "integer";
                        } else if(preg_match("/^-[0-9\.]*$/", $field)) {
                                $type = "float";
                        } else if(preg_match("/^-?\d*[\.eE]\d*$/i",$field)) {
                                $type = "float";
                        } else if(strtotime($field)) {
                                $type = "date";
                        }

                        $coldef = $columnDefs[$columns[$i]]; // Just to make it easier on the eyes
                        $coltype = $coldef["datatype"]; // Just to make it easier on the eyes

                        if((!isset($coldef["minlength"])) OR (strlen($field) < $coldef["minlength"])) {
                                $columnDefs[$columns[$i]]["minlength"] = strlen($field);
                        }
                        if((!isset($coldef["maxlength"])) OR (strlen($field) > $coldef["maxlength"])) {
                                $columnDefs[$columns[$i]]["maxlength"] = strlen($field);
                        }
                        if(($coltype!="string") AND ($type != "") AND ($type != $coltype)) {
                                // If a field has ever previously been set to string, we leave it
                                // If the current field is "" we ignore it
                                if(($type=="integer") && ($coltype=="float")) {
                                        // We've already discovered it was float, so ignore that the current field is integer
                                        $type = "float";
                                }
                                //printf("%s has a type %s (%s)\n", $columns[$i], $type, $field);
                                $columnDefs[$columns[$i]]["datatype"] = $type;
                                $columnDefs[$columns[$i]]["description"] = sprintf("Data Source field type automatically set as $type.",$type);
                                $columnDefs[$columns[$i]]["sample"] = $field;
                        } else {
                                //echo (".");
                        }

                    }
                    $linecount++;
                }
                fclose($handle);
                //print_r($columnDefs);
            }
                return array($columnDefs, $linecount);
        }

}

?>
