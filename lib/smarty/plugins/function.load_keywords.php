<?
	/**
	 * smarty template function
	 * name: node keywords resource
	 * package: InTerra Blog Machine CMS
	 * author: Kulikov Alexey
	 * 
	 * params: 
	 *   optional:		'max' max items to fetch (int)			default: 15
	 * 					'sortby' sortoder (rand, alph, rating)	default: alph
	 * 					'var' smarty variable name (string) 	default: keywords
	 * 
	 * @version 0.99
	 **/
	 
 	function smarty_function_load_keywords($params, &$smarty){
		global $db;
				
		//smarty variable name
		if(!empty($params['var'])){
			$variableName = (string)$params['var'];
		}else{
			$variableName = "keywords";
		}
		
		//items to fetch
		if(!empty($params['max'])){
			$itemsToGet = (int)$params['max'];
		}else{
			$itemsToGet = 15;
		}
		
		//items sortorder
		if(!empty($params['sortby'])){
			switch($params['sortby']){
				case 'rand': 	{$sortBy = 'rand()'; break;}
				case 'rating':  {$sortBy = 'total'; break;}
				default: 		{$sortBy = 'word';}
			}
		}else{
			$sortBy = 'word';
		}
		
		//give data to smarty and terminate
		$smarty->assign($variableName,$db->getAll("SELECT 
		                                                ".PREFIX."keyword.word, 
		                                                ".PREFIX."keyword.unixword AS link, 
		                                                count(".PREFIX."keywords.entryid) AS total 
													FROM ".PREFIX."keywords 
													LEFT JOIN ".PREFIX."keyword ON (".PREFIX."keywords.wordid = ".PREFIX."keyword.wordid)													
													GROUP BY ".PREFIX."keywords.wordid ORDER BY ".$sortBy." DESC, word LIMIT ".$itemsToGet));
													
		$smarty->assign("TOTALDBQUERIES",$db->totq);		
		return;
	}
	
	/*
	* Short note: I love smarty! =) 
	******/
?>