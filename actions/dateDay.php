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
	
	//figure out all the neccessary breadcrumbs
	$breadCrumbs[] = date("Y",mktime(0,0,0,$month,$day,$year)); //year
	$breadCrumbs[] = date("m",mktime(0,0,0,$month,$day,$year)); //month
	$breadCrumbs[] = date("d",mktime(0,0,0,$month,$day,$year)); //day
	
	$smarty->assign(array(	"year"	=>	$year,
							"month"	=>	$month,
							"day"	=>	$day,
							"title"	=>	$year . " / " . $month . " / " . $day,
							"ts"	=>	mktime(0,0,0,$month,$day,$year)));

	
	//get this day's entries from the database	
	include("lib/entries.class.php");
	$myEntries = new inTerraEntry($db);
	$myEntries->setTimes(mktime(0,0,0,$month,$day,$year),mktime(23,59,59,$month,$day,$year));
	$myEntries->setOrder("ASC");
		
	$smarty->assign("data",$myEntries->getEntries(false));
								
	//select smarty template
	$smarty->assign("template","dayView.htm");
?>