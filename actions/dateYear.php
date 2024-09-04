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
	
	//assign some data
	$smarty->assign(array(	"thisYear"	=>	$year,
							"thisMonth"	=>	$month,
							"years"		=>	$db->getCol("SELECT DISTINCT FROM_UNIXTIME(intime,'%Y') as myYear FROM ".PREFIX."entry ORDER BY myYear"),
							"months"	=>	$db->getCol("SELECT DISTINCT FROM_UNIXTIME(intime,'%c') as myMonth FROM ".PREFIX."entry WHERE intime > '".mktime(0,0,0,1,1,$year)."' AND intime < '".mktime(23,59,59,12,31,$year)."' ORDER BY intime DESC"),
							"title"		=>	$year));
							
	//select smarty template
	$smarty->assign("template","yearView.htm");
?>