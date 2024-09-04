<?
    //include stuff
	include("lib/simpleRSS.class.php");

	/***
	 RSS Compiler, we are all we've got =)
	***/
	function recompileRSS($section=null){
		global $db;

		if(!$section){
			$desc    = RSS_DESC;
			$title   = RSS_HEADER;
			$link    = SERVER_ROOT;
			$entries = $db->getAll("SELECT subject, entryid, content_p, intime, urlcache AS url FROM ".PREFIX."entry WHERE format = '1' ORDER BY intime DESC LIMIT 10");
			$file    = "entry.xml";
		}else{
			$section = $db->getRow("SELECT * FROM ".PREFIX."category WHERE catid = '$section'");
			$desc    = RSS_DESC . " // " . $section['fullName'];
			$title   = RSS_HEADER . " // " . $section['fullName'];
			$link    = SERVER_ROOT . $section['name'] . "/";
			$file    = $section['name'].".xml";

			$entries = $db->getAll("SELECT subject, entryid, content_p, intime, urlcache AS url FROM ".PREFIX."entry WHERE catid = '".$section['catid']."' ORDER BY intime DESC LIMIT 10");
		}

		$myRSS = new simpleRSS(	$title,
		 						$link,
		 						$desc,
								array(
										"ttl"			=>	60,
										"lastBuildDate"	=>	date("r"),
										"generator"		=>	"InTerra Blog Machine"));

		//get latest entries
		foreach($entries as $entry){
		    
			if(ALLOW_NICEURLS){	//different link formatting =)
			    $link = SERVER_ROOT.date("Y/m/d/",$entry['intime']).$entry['url'].".html";
			    $entry['content_p'] = preg_replace("/<a name=\"cut([0-9]{1,})\"><\/a><lj-cut text=\"(.*?)\">(.*?)<\/lj-cut>/si", "<span class=\"inTerraCut\">[ <a href=\"$link#cut$1\">$2</a> ]</span>", $entry['content_p']);
				$myRSS->addItem($entry['subject'],$link,htmlspecialchars($entry['content_p']),array("pubDate"=>date("r",$entry['intime'])));
				
			}else{
			    $link = SERVER_ROOT."entry/".$entry['entryid']."/";
			    $entry['content_p'] = preg_replace("/<a name=\"cut([0-9]{1,})\"><\/a><lj-cut text=\"(.*?)\">(.*?)<\/lj-cut>/si", "<span class=\"inTerraCut\">[ <a href=\"$link#cut$1\">$2</a> ]</span>", $entry['content_p']);

				$myRSS->addItem($entry['subject'],$link,htmlspecialchars($entry['content_p']),array("pubDate"=>date("r",$entry['intime'])));
			}
		}

		$myRSS->create("rss/".$file);
	}
	
    //RSS compiler
    recompileRSS();
    if($catID > 0){
        recompileRSS($catID);
    }
?>