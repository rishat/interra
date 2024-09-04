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
	
	//check if there is an LJ entry to delete as well
	if(ENABLE_LJ){
		if($ljID = $db->getOne("SELECT ljid FROM ".PREFIX."entry WHERE entryid = '$entryID'")){	//we have a hit! =)
			
			//get the libraries
			require('lib/xmlrpc/xmlrpc.inc');
			require('lib/lj.class.php');
			
			//create interface and drop post
			$myLJ = new lj(LJ_USER,LJ_PASS);
			$myLJ->dropPost($ljID);
		}
	}
	
	$db->query("DELETE FROM ".PREFIX."comment WHERE entryid = '$entryID'");
	$db->query("DELETE FROM ".PREFIX."keywords WHERE entryid = '$entryID'");
	$db->query("DELETE FROM ".PREFIX."entry WHERE entryid = '$entryID'");
	
	//get rid of cache
	$smarty->clear_cache(null,"______rootpage");
	$smarty->clear_cache(null,"entry|$entryID");
	
	header("Location: ".SERVER_ROOT);
	exit;
?>