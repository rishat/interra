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
	*****************************************************************************/

	//disable caching
	$smarty->caching = false;


	if(empty($_SESSION['challenge'])){
	   $_SESSION['challenge'] = "dull";
	}

    require_once ROOT_DIR . "lib/antispam/jpgraph_antispam.php";
    $spam  = new AntiSpam();
    //$chars = $spam->Rand(5);
    $spam->Set($_SESSION['challenge']);
    if(!$spam->Stroke()){
        echo "shit";
    }
    exit;
?>