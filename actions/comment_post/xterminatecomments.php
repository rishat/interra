<?
    //new stuff for overcommented blogs
    if(AUTO_KILL_COMMENTS == '1'){
        //prepare garbage collection engine
        $cids = $db->getCol("SELECT commentid 
                                    FROM ".PREFIX."comment 
                                    LEFT JOIN ".PREFIX."entry ON ".PREFIX."comment.entryid = ".PREFIX."entry.entryid
                                    WHERE
                                        ".PREFIX."entry.intime < ".(time() - 60*60*24*COMMENT_DAYS)); $cids[] = 0;
        
        $db->Query("DELETE FROM ".PREFIX."comment WHERE commentid IN (".implode(',',$cids).")");
        $db->Query("UPDATE ".PREFIX."entry SET commentcount = 0 WHERE intime < ".(time() - 60*60*24*COMMENT_DAYS));
    }
?>