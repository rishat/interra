<?
	class macroProcessor{
		var $parser;

		function macroProcessor(){
		    if(defined("ROOT_DIR")){
		        require_once(ROOT_DIR."lib/nwacko/classes/WackoFormatter.php");
		    }else{
			    require_once("lib/nwacko/classes/WackoFormatter.php");
		    }
			$this->parser = &new WackoFormatter();
		}
		
		/**
		 * process() - will process a string replacing special tags with HTML
		 * 
		 * @param $data (string)
		 * @return (string)
		 */
		function process($data,$format=true,$forceCompile=true){
			//slashes
			if (get_magic_quotes_gpc()) $data = stripslashes($data);

			// create object with custom code and config for formatter
			$Config = &new CustomConfig();

			$this->parser->SetObject($Config);

			// make preformatted text 
			$preformatted = $this->parser->Format($data);
			
			// add typografica & paragrafica
			// (if you don`t need them, simply remove next 6 lines)
			if(defined("ROOT_DIR")){
			    require_once(ROOT_DIR."lib/nwacko/classes/typografica.php");
    			require_once(ROOT_DIR."lib/nwacko/classes/paragrafica.php");
			}else{
    			require_once("lib/nwacko/classes/typografica.php");
    			require_once("lib/nwacko/classes/paragrafica.php");
			}

			$typo = new typografica( $Config );
			$para = new paragrafica( $Config );
			$preformatted = $typo->correct($preformatted);
			$preformatted = $para->correct($preformatted);
			$preformatted = $this->parser->PostFormat($preformatted);
			
			if (get_magic_quotes_gpc()) $preformatted = addslashes($preformatted);
			
			//process InTerra Cuts
			$preformatted = preg_replace_callback("/\[cut (text=)?(.*?)\](.*?)\[\/cut\]/si","ljCut",$preformatted);
			$preformatted = preg_replace_callback("/\[cut (text=)?(.*?)\](.*?)\z/si","ljCut",$preformatted);
			$preformatted = preg_replace_callback("/\[cut\](.*?)\z/si","ljCut",$preformatted);

			// make postformatting
			return $preformatted;
		}	
	}
	
	//init global counter (i know this is bad)
	$cutCount = 0;

	//a boring neccessity
	function ljCut($matches){
		global $cutCount;
		$cutCount++;
		
		if(empty($matches[2])){
			$matches[2] = "Read On...";
			$matches[3] = $matches[1];
		}
		
		$matches[2] = strip_tags(str_replace("&nbsp;"," ",$matches[2]));
		return "<a name=\"cut".$cutCount."\"></a><lj-cut text=\"".$matches[2]."\">".$matches[3]."</lj-cut>";
	}
?>