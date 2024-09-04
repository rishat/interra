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

    //actions?
    if($_GET['do'] == 'delete' and $_SESSION['admin']){        
        $db->query("DELETE FROM ".PREFIX."pages WHERE url = '".$_GET['section']."'");
        header("Location: ".SERVER_ROOT);
        exit;
    }
        
    $breadCrumbs[] = $page['title'];
    
	//get data and pass to smarty
    $smarty->assign("data",$page);

    //select smarty template
    $smarty->assign("template","page.htm");
?>