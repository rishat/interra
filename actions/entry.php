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

	//get data and pass to smarty
	include("lib/entries.class.php");
	$myEntries = new inTerraEntry($db);
	$entry = $myEntries->getEntry($_GET['id']);

	//is this a 404?
	if(!$entry){
		include("actions/404.php");
	}else{
		$smarty->assign("data",$entry);

		//if comments are allowed for this post, then fetch the comment tree
		if($entry['comments'] == '1'){
			$smarty->assign("comments",$db->getAll("SELECT * FROM ".PREFIX."comment WHERE entryid = '" . (int)$_GET['id'] . "' ORDER BY sortorder"));
		}

		//figure out all the neccessary breadcrumbs
		$breadCrumbs[] = date("Y",$entry['intime']); 	//year
		$breadCrumbs[] = date("m",$entry['intime']); 	//month
		$breadCrumbs[] = date("d",$entry['intime']); 	//day
		$breadCrumbs[] = $entry['subject']; 			//actual subject

		//select smarty template
		$smarty->assign("template","entry.htm");
	}
?>