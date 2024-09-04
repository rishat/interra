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
	
	$entryID = (int)$_GET['id'];
	$comSOrd = (int)$_GET['comid'];
	
	//$db->query("DELETE FROM ".PREFIX."comment WHERE entryid = '$entryID' AND sortorder = '$comSOrd'");
	//$db->query("UPDATE ".PREFIX."comment SET sortorder = sortorder - 1 WHERE entryid = '$entryID' AND sortorder > '$comSOrd'");
	$db->query("UPDATE ".PREFIX."comment SET deleted = '1' WHERE entryid = '$entryID' AND sortorder = '$comSOrd'");
	$db->query("UPDATE ".PREFIX."entry SET commentcount = commentcount - 1 WHERE entryid = '$entryID'");
	
	//kill cache if any
	if(SMARTY_ALLOW_CACHE){
		$smarty->clear_cache(null,"entry|$entryID");
		$smarty->clear_cache(null,"______rootpage");
		$smarty->clear_cache(null,"keyword");
		$smarty->clear_cache(null,YEAR."|".MONTH."|".DAY);
	}
	
	header("Location: ".REDURL);
	exit;
?>