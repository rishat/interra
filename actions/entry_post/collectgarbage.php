<?
    //============= GARBAGE COLLECTOR ===========================
    //get rid of cache
    $smarty->clear_cache(null,"______rootpage");
    $smarty->clear_cache(null,"keyword");
    $smarty->clear_cache(null,date("Y|m|d",$date));

    //kill empty sections and unused keywords
    # keywords
    $keywords = array();
    $keywords = $db->getCol("SELECT DISTINCT wordid FROM ".PREFIX."keywords");
    if(!empty($keywords)){
        $db->query("DELETE FROM ".PREFIX."keyword WHERE wordid NOT IN(".implode(",",$keywords).")");
        $db->query("OPTIMIZE TABLE ".PREFIX."keyword");
    }

    #sections
    $sections = array();
    $sections = $db->getCol("SELECT DISTINCT catid FROM ".PREFIX."entry");
    if(!empty($sections)){
        $db->query("DELETE FROM ".PREFIX."category WHERE catid NOT IN(".implode(",",$sections).")");
        $db->query("OPTIMIZE TABLE ".PREFIX."category");
    }
    
    //============= END GARBAGE COLLECTOR =======================
?>