<?
	/**
	 * smarty template function
	 * name: comment fetch resource
	 * package: InTerra Blog Machine
	 * author: Kulikov Alexey <alex [at] inses [dot] ru
	 * 
	 * params:
	 *   optional:		'items' items to fetch (int)				default: 5
	 * 					'var' smarty variable name (string) 		default: comments
	 *                  'admin' if to include admin replies (bool)  default: false
	 * @version 1.1
	 * @copyright 2004, InSES Corporation and Alexey Kulikov
	 **/
	 
 	function smarty_function_comments($params, &$smarty){
		global $db;
		
		//check for optional variables
		//smarty variable name
		if(!empty($params['var'])){
			$variableName = (string)$params['var'];
		}else{
			$variableName = "comments";
		}
		
		//items to fetch
		if(!empty($params['items'])){
			$itemsToGet = (int)$params['items'];
		}else{
			$itemsToGet = 5;
		}
		
		//check if admin replies are also fetched
		if($params['admin'] == "true"){
		    $admin = null;
		}else{
		    $admin = "AND ".PREFIX."comment.level = 0";    
		}
		
		//create SQL query
		$data = $db->getAll("SELECT 
								".PREFIX."comment.commentid AS id, 
								".PREFIX."comment.content, 
								".PREFIX."comment.entryid, 
								".PREFIX."comment.intime AS comintime, 
								".PREFIX."comment.sortorder,
								".PREFIX."comment.senderName,
								".PREFIX."comment.level AS adminReply,
								".PREFIX."entry.urlcache AS url,
								".PREFIX."entry.intime
							FROM ".PREFIX."comment LEFT JOIN ".PREFIX."entry ON (".PREFIX."comment.entryid = ".PREFIX."entry.entryid)
							WHERE deleted = '0'
							$admin ORDER BY ".PREFIX."comment.intime DESC LIMIT $itemsToGet");
		
		//preformat urls	
		foreach($data as $key => $entry){
			if(ALLOW_NICEURLS){
				$data[$key]['url'] = SERVER_ROOT . date("Y/m/d/",$entry['intime']).$entry['url'].".html";
			}else{
				$data[$key]['url'] = SERVER_ROOT . "entry/" . $entry['entryid'] . "/";
			}
		}
		
		//give data to smarty and terminate
		$smarty->assign("TOTALDBQUERIES",$db->totq);
		$smarty->assign($variableName,$data);		
		return;
	}
?>