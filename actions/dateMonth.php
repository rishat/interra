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
	
	//figure out all the neccessary breadcrumbs
	$breadCrumbs[] = date("Y",mktime(0,0,0,$month,$day,$year)); //year
	$breadCrumbs[] = date("m",mktime(0,0,0,$month,$day,$year)); //month
	
	//assign some data
	$smarty->assign(array(	"year"	=>	$year,
							"month"	=>	$month,
							"title"	=>	$year . " / " . $month));
	
	//get entries and pre-process URLs
	$data = $db->getAll("SELECT 
											subject, intime, comments, commentcount, entryid AS id, urlcache AS url 
										FROM ".PREFIX."entry WHERE intime > '".mktime(0,0,0,$month,1,$year)."' AND intime < '".mktime(23,59,59,$month,date("t",mktime(0,0,0,$month,1,$year)),$year)."' ORDER BY intime ASC");
	
	foreach($data as $key => $entry){
		if(ALLOW_NICEURLS){
			$data[$key]['url'] = SERVER_ROOT . date("Y/m/d/",$entry['intime']).$entry['url'].".html";
		}else{
			$data[$key]['url'] = SERVER_ROOT . "entry/" . $entry['id'] . "/";
		}
	}
	
	//pass data to smarty
	$smarty->assign("data",$data);
	
	//select smarty template
	$smarty->assign("template","monthView.htm");
?>