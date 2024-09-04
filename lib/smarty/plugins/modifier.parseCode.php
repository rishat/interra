<?

	/**
	 * smarty template function
	 * package: InSpire CMS
	 * author: Kulikov Alexey
	 */

 	/**
	 * parseCode() - Smarty Modifier - will create a nice table of
	 * 							 all the <code> data in the passed string.
	 * 
	 * @param $string (string)
	 * @return 
	 */
	function smarty_modifier_parseCode($string){
		return preg_replace_callback("/<code>(.*?)<\/code>/si",_makeCode,$string);
	}
	
	
	/**
	 * _makeCode() - callback from parseCode();
	 * 
	 * @param $matches
	 * @return 
	 */
	function _makeCode($matches){
		$lines = explode("\n",trim($matches[1]));
		if(count($lines) < 2){
			$lines = explode("<br>",trim($matches[1]));
		}
		$toReturn = "<ol class=code>";
		foreach($lines as $line){
			$line = ereg_replace("[\t]","&nbsp;",$line);
			$toReturn .= "<li><code>$line</code></li>";
		}
		$toReturn .= "</ol>";
		return $toReturn;
	}

?>