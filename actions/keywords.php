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
	
	//set breadcrumbs
	$breadCrumbs[] = $langData['keywords'];
	
	//get some data to smarty
	/*
	$smarty->assign("keywords",$db->getAll("SELECT ".PREFIX."keyword.word, ".PREFIX."keyword.unixword AS link, count(".PREFIX."keywords.entryid) AS total 
													FROM ".PREFIX."keywords 
													LEFT JOIN ".PREFIX."keyword ON (".PREFIX."keywords.wordid = ".PREFIX."keyword.wordid)
													
													GROUP BY ".PREFIX."keywords.wordid ORDER BY word"));
	*/
	
	$smarty->assign("keywords2",$db->getAll("SELECT ".PREFIX."keyword.word, ".PREFIX."keyword.unixword AS link, count(".PREFIX."keywords.entryid) AS total 
													FROM ".PREFIX."keywords 
													LEFT JOIN ".PREFIX."keyword ON (".PREFIX."keywords.wordid = ".PREFIX."keyword.wordid)
													
													GROUP BY ".PREFIX."keywords.wordid ORDER BY total DESC, word"));
	
	//select smarty template
	$smarty->assign("template","keywords.htm");
?>