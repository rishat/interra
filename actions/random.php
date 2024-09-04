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
	* 				All Rights Reserved // www.inses.ru							*
	* 																			*
	* Read the full license in the license.rtf file distributed along with		*
	* this software package.													*
	*****************************************************************************/
	
	//initialize entry handler
	include("lib/entries.class.php");
	$myEntry = new inTerraEntry($db);
	
	//first check if this is a clear run or a limited by section run
	if(!empty($_GET['section'])){ //yes
	
	    //see if this section exists!
	    if(!$section = $db->getOne("SELECT catid FROM ".PREFIX."category WHERE name = '".$_GET['section']."'")){
    		include("actions/404.php");	//no, the section does not exist, call a 404
    	}else{ // it does!!!
    	
    	    //get a random post from this section
    		$randomPostID = $db->getOne("SELECT entryid FROM ".PREFIX."entry WHERE catid = '".$section."' ORDER BY rand() LIMIT 1");
    	}
    
    //now check if we have a keyword limiter
	}else if(!empty($_GET['keyword'])){
	    
	    //see if this keyword exists
	    if(!$wordid = $db->getOne("SELECT wordid AS id FROM ".PREFIX."keyword WHERE unixword = '".$_GET['keyword']."'")){
	        include("actions/404.php");	//no, the keyword does not exist, call a 404
	    }else{ // it does!
	        
	        //get a random post for this keyword
	        $randomPostID = $db->getOne("SELECT entryid FROM ".PREFIX."keywords WHERE wordid = '".$wordid."' ORDER BY rand() LIMIT 1");
	    }
	
	//no limits at all, get any post you like
	}else{ //no
	    
	    //get some random post =)
	    $randomPostID = $db->getOne("SELECT entryid FROM ".PREFIX."entry ORDER BY rand() LIMIT 1");   
	}
	
	
	//now get the url for redirection!
	if($randomPost = $myEntry->getEntry($randomPostID)){
	   header("Location: " . $randomPost['url']);
	   exit;
	}
	
	//no post? then it is a 404 =))
	include("actions/404.php");	
?>