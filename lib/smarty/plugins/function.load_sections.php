<?
	/***
		Smarty Load Sections Resource built for InTerra Blog Machine

		@author Kulikov Alexe <alex@pvl.at, alex@essentialmind.com>

		no mandatory params =)
	***/
 	function smarty_function_load_sections($params, &$smarty){
		global $db;

		//smarty variable name
		if(!empty($params['var'])){
			$variableName = (string)$params['var'];
		}else{
			$variableName = "sections";
		}

		//hidden only
		if(!empty($params['hidden'])){
			switch($params['hidden']){
				case 'all':		{
									$hidden = null;
									break;
								}

				case 'only':	{
									$hidden = "WHERE hidden = '1'";
									break;
								}

				default:		{
									$hidden = "WHERE hidden = '0'";
								}
			}
		}else{
			$hidden = "WHERE hidden = '0'";
		}

		//items sortorder
		if(!empty($params['sortby'])){
			switch($params['sortby']){
				case 'rand': 	{
									$sortBy = 'rand()';
									break;
								}

				case 'unix':	{
									$sortBy = 'name';
									break;
								}

				default: 		{
									$sortBy = 'fullName';
								}
			}
		}else{
			$sortBy = 'fullName';
		}

		//get the sections
		$sections = $db->getAll("SELECT catid AS id, name, fullName FROM ".PREFIX."category ".$hidden." ORDER BY ".$sortBy);

		//now, do we want post counts for them as well?
		if(!empty($params['count'])){
		    foreach($sections as $key => $section){
		        $sections[$key]['total'] = $db->getOne("SELECT count(*) FROM ".PREFIX."entry WHERE catid = ".$section['id']);
		    }
		}

		$smarty->assign($variableName,$sections);
		$smarty->assign("TOTALDBQUERIES",$db->totq);
		return null;
 	}
?>