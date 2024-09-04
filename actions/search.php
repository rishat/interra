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
	
	//fix breadcrumbs
	$breadCrumbs[] = $langData['search']; //year
	
	//disable cache
	$smarty->caching = false;
	
	//assign smarty vars
	$smarty->assign("sections",	$db->getAssoc("SELECT catid, fullName FROM ".PREFIX."category WHERE hidden = '0' ORDER BY fullName"));
	
	//process post =)
	if($_POST){
		//prepare search phrase
		$word = trim(htmlspecialchars(strip_tags($_POST['word'])));
		$word = str_replace('%','\%',$word);
		$word = str_replace('_','\_',$word);
		
		if(strlen($word)>2){
			//include Sniffer Lib
			include("lib/sniffer.class.php");
			
			//prepare sql query
			$mySniff = new sniffer($db);
			$mySniff->search($word,"".PREFIX."entry","subject, content");
			$mySniff->select("entryid AS id, subject, content_p AS content, intime, comments, commentcount, urlcache AS url");
			$mySniff->limitStart(0);
			$mySniff->limitEnd(25);
			$mySniff->order("intime DESC");
			
			if((int)$_POST['section'] > 0){
				$mySniff->constraint("catid = '".(int)$_POST['section']."'");
			}else{
			    $mySniff->constraint("urlcache != 'AJAX DRAFT'");
			}
			
			//get search result
			$data = $mySniff->find();
			
			//preformat urls	
			if(is_array($data)){
				foreach($data as $key => $entry){
					if(ALLOW_NICEURLS){
						$data[$key]['url'] = SERVER_ROOT . date("Y/m/d/",$entry['intime']).$entry['url'].".html";
					}else{
						$data[$key]['url'] = SERVER_ROOT . "entry/" . $entry['id'] . "/";
					}
				}
			}
			
			$smarty->assign(array(	"searchResults"	=>	$data,
									"searchTotal"	=>	$mySniff->getTotal()));
		}else{
			$smarty->assign(array(	"searchResults"	=>	null,
									"searchTotal"	=>	0));	
		}
	}
	
	//select smarty template
	$smarty->assign("template","search.htm");	
?>