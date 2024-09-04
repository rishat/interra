<?
    ### e-mail shit happens below ###
    #
    //disable smarty cache
    $smarty->caching = false;
    $smarty->autoload_filters = array('output' => array());
    
    
    //check
    $comment            = str_replace('&nbsp;',' ',$comment);
    $unprocessedComment = str_replace('&nbsp;',' ',$unprocessedComment);
    
    
    //is this a ROOT post (no replyto)
    if(empty($replyTo)){ //yes
        //notify admin only
        
        //quick hack to get rid of sorting IDs
        $sortOrder = $db->getOne("SELECT commentid FROM ".PREFIX."comment WHERE entryid = '$entryID' AND sortorder = '".(int)($sortOrder-1)."'");
        
        //unlink comment short tag (1.30)
        $comment = preg_replace('/\[&nbsp;<b><a href=\\\"(.*)\\\">(.*)<\/a><\/b>&nbsp;\]/', '\\1', $comment);
    
        //get some data to smarty
        $smarty->assign(array(
                            "sender"		=> $sender,
                            "senderMail"	=> $mail,
                            "comment"		=> strip_tags(stripslashes($comment)),
                            "entry"			=> $entry['content'],
                            "entryID"		=> $entryID,
                            "commentID"		=> $commentID,
                            "commentSO"		=> $sortOrder,
                            "url"			=> REDURL . "#" . $sortOrder
                            ));
    
        ## Notify Admin ##
        $message = $smarty->preFetch("commentAdmin.txt");
        
        //check of allowed to send e-mail and send it of so =)
        if(ENABLE_BMMAIL){
            @mail(BLOGMASTER,$langData['comNew'],$message,"FROM: ".MAILBOT."\nContent-type: text/plain; charset=".CHARSET);
        }
        
        
        
    }else{
        //quick hack to get rid of sorting IDs
        $sortOrder = $replyTo;
    
        //notify whoever wants to be notified
        $mailData = $db->getRow("SELECT senderName, senderMail, notify, content FROM ".PREFIX."comment WHERE commentid = '".$replyTo."'");
    
        if($mailData['notify'] == '1'){
            $smarty->assign(array(
                                "sender"		=> " ".$mailData['senderName'],
                                "senderMail"	=> $mailData['senderMail'],
                                "comment"		=> strip_tags(stripslashes($unprocessedComment)),
                                "entry"			=> strip_tags($mailData['content']),
                                "entryID"		=> $entryID,
                                "commentID"		=> $replyTo,
                                "commentSO"		=> $sortOrder,
                                "url"			=> REDURL . "#" . $sortOrder
                                ));
    
            $message = $smarty->preFetch("commentUser.txt");
            
            //check of allowed to send e-mail and send it of so =)
            if(ENABLE_UMAIL){
                @mail($mailData['senderMail'],$langData['comReply'],$message,"FROM: ".MAILBOT."\nContent-type: text/plain; charset=".CHARSET);
            }
        }
        
        //get a copy to admin as well???
        if(!$adminReply){
            $smarty->assign(array(
                                "admintext"     => true,
                                "sender"		=> "",
                                "senderMail"	=> $sender . " ".$mail,
                                "comment"		=> strip_tags(stripslashes($unprocessedComment)),
                                "entry"			=> strip_tags($mailData['content']),
                                "entryID"		=> $entryID,
                                "commentID"		=> $replyTo,
                                "commentSO"		=> $sortOrder,
                                "url"			=> REDURL . "#" . $sortOrder
                                ));
    
            $message = $smarty->preFetch("commentUser.txt");
            
            //check of allowed to send e-mail and send it of so =)
            if(ENABLE_BMMAIL){
                @mail(BLOGMASTER,$langData['comNew'],$message,"FROM: ".MAILBOT."\nContent-type: text/plain; charset=".CHARSET);
            }
        }
    }                                
    #
    ### /end e-mail shit
?>