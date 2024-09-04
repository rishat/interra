<?
	/**
	 * smarty template function
	 * name: entry fetch resource
	 * package: InTerra Blog Machine
	 * author: Kulikov Alexey <alex [at] inses [dot] ru
	 * 
	 * params:
	 *   optional:		'items' items to fetch (int)				default: 5
	 * 					'var' smarty variable name (string) 		default: entries
	 * 					'sortby' sortorder of entries (string)		default: intime
	 *					'sortpref' sort preference (string)			default: desc
	 * 					'section' limit by section (int)			default: null (no limit)
	 *					'intime' limit by time in days (int)		default: 90
	 *                  'year' the year for which to load           default: null
	 *                  'month' the month for which to load         default: null
	 * @version 1.2
	 * @copyright 2004, InSES Corporation and Alexey Kulikov
	 **/
	 
 	function smarty_function_entries($params, &$smarty){
		global $db;
		
		
		//check for optional variables
		//smarty variable name
		if(!empty($params['var'])){
			$variableName = (string)$params['var'];
		}else{
			$variableName = "entries";
		}
		
		//items to fetch
		if(!empty($params['items'])){
		    if($params['items'] == 'all'){
		        $itemsToGet = null;
		    }else{
			    $itemsToGet = (int)$params['items'];
			}
		}else{
			$itemsToGet = 5;
		}
		
		//sort by
		if(!empty($params['sortby'])){
			switch($params['sortby']){
				case 'subject':		{$sortBy = "subject"; break;}
				case 'comments':	{$sortBy = "commentcount"; break;}
				case 'random':		{$sortBy = "rand()"; break;}
				default:			{$sortBy = "intime";}	
			}
		}else{
			$sortBy = "intime";
		}
		
		//sort preference
		if(!empty($params['sortpref'])){
			$sortPref = "asc";
		}else{
			$sortPref = "desc";
		}
		
		//erro check
		if($sortBy == 'rand()'){
			$sortPref = null;
		}
		
		//section
		if(!empty($params['section'])){
			$section = "AND catid = '" . (string)$params['section'] . "'";
		}else{
			$section = null;
		}
		
		//time limit
		if(!empty($params['intime'])){
			$intime = NOW - abs((int)$params['intime']) * 60 * 60 * 24;
		}else{
		    if(!empty($params['year'])){
		        if(!empty($params['month'])){
		            $intime = " (intime > ".strtotime($params['year'].'-'.$params['month'].'-01 00:00')." AND intime < ".strtotime($params['year'].'-'.$params['month'].'-'.date('t',strtotime($params['year'].'-'.$params['month'].'-01')).' 23:59')." ) ";
		        }else{
		            $intime = " (intime > ".strtotime($params['year'].'-01-01 00:00')." AND intime < ".strtotime($params['year'].'-12-31 23:59')." ) ";
		        }
		    }else{
			    $intime = " intime > ".(int)(NOW - 90 * 60 * 60 * 24);
			}
		}
		
		//create SQL query
		$data = $db->getAll("SELECT 
								subject,
								content_p AS content, 
								commentcount, 
								intime, 
								".PREFIX."entry.entryid AS id,
								urlcache AS url 
							 FROM ".PREFIX."entry 
							 WHERE 
							    $intime $section ORDER BY $sortBy $sortPref ".($itemsToGet?" LIMIT $itemsToGet":""));
		
		
		//preformat urls	
		foreach($data as $key => $entry){
			if(ALLOW_NICEURLS){
				$data[$key]['url'] = SERVER_ROOT . date("Y/m/d/",$entry['intime']).$entry['url'].".html";
			}else{
				$data[$key]['url'] = SERVER_ROOT . "entry/" . $entry['id'] . "/";
			}
		}
		
		//give data to smarty and terminate
		$smarty->assign("TOTALDBQUERIES",$db->totq);
		$smarty->assign($variableName,$data);		
		return;
	}
?>