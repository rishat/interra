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
	
	$db->query("UPDATE ".PREFIX."entry SET image = '0' WHERE entryid = '$entryID'");
	
	//get rid of cache
	$smarty->clear_cache(null,"entry|$entryID");
	$smarty->clear_cache(null,"______rootpage");
	
	//get rid of files
	@unlink("files/".$entryID."/image.jpg");
	@unlink("files/".$entryID."/thumb.jpg");
	
	header("Location: ".SERVER_ROOT."entry/".$entryID."/edit/");
	exit;
?>