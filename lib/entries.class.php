<?
	/****************************************************************************
	* Software distributed under the License is distributed on an "AS IS" 		*
	* basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the 	*
	* License for a specific language governing rights and limitations under	*
	* the License. 																*
	* 																			*
	*	The Original Code is - InTerra Blog Machine   				            *
	* 																			*
	*	The Initial Developer of the Original Code is 							*
	* 																			*
	* 			Kulikov Alexey <alex [at] essentialmind [dot] com>	 			*
	* 																			*
	* 							All Rights Reserved. 							*
	* 																			*
	*****************************************************************************/

	class inTerraEntry{
		var $db;				// @access private
		var $limitStart;		// @access private
		var $timeStart;			// @access private
		var $timeEnd;			// @access private
		var $section;			// @access private
		var $order = "DESC";	// @access private
		var $entryID;			// @access private
		var $root = false;      // @access private
		
		
		/***
		 El Constructor
		 @param $db (object)
		***/
		function inTerraEntry(&$db){
			$this->db = &$db;
		}
		
		
		/***
		 inTerraEntry::createSQL() -- creates the SQL query that will be passed to the DB handler
		 
		 @access private
		 @return string
		***/
		function createSQL($limit=true){
			//initial sql string
			$sql = "SELECT 
						entryid AS id,
						subject, 
						content_p AS content, 
						intime, 
						comments,
						commentcount,
						image,
						ljurl,
						urlcache AS url,
						keywordcache AS keywords,
						".PREFIX."entry.catid,
						".PREFIX."category.name AS catName,
						".PREFIX."category.fullName AS catFullName
					FROM ".PREFIX."entry LEFT JOIN ".PREFIX."category ON (".PREFIX."entry.catid = ".PREFIX."category.catid)";
			
			//check if there is a sectional dellimiter
			$delim = array();

			//check if we need a separate entry
			if(isset($this->entryID)){
				$sql .= " WHERE entryid = '".$this->getEntryID()."'";
			}else{
				//check if there is a section delimiter
				if(isset($this->section)){
					$delim[] = PREFIX."entry.catid = '".$this->getSection()."'";	
				}
				
				//check if there is a timeframe
				if(!empty($this->timeStart) and !empty($this->timeEnd)){
					$delim[] = PREFIX."entry.intime > '".$this->getTimeStart()."' AND ".PREFIX."entry.intime < '".$this->getTimeEnd()."'";
				}
				
				//check if this is the root page
				if($this->getRoot()){
				    $delim[] = PREFIX."entry.format = '1'";
				}
				
				//create dellimmiting query
				if(!empty($delim)){
					$sql .= " WHERE " . implode(" AND ",$delim);	
				}
				
				//set sort order
				$sql .= " ORDER BY ".PREFIX."entry.intime ".$this->getOrder();

				//limit?
				if($limit){
					$sql .= " LIMIT ".(int)$_GET['skip'].", " . PER_PAGE;
				}
			}
				
			return $sql;
		}
		
		
		/***
		 inTerraEntry::getEntries() -- returns an array of entries
		 
		 @return array
		***/
		function getEntries($limit=true){
			$entries = $this->db->getAll($this->getSQL($limit));
			
			//check if keywords are allowed and process them into an array
			foreach($entries as $key => $entry){
				
				//keywords
				if(!empty($entry['keywords'])){
					$myWords = explode(",",$entry['keywords']);

					foreach($myWords as $mkey => $keyword){
						$temp = explode("|",$keyword);

						//upgrade bogus check
						if(empty($temp[1])){
							$temp[1] = urlencode($temp[0]);
						}

						$myWords[$mkey] = array("word"=>$temp[0], "link"=>$temp[1]);
					}

					$entries[$key]['keywords'] = $myWords;
				}else{
					$entries[$key]['keywords'] = array();
				}

				//categorial settings
				if(!empty($entry['catid'])){
					$entries[$key]['section'] = array();
					$entries[$key]['section']['id'] = $entry['catid'];
					$entries[$key]['section']['name'] = $entry['catName'];
					$entries[$key]['section']['fullName'] = $entry['catFullName'];
				}else{
					$entries[$key]['section'] = array();
				}
				
				//check url cache (reverse compatibility issue V1.1
				if(!ALLOW_NICEURLS){
					$entries[$key]['url'] = SERVER_ROOT . "entry/" . $entry['id'] . "/";
				}else{
					$entries[$key]['url'] = SERVER_ROOT . date("Y/m/d/",$entry['intime']).$entry['url'].".html";
				}
				
				//unset double data
				unset($entries[$key]['catid']);
				unset($entries[$key]['catName']);
				unset($entries[$key]['catFullName']);
			}
			
			return $entries;
		}
		
		
		/***
		 inTerraEntry::getEntry() -- returns one entry object with all associate data
		 
		 @param entryID int
		 @return array
		***/
		function getEntry($entryID){
			$this->setEntryID($entryID);
			$entries = $this->getEntries();
			return $entries[0];
		}
		
		
		/***
		 inTerraEntry::getSQL() -- alias for createSQL()
		 
		 @return string
		***/
		function getSQL(){
			return $this->createSQL();
		}
		
		/***
		 inTerraEntry::setOrder() -- set SQL sort order, default is DESC
		 
		 @param order string
		***/
		function setOrder($order="DESC"){
			$this->order = $order;
		}
		
		/***
		 inTerraEntry::getOrder() -- returns the preset sort order
		 
		 @return string
		***/
		function getOrder(){
			return $this->order;	
		}
		
		/***
		 inTerraEntry::setSection() -- sets the section of entries for SQL dellim
		 
		 @param section int
		***/
		function setSection($section){
			$this->section = (int)$section;	
		}
		
		/***
		 inTerraEntry::getSection() -- return currently selected section
		 
		 @return int
		***/
		function getSection(){
			return $this->section;	
		}
		
		/***
		 inTerraEntry::setTimeStart()
		 
		 @param timestamp int
		***/
		function setTimeStart($timestamp){
			$this->timeStart = $timestamp;
		}
		
		/***
		 inTerraEntry::getTimeStart()
		 
		 @return int
		***/
		function getTimeStart(){
			return $this->timeStart;	
		}
		
		/***
		 inTerraEntry::setTimeEnd()
		 
		 @param timestamp int
		***/
		function setTimeEnd($timestamp){
			$this->timeEnd = $timestamp;
		}
		
		/***
		 inTerraEntry::getTimeEnd()
		 
		 @return int
		***/
		function getTimeEnd(){
			return $this->timeEnd;	
		}
		
		/***
		 inTerraEntry::setTimes()
		 
		 @param start int
		 @param end int
		***/
		function setTimes($start,$end){
			$this->setTimeStart($start);
			$this->setTimeEnd($end);	
		}
		
		/***
		 inTerraEntry::setEntryID() -- sets the desired entry id
		 
		 @param id int
		***/
		function setEntryID($id){
			$this->entryID = (int)$id;
		}
		
		/***
		 inTerraEntry::getEntryID() -- return the preset entry id
		 
		 @return int
		***/
		function getEntryID(){
			return $this->entryID;	
		}
		
		/***
		 inTerraEntry::setRoot() -- sets the notion of this being the root page
		 
		 @return int
		***/
		function setRoot($value=true){
		    $this->root = (bool)$value;   
		}
		
		/***
		 inTerraEntry::getRoot() -- return the notion of this being the root page
		 
		 @return int
		***/
		function getRoot(){
		    return $this->root;   
		}
	}
?>