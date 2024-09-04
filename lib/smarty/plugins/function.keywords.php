<?
	/**
	 * smarty template function
	 * name: node keywords resource
	 * package: InTerra Blog Machine CMS
	 * author: Kulikov Alexey
	 * 
	 * params: 
	 *	 mandatory:		'entry' entry id (int)					default: current entry
	 *   optional:		'max' max items to fetch (int)			default: 10
	 * 					'sortby' sortoder (rand, alph)			default: alph
	 * 					'var' smarty variable name (string) 	default: keywords
	 * 
	 * @version 0.99
	 * @copyright 2004, InSES Corporation and Alexey Kulikov
	 **/
	 
 	function smarty_function_keywords($params, &$smarty){
		global $db;
		
		//check for optional variables
		if(!empty($params['entry'])) {
			$entry = (int)$params['entry'];
    	}else{
	        $smarty->_trigger_fatal_error("please define an entry to get a keyword list for");
	        return;
		}
		
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
			$itemsToGet = 10;
		}
		
		//items sortorder
		if(!empty($params['sortby'])){
			switch($params['sortby']){
				case 'rand': 	{$sortBy = 'rand()'; break;}
				default: 		{$sortBy = 'word';}
			}
		}else{
			$sortBy = 'word';
		}
		
		//give data to smarty and terminate
		$smarty->assign("TOTALDBQUERIES",$db->totq);
		$smarty->assign($variableName,$db->getCol("SELECT word FROM ".PREFIX."keywords LEFT JOIN ".PREFIX."keyword ON ".PREFIX."keywords.wordid = ".PREFIX."keyword.wordid WHERE ".PREFIX."keywords.entryid = " . $entry . " ORDER BY ".$sortBy." LIMIT ".$itemsToGet));
		return;
	}
	
	/*
	* Short note: I love smarty! =) 
	******/
?>