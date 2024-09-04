<?
	/***
		Smarty Load Pages Resource built for InTerra Blog Machine

		@author Kulikov Alexey <alex@pvl.at, alex@essentialmind.com>

		no mandatory params =)
	***/
 	function smarty_function_load_pages($params, &$smarty){
		global $db;

        $where = array();
        
		//smarty variable name
		if(!empty($params['var'])){
			$variableName = (string)$params['var'];
		}else{
			$variableName = "pages";
		}
		
		
		if(!empty($params['all'])){
		    //...
		}else{
		    $where[] = " show_in_menu = 't' ";
		}


		//items sortorder
		if(!empty($params['sortby'])){
			switch($params['sortby']){
				case 'rand': 	{
									$sortBy = 'rand()';
									break;
								}

				case 'unix':	{
									$sortBy = 'url';
									break;
								}
								
			    case 'intime':  {
			                        $sortBy = 'intime';
			                        break;
			                    }

				default: 		{
									$sortBy = 'title';
								}
			}
		}else{
			$sortBy = 'title';
		}

		$smarty->assign($variableName,$db->getAll("SELECT page_id, url, title, intime FROM ".PREFIX."pages ".($where?' WHERE '.implode(' AND ',$where):'')." ORDER BY ".$sortBy));
		$smarty->assign("TOTALDBQUERIES",$db->totq);
		return null;
 	}
?>