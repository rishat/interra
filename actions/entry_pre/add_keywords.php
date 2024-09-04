<?
    //============= KEYWORDS ====================================
    //keywords check
    if(ALLOW_KEYWORDS){
        $db->query("DELETE FROM ".PREFIX."keywords WHERE entryid = '".ENTRY_ID."'");
        $keywords = explode(",",$_POST['keywords']);
        $keywords = array_diff($keywords,array(''));
        $uwords   = array();

        foreach($keywords as $key => $keyword){
            //get rid of trailing spaces
            $keyword = trim($keyword);

            //create valud url from keyword
            $unixkey = $myTrans->UrlTranslit($keyword);
            $uwords[]= $unixkey;

            //last bogus check
            if(!empty($keyword)){
                //check if already in database
                if($keyWordID = $db->getOne("SELECT wordid FROM ".PREFIX."keyword WHERE unixword = '".$unixkey."'")){
                    //kewl push a reference to it into the database
                    $db->query("INSERT INTO ".PREFIX."keywords(entryid,wordid) VALUES('".ENTRY_ID."','".$keyWordID."')");
                }else{
                    //no so kewl, insert the keyword into the database, then push a reference
                    $keyWordID = (int)$db->getOne("SELECT max(wordid) FROM ".PREFIX."keyword") + 1;
                    $db->query("INSERT INTO ".PREFIX."keyword VALUES('".$keyWordID."','".$keyword."','".$unixkey."')");
                    $db->query("INSERT INTO ".PREFIX."keywords(entryid,wordid) VALUES('".ENTRY_ID."','".$keyWordID."')");
                }
            }
            $keywords[$key] = $keyword."|".$unixkey;
        }

        $keywords = implode(",",$keywords);
    }else{
        $keywords = null;
        $uwords   = array();
    }
    //============= END KEYWORDS ==============================
?>