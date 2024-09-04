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
	
	//bogus
    if(eregi('(.*)/$',$_GET['section'])){
        $_GET['section'] = substr($_GET['section'],0,-1);
    }


	//figure out section settings (if any)
	if(!$section = $db->getRow("SELECT * FROM ".PREFIX."category WHERE name = '".$_GET['section']."'")){
	
	    //is there a page maybe? (V 1.70)
	    if($page = $db->getRow("SELECT * FROM ".PREFIX."pages WHERE url = '".$_GET['section']."'")){
	        if(($_GET['do'] == 'edit' or $_POST) and $_SESSION['admin']){
	            include("actions/add_page.php");
	        }else{
	            include("actions/page.php");
	        }
	    }else{
	        if($_SESSION['admin']){
	           include("actions/add_page.php");
	        }else{
	           include("actions/404.php");
	        }
	    }
	    
	}else{
		//breadcrumbs
		$breadCrumbs[] = $section['fullName']; //year
		
		include("lib/entries.class.php");
		$myEntries = new inTerraEntry($db);
		$myEntries->setSection($section['catid']);
		
		//get some data fro smarty
		$smarty->assign(array(
								"data"		=> $myEntries->getEntries(),
								"section"	=> $section,
								"title"		=> $section['fullName']
							));
		
		//if pagers are enabled need to get post count
		if(ENABLE_PAGERS){
			$totalEntries = $db->getOne("SELECT count(*) FROM ".PREFIX."entry WHERE catid = '".$section['catid']."'");
			
			//check for a possible 404
			if($_GET['skip'] >= $totalEntries){
				include("actions/404.php");
			}else{
				$smarty->assign("pagerTotal",$totalEntries);
				
				//select smarty template
				$smarty->assign("template","section.htm");	
			}
			
		}else{
			//select smarty template
			$smarty->assign("template","section.htm");	
		}
	}
?>