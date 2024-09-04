<?
	/***
		Smarty Calendar Resource built for InTerra Blog Machine
		
		@author Kulikov Alex <alex@pvl.at, alex@essentialmind.com>
		
		no mandatory params =)
		
		@param year (int)
		@param month (int)
		@param day (int)
	***/
 	function smarty_function_calendar($params, &$smarty){
		global 	$db, $cacheID;
		
		//check for incoming params =)
		//year
		if(empty($params['year'])){
			$year = date("Y");
		}else{
			$year = (int)$params['year'];
		}
		
		//month
		if(empty($params['month'])){
			$month = date("m");
		}else{
			$month = (int)$params['month'];
		}
		
		//check for selected day
    	if(!empty($params['day'])){
	    	$day = (int)$params['day'];
	    	if($day < 1){
	    		$day = 1;
	    	}elseif($day > 31){
	    		$day = 31;
	    	}
    	}else{
    		$day = null;
    		
    		//the current day must be marked, go on, find it =)
    		$thisMonth = date("m",NOW);
    		$thisYear = date("Y",NOW);	
    		
    		if($month == $thisMonth and $year == $thisYear){
    			$day = date("d",NOW);	
    		}
    	}
	    
		
	    //check for erroneous data
    	if($year < 1971){
    		$year = 1971;
    	}elseif($year > 2030){
    		$year = 2030;
    	}
    	
    	if($month < 1){
    		$month = 1;
    	}elseif($month > 12){
    		$month = 12;
    	}
    	
    	
    	//now figure out some settings
    	$calendar = array();
    	$calendar['timeStamp']			= mktime(0,0,0,$month,1,$year);
    	$calendar['year']				= $year;
    	$calendar['month']				= $month;
    	$calendar['day']				= $day;
    	$calendar['monthFull']			= date("F",$calendar['timeStamp']);
    	$calendar['firstDayOfMonth'] 	= date("w",$calendar['timeStamp']) - 1;
    	$calendar['daysInMonth'] 		= date("t",$calendar['timeStamp']);
    	$calendar['weeksInMonth']		= ceil($calendar['daysInMonth'] / 7);
    	$calendar['thisMonth']			= date("Y/m/",$calendar['timeStamp']);
    	$calendar['prevMonth']			= date("Y/m/",mktime(0,0,0,$month-1,1,$year));
    	$calendar['nextMonth']			= date("Y/m/",mktime(0,0,0,$month+1,1,$year));
    	
    	if($calendar['firstDayOfMonth'] < 0){
    		$calendar['firstDayOfMonth'] = 6;	
    	}
    	
    	//get day data
    	$dates = $db->getAssoc("SELECT DISTINCT FROM_UNIXTIME(intime,'%d') + ".$calendar['firstDayOfMonth']." AS myDays, FROM_UNIXTIME(intime,'%d') AS myDays FROM ".PREFIX."entry WHERE intime > '".mktime(0,0,0,$month,1,$year)."' AND intime < '".mktime(23,59,59,$month,$calendar['daysInMonth'],$year)."'");
    	
    	$days = array();

    	//shift empty days
    	for($i = 1; $i < $calendar['firstDayOfMonth']; $i++){
    		$days[$i] = null;
    	}
    	
    	//fill other days
		/* bug corrector */
		$endFactor = $calendar['daysInMonth'] + $calendar['firstDayOfMonth'];
		if($calendar['firstDayOfMonth'] == 0){
			$endFactor--;
		}
		/* end bug corrector */

    	for($i = $calendar['firstDayOfMonth']; $i <= $endFactor; $i++){
    		$days[$i] = array("number" => $i - $calendar['firstDayOfMonth'] + 1);
    		
    		//mark selected days with links
    		if(isset($dates[$i+1])){
    			$days[$i]['selected'] = true;	
    		}
    		
    		if(($i - $calendar['firstDayOfMonth'] + 1) < 10){
    			$days[$i]['link'] = "0".$days[$i]['number'];
    		}else{
    			$days[$i]['link'] = $days[$i]['number'];
    		}
    	}
    	
    	//figure out if selected day is in this month
    	if($day){
    		$index = $calendar['firstDayOfMonth'] + $day - 1;
    		$days[$index]['today'] = true;	
    	}
    	
 		
    	//check if navigation is to be ignored
    	if(!empty($params['ignoreNav'])){
			$calendar['showNav'] = false;
		}else{
			$calendar['showNav'] = true;
		}
		
    	//assign days to calendar
    	$calendar['days'] = $days;
		
    	//pass data to smarty
	    $smarty->assign("calendar",$calendar);
    	$smarty->assign("TOTALDBQUERIES",$db->totq);
    	
    	
    	//see the magic happen
    	$tempStatus = $smarty->caching;
    	$smarty->caching = false;
    	$data = $smarty->fetch("calendar.htm");
    	$smarty->caching = $tempStatus;
		return $data;
	}
?>