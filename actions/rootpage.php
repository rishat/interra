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
	* Read the full license in the license.rtf file distributed along with		*
	* this software package.													*
	*****************************************************************************/

	/***
	 Root Page
	***/

	//get the last 20 entries from the database and assign them to the template engine
	include("lib/entries.class.php");
	$myEntries = new inTerraEntry($db);
	$myEntries->setRoot(true);
	$smarty->assign("data",$myEntries->getEntries());

	//if pagers are enabled need to get post count
	if(ENABLE_PAGERS){
		$totalEntries = $db->getOne("SELECT count(*) FROM ".PREFIX."entry");

		//check for a possible 404
		if($_GET['skip'] >= $totalEntries){
			include("actions/404.php");
		}else{
			$smarty->assign("pagerTotal",$totalEntries);

			//select smarty template
			$smarty->assign("template","main.htm");
		}

	}else{
		//select smarty template
		$smarty->assign("template","main.htm");
	}
?>