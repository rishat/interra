<?

	/**
	 * smarty template function
	 * package: InSpire CMS
	 * author: Kulikov Alexey
	 */

 	/**
	 * smarty_highlight()
	 * 
	 * @param $text
	 * @param $term
	 * @param $start_tag
	 * @param $end_tag
	 * @return 
	 */
	function smarty_modifier_highlight($text, $term, $start_tag='<span class=search>', $end_tag='</span>') 
	{ 
		//accept an array of terms to hightlight
		if(is_array($term)){
			while(list($key,$val) = each($term)){
				$term[$key] = preg_quote($val);
			}
			$term = implode("|",$term);
		}else{	//or a single term
			$term = preg_quote($term);
		}
		
		$text = strip_tags($text);		//careless ;)

		return preg_replace('/('.$term.')/i', $start_tag.'$1'.$end_tag, $text);
	}
?>