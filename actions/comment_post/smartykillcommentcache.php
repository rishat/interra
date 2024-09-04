<?
    //kill cache if any
    if(SMARTY_ALLOW_CACHE){
        $smarty->clear_cache(null,"entry|$entryID");
        $smarty->clear_cache(null,"______rootpage");
        $smarty->clear_cache(null,YEAR."|".MONTH."|".DAY);
    }
?>