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
        //get some libraries
		require("lib/nwacko/classes/macroProcessor.class.php");
		$parser = &new macroProcessor();
		
		//error check
		$_POST['subject'] = trim($_POST['subject']);
		if(empty($_POST['subject'])){
			$_POST['subject'] = $langData['noSubject'];
		}

		if(empty($_POST['content'])){
			$_POST['content'] = $langData['noContent'];
		}
		
		//figure out timestamp of post
		$date = mktime($_POST['time']['Time_Hour'],$_POST['time']['Time_Minute'],0,$_POST['date']['Date_Month'],$_POST['date']['Date_Day'],$_POST['date']['Date_Year']);

        //wiki process content (only if not in WYSIWYG mode)
		if(!WYSIWYG){
		  $parsedContent = $parser->process($_POST["content"]);
		}else{
		  $parsedContent = $_POST["content"];
		}
				
		if($_POST['edit']){
            $db->query("UPDATE ".PREFIX."pages SET
                                title 	      = '".adds($_POST['subject'])."',
                                content 	  = '".adds($_POST['content'])."',
                                content_p 	  = '".adds($parsedContent)."',
                                show_in_menu  = '".adds($_POST['show_in_menu'])."'
                            WHERE page_id = ".(int)$_POST['edit']);

		}else{
            $db->query("INSERT INTO ".PREFIX."pages SET
                                title 	      = '".adds($_POST['subject'])."',
                                content 	  = '".adds($_POST['content'])."',
                                content_p 	  = '".adds($parsedContent)."',
                                intime		  = '".$date."',
                                url	          = '".$_GET['section']."'");
		}
		
	    header("Location: " . SERVER_ROOT . $_GET['section'].'/');
		exit;
	}
	
	if($page){
	   $breadCrumbs[] = $langData['edit'];
	   $smarty->assign("edit",$page);
	}else{
	   $breadCrumbs[] = $langData['add'];
	}

	//select smarty template
	$smarty->assign("template","add.htm");	
	$smarty->assign("add","page");
?>