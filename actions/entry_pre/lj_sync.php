<?
    //============= LJ SYNC =====================================
    ## case LJ Syndicate ##
    if(ENABLE_LJ and $_POST['lj'] == '1'){
        require('lib/xmlrpc/xmlrpc.inc');
        require('lib/lj.class.php');

        //create LJ template
        $smarty->assign("entry",array( 	"subject"	=>	$_POST['subject'],
                                        "content"	=>	$parsedContent,
                                        "id"		=>	ENTRY_ID,
                                        "intime"	=>	$date,
                                        "url"		=>  $redirectionURL,
                                        "image"		=>	$imageTag));
        $myLJContent = $smarty->preFetch("postToLJ.htm");

        //create an LJ client
        $myLJ = new lj(LJ_USER,LJ_PASS);

        if((int)$_POST['comments'] == 1){
            $copt = false;
        }else{
            $copt = true;
        }
        
        //is this a post or an update?
        if(!empty($_POST['ljid'])){
            $myLJ->updatePost($_POST['ljid'],$_POST['subject'],$myLJContent,$date,implode(', ',$uwords),$copt);
        }else{
            if($ljID = $myLJ->addPost($_POST['subject'],$myLJContent,$date,implode(', ',$uwords),$copt)){
                $ljURL = $ljID['itemid']*256 + $ljID['anum'];
            }else{
                $ljID['itemid'] = 'null';
                $ljURL	= 'null';
            }
        }

    }else{
        $ljID['itemid'] = 'null';
        $ljURL	= 'null';
    }
    ## end case LJ Syndicate ##
    //============= END LJ SYNC =================================
?>