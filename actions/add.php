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

	//check access
	if(!$_SESSION['admin']){
		header("Location: " . SERVER_ROOT);
		exit;
	}

	//disable caching
	$smarty->caching = false;
	$smarty->autoload_filters = array(); //disable filter


	function adds($text){
		if (!get_magic_quotes_gpc()) return addslashes($text);
		return $text;
	}



	//process POST
	if($_POST){
	    //bug fix for 1.42
	    if($_GET['id'] and $_SESSION['tempEntryID'] != (int)$_GET['id']){
	       $_SESSION['tempEntryID'] = (int)$_GET['id'];
	    }
	    
		//useful hack in order to make wackoprocesso user friendly =)
		define("ENTRY_ID",$_SESSION['tempEntryID']);

		//get some libraries
		require("lib/nwacko/classes/macroProcessor.class.php");
		$parser = &new macroProcessor();

		//create url transformation handler
		require("lib/translit/php/translit.php");
		$myTrans = new Translit();

		//as only the admin can post, we do not clean the input, just process it
		//and insert into the database

		//error check
		$_POST['subject'] = trim($_POST['subject']);
		if(empty($_POST['subject'])){
			$_POST['subject'] = $langData['noSubject'];
		}

		if(empty($_POST['content'])){
			$_POST['content'] = $langData['noContent'];
		}

		// =============== URL PREPARE ================================
		//first check if there was a "per hand" defined url
		if(ALLOW_HANDINPUT and !empty($_POST['url'])){
		    //prepare nifty looking url
            $urlCache = $myTrans->UrlTranslit($_POST['url']);
		}else{
            //prepare nifty looking url
            $urlCache = $myTrans->UrlTranslit($_POST['subject']);
		}

		//bogus check
		if(empty($urlCache)){
			$urlCache = "net_temy";
		}

		//figure out timestamp of post
		$date = mktime($_POST['time']['Time_Hour'],$_POST['time']['Time_Minute'],0,$_POST['date']['Date_Month'],$_POST['date']['Date_Day'],$_POST['date']['Date_Year']);

		//now check if such URL already exists for the current day
		$dayEndTimeStamp = mktime(23,59,59,$_POST['date']['Date_Month'],$_POST['date']['Date_Day'],$_POST['date']['Date_Year']);
		$dayStartTimeStamp = mktime(0,0,0,$_POST['date']['Date_Month'],$_POST['date']['Date_Day'],$_POST['date']['Date_Year']);

        //checking URL on server
		if($db->getOne("SELECT entryid FROM ".PREFIX."entry WHERE intime >= '$dayStartTimeStamp' AND intime <= '$dayEndTimeStamp' AND urlcache = '$urlCache' AND entryid != '".ENTRY_ID."'")){
			$urlCache = $urlCache . "_" . ENTRY_ID; //darn, just append the entryid to it, it will then be for sure unique
		}

		//create new redirection URL
		if(ALLOW_NICEURLS){
			$redirectionURL = SERVER_ROOT . date("Y/m/d/",$date) . $urlCache . ".html";
		}else{
			$redirectionURL = SERVER_ROOT . "entry/" . ENTRY_ID . "/";
		}

		//prepare constant to pass to wProcessor
		define("tREDURL",$redirectionURL);
		//================= END URL PREPARE ============================


        ## ENTRY ADD PREPROCESSORS ##
        #
        foreach(glob('actions/entry_pre/*.php') as $entry) {
            include_once($entry);
        }
        #
        ## //	
		

		//============= POST ======================================
		#drafts
		$db->query("DELETE FROM ".PREFIX."entry WHERE urlcache = 'AJAX DRAFT'");
		
		
		//database insert OR update
		if($_POST['edit']){
			$db->query("UPDATE ".PREFIX."entry SET
							subject 	  = '".adds($_POST['subject'])."',
							content 	  = '".adds($_POST['content'])."',
							content_p 	  = '".adds($parsedContent)."',
							catid		  = '".$catID."',
							intime		  = '".$date."',
							comments	  = '".(int)$_POST['comments']."',
							image		  = '".$imageTag."',
							keywordcache  = '".$keywords."',
							urlcache	  = '".$urlCache."',
							format        = '".(int)$_POST['replicate']."'
							WHERE entryid = '".ENTRY_ID."'");

			//get rid of cache
			$smarty->clear_cache("index.htm","entry|".ENTRY_ID);
		}else{
			$db->query("INSERT INTO ".PREFIX."entry SET
							entryid 	  = '".ENTRY_ID."',
							subject 	  = '".adds($_POST['subject'])."',
							content 	  = '".adds($_POST['content'])."',
							content_p 	  = '".adds($parsedContent)."',
							catid		  = '".$catID."',
							intime		  = '".$date."',
							image		  = '".$imageTag."',
							ljid		  = ".$ljID['itemid'].",
							ljurl		  = ".$ljURL.",
							keywordcache  = '".$keywords."',
							urlcache	  = '".$urlCache."',
							format        = '".(int)$_POST['replicate']."',
							comments	  = '".(int)$_POST['comments']."'");
		}
		//============= END POST ====================================
        
        ## ENTRY ADD PREPROCESSORS ##
        #
        foreach(glob('actions/entry_post/*.php') as $entry){
            include_once($entry);
        }
        #
        ## //
        

		//redirect
		header("Location: " . $redirectionURL);
		exit;
	}

	//fetch all existing keywords
	$keywords = array();
	$keywords = $db->getAssoc("SELECT word, word FROM ".PREFIX."keyword ORDER BY word");

	//are we editing an existing post?
	if($_GET['table'] == "update"){
		$edit = $db->getRow("SELECT * FROM ".PREFIX."entry WHERE entryid = '".(int)$_GET['id']."'");
		$entryKeys = $db->getAssoc("SELECT ".PREFIX."keyword.word,".PREFIX."keyword.word FROM ".PREFIX."keywords
														LEFT JOIN ".PREFIX."keyword ON ".PREFIX."keywords.wordid = ".PREFIX."keyword.wordid
														WHERE ".PREFIX."keywords.entryid = '".(int)$_GET['id']."'");

		//get rid of recurring keywords in dropdown
		$keywords = array_diff($keywords,$entryKeys);

		$edit['keywords'] = implode(", ",$entryKeys);
		$edit['subject'] = str_replace("\"","&#34;",$edit['subject']);

		//pass data to smarty
		$smarty->assign("edit",$edit);
	}else{
		//-- bug fix where adding a new entry did not create time offsets @ 21.11.04 --
		//$smarty->assign("edit",array("intime" => NOW));
		$smarty->assign("newPost",true);
		//-----------------------------------------------------------------------------
	}

	//give some default settings to smarty
	$smarty->assign(array(
							"addSections"	=>	$db->getAssoc("SELECT catid, fullName FROM ".PREFIX."category ORDER BY fullName"),
							"keywords"		=>	$keywords
							));

	//set breadcrumbs
	if($_POST['edit'] or $_GET['table'] == "update"){
		$breadCrumbs[] = $langData['edit'];
		$_SESSION['tempEntryID'] = (int)$_GET['id'];
	}else{
		$breadCrumbs[] = $langData['add'];
		$_SESSION['tempEntryID'] = (int)$db->getOne("SELECT max(entryid) FROM ".PREFIX."entry WHERE urlcache != 'AJAX DRAFT'") + 1;
	}

	//select smarty template
	$smarty->assign("template","add.htm");
	
	
	//for the template to shift the time baby yeah
	if(defined('OFFSET')){
	    $smarty->assign('OFFSET',(int)OFFSET);
	}
?>