<?
	/***
		Smarty Load Sections Resource built for InTerra Blog Machine

		@author Kulikov Alexe <alex@pvl.at, alex@essentialmind.com>

		no mandatory params =)
	***/
 	function smarty_function_get_links($params, &$smarty){
		global $db;

		//smarty variable name
		if(!empty($params['var'])){
			$variableName = (string)$params['var'];
		}else{
			$variableName = "links";
		}


		$links = array();
		$links['next'] = $db->getRow("SELECT entryid AS id, intime, urlcache AS url FROM ".PREFIX."entry WHERE entryid > ".(int)$_GET['id']." ORDER BY entryid ASC");
		$links['prev'] = $db->getRow("SELECT entryid AS id, intime, urlcache AS url FROM ".PREFIX."entry WHERE entryid < ".(int)$_GET['id']." ORDER BY entryid DESC");
		
		
		
		if(!ALLOW_NICEURLS){
		    if($links['next']){
		        $links['next'] = SERVER_ROOT . "entry/" . $links['next']['id'] . "/";
		    }
		    
		    if($links['prev']){
		        $links['prev'] = SERVER_ROOT . "entry/" . $links['prev']['id'] . "/";
		    }
        }else{
            if($links['next']){
                $links['next'] = SERVER_ROOT . date("Y/m/d/",$links['next']['intime']).$links['next']['url'].".html";
            }
            
            if($links['prev']){
                $links['prev'] = SERVER_ROOT . date("Y/m/d/",$links['prev']['intime']).$links['prev']['url'].".html";
            }
        }
        
        

		$smarty->assign($variableName,$links);
		$smarty->assign("TOTALDBQUERIES",$db->totq);
		return null;
 	}
?>