<?
	/****
	 InTerra Blog Machine <lj-cut> parser
	 Author: Kulikov Alexey
	 Version: 1.0
	****/
	function smarty_modifier_cut($string, $link, $text="Continue..."){
		//process <lj-cut text=...></lj-cut>
		$string = preg_replace("/<a name=\"cut([0-9]{1,})\"><\/a><lj-cut text=\"(.*?)\">(.*?)<\/lj-cut>/si", "<span class=\"inTerraCut\">[ <a href=\"$link#cut$1\">$2</a> ]</span>", $string);
        //$string = preg_replace("/<lj-cut text=\"(.*?)\">(.*?)(<\/lj-cut>)?/si", "<span class=\"inTerraCut\">[ <a href=\"$link\">$1</a> ]</span>", $string);

		//process --------------- (recall to older versions of InTerra)
		//$string = preg_replace("/<hr (.*?)>(.*?)\z/si", "<span class=\"inTerraCut\">[ <a href=\"$link\">$text</a> ]</span>", $string);

	    return $string;
	}
?>