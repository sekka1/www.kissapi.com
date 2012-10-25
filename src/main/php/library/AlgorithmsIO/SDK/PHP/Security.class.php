<?php
/*
 *	(c)2012 Algorithms.IO, Inc.
 *	Created By: MRR
 *	Created On: 2012-07-01
 *	For more information and usage terms see: https://www.algorithms.io/api/php (TODO)
 */
namespace AlgorithmsIO {

/******************************************** Security ***********************************************/
	class Security extends Base {

            private $_permCache;
            
		public function __construct($options = array()) {
			$this->defaults = array(
				"authObj"		=>null,
				"fakeAllPerms"		=>true, // Must be False for Production
				"debug"			=>false, 
			);
			$this->_oData = array_replace_recursive($this->defaults, $options);

			if(!$this->_oData["authObj"]) {
				$this->error("ERROR 201207031337: Security needs an Authentication Object");
			}

			if($this->_oData["fakeAllPerms"]) {
				$this->fakeAllPerms();
			}
		}	

		public function canRead($permission) {
			return $this->permCheck($permission, Array("R"));
		}
		public function canWrite($permission) {
			return $this->permCheck($permission, Array("W"));
		}
		public function canEdit($permission) {
			return $this->permCheck($permission, Array("W")); // Same as canWrite
		}
		public function canCreate($permission) {
			return $this->permCheck($permission, Array("W")); // Same as canWrite
		}
		public function canDelete($permission) {
			return $this->permCheck($permission, Array("W")); // Same as canWrite
		}
		public function canExecute($permission) {
			return $this->permCheck($permission, Array("X"));
		}
		public function canBuy($permission) {
			return $this->permCheck($permission, Array("B"));
		}
		public function canSell($permission) {
			return $this->permCheck($permission, Array("S"));
		}
		public function permRights($permission) {
			/* //Get the permissionID from the Permissions table
			 * SELECT RoleToPermission_xref.Rights FROM RoleToPermission_xref,Permissions WHERE RoleToPermission_xref.RoleID=[Authentication->RoleID()] AND Permissions.Name=[$permission]
			 * // NOTE: We may want to do a left join to see if the permission even exists to avoid typos
			 * $result["Rights"] should be a comma delimited list of permssions (R,W,X,B,S)	
			 * $rightsArray = explode(",", $result["Rights"]);
			 * // Next we add it to the permissions cache, so we don't have to query it again if we are asked
			 * return $this->_permCache = array($permission=>$rightsArray)
			 */
                         $this->warning("WARNING201209301222: $permission is not in the cache or in the DB so we are denying access to it");
                         $this->_permCache[$permission] = array();
		}
		public function permCheck($permission, $requestedRights) {
			if($this->_oData["fakeAllPerms"]) {
				$this->warning("WARNING 201207031432: Warning: permCheck is using fake permissions/rights");
			} else {
				// TODO: Remove this once the DB calls are working - MRR20120703
				$this->warning("WARNING 201207031244:**** Warning: permCheck is returning true for everything".$this->_oData["fakeAllPerms"]);
				return true;
			}

			if(!isset($this->_permCache[$permission])) {
                                // We don't have it in the cache, so we attempt to grab it
                                // Doesn't work yet
				$this->permRights($permission);
			}
			$rights = $this->_permCache[$permission];
                        if(!count($rights)) {
                            // We have no rights, so return false
                            return false;
                        }
			$result = array_diff($requestedRights, $rights);
			if($result) {
				return false; // User does not have at least one of the requested Rights
			} else {
				return true; // The user has all of the requested rights
			}
		}

		public function hasFeature($feature) {
			// TODO: Check if the customer has a feature. The customer is retrieved from the user's role
			$this->error("ERROR201207031128: hasFeature() has not been implemented");
		}

		public function loadAllPerms() {
			// TODO: If we are doing a lot of permission checks, this function can pre-load the _permCache with all of the user's permissions
			$this->error("ERROR201207031231: loadAllPerms() has not been implemented");
		}

		public function fakeAllPerms() {
			$this->_permCache = Array(
				"Dashboards" 			=> Array("R", "X"),//Array("R", "W", "X"),
				"DataSources" 			=> Array("R", "W", "X"),
				//"DataSources" 			=> Array("", "W", "X"),
				//"Algorithms" 			=> Array("R", "W", "X"),
				//"Visualizations" 		=> Array("R", "W", "X"),
				//"Flows" 			=> Array("R", "W", "X"),
				//"Jobs"	 			=> Array("R", "W", "X"),
			);
		}
	}
}

?>
