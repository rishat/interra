<?
	/****************************************************************************
	* Software distributed under the License is distributed on an "AS IS" 		*
	* basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the 	*
	* License for a specific language governing rights and limitations under	*
	* the License. 																*
	* 																			*
	*	The Original Code is - InTerra Blog Machine   				            *
	* 																			*
	*	The Initial Developer of the Original Code is 							*
	* 																			*
	* 			Kulikov Alexey <alex [at] essentialmind [dot] com>	 			*
	* 																			*
	* 				All Rights Reserved // www.inses.ru							*
	*****************************************************************************/

	//disable cache
	$smarty->caching = false;

	//useful stuff =))
	function adds(&$el,$level=0) {
		if (is_array($el)) {
			if (get_magic_quotes_gpc()) return;
			foreach($el as $k=>$v) adds($el[$k],$level+1);
		} else {
			if (!get_magic_quotes_gpc()) $el = addslashes($el);
			if (!$level) return $el;
		}
	}
	
	
	function getSortOrder($commentID,$order=0){
	    global $db;
	    
	    //echo "SELECT commentid, sortorder FROM ".PREFIX."comment WHERE parent = '".$commentID."' ORDER BY sortorder DESC LIMIT 1<br />";
	    //exit;
	    
	    //first get the last in the list (if any)
	    if($data = $db->getRow("SELECT commentid, sortorder FROM ".PREFIX."comment WHERE parent = '".$commentID."' ORDER BY sortorder DESC LIMIT 1")){
	        
	        if($data['sortorder'] > $order){
	            $myOrder = getSortOrder($data['commentid'],$data['sortorder']);
	        }else{
	            $myOrder = $order;
	        }
	    }else{
	       $myOrder = (int)$db->getOne("SELECT sortorder FROM ".PREFIX."comment WHERE commentid = '".$commentID."'");
	    }
	    
	    return $myOrder;
	}



	//first check if the comment may be added
	if(ANTISPAM and strtolower($_SESSION['challenge']) != strtolower($_POST['antispam']) and !$_SESSION['admin']){
        $noComment = true;
        setcookie("tPost",$_POST['comment'],time()+120,"/");
        $_SESSION['error'] = true;
	}else{
	    $noCommanet = false;
	    setcookie("tPost",null,time(),"/");
	    unset($_SESSION['error']);
	}


    
	if($_POST and !$noComment){
		$entryID = (int)$_GET['id'];
        
		//check if this post allows comments
		$entry = $db->getRow("SELECT content, comments, intime FROM ".PREFIX."entry WHERE entryid = '".$entryID."'");
		if($entry['comments'] =='1' and $entry['intime'] > NOW - 60*60*24*COMMENT_DAYS){

            //mark the id of the comment being replied to
            $replyTo = $_POST['replyto'];
            $unprocessedComment = $_POST['comment'];
            
			if($_SESSION['admin'] and $_POST['replyto']){	//this is an admin reply
				//get some libraries
				require("lib/nwacko/classes/macroProcessor.class.php");
				$parser = &new macroProcessor();

				//this is an admin reply =) be cool =)
				$adminReply = true;
				$mail       = BLOGMASTER;
				$sender     = BLOGMASTER;
				$comment    = $parser->process($_POST['comment']);

			}else{	//this is a mortal comment
				//include safeHTML parser
				require('lib/safehtml/classes/HTMLSax.php');
				require('lib/safehtml/classes/safehtml.php');

				//prepare contents
				$comment = strip_tags($_POST['comment'],"<a>,<i>,<b>");
				$sender  = htmlspecialchars(trim($_POST['sender']));
				$mail    = htmlspecialchars(trim($_POST['mail']));
				$url     = htmlspecialchars(trim($_POST['url']));
				
				if($url and !eregi('^http:',$url)){
				    $url = 'http://'.$url;
				}

				//autolink
				$comment = preg_replace('#[^\"=]http://([^\s]*)#', ' [&nbsp;<b><a href="http://\\1">'.$langData['link'].'</a></b>&nbsp;]', $comment);
				$comment = preg_replace('#^http://([^\s]*)#', ' [&nbsp;<b><a href="http://\\1">'.$langData['link'].'</a></b>&nbsp;]', $comment);

				###################################
				//parse contents through safehtml
				$comment = stripslashes($comment);

				// Save all "<" symbols
				$comment = preg_replace("/<(?=[^a-zA-Z\/\!\?\%])/", "&lt;", $comment);

				// Opera6 bug workaround
				$comment = str_replace("\xC0\xBC", "&lt;", $comment);

				// Instantiate the handler  parser
				$handler=& new safehtml();
				$parser=& new XML_HTMLSax();

				// Register the handler with the parser
				$parser->set_object($handler);

				// Set the handlers
				$parser->set_element_handler('openHandler','closeHandler');
				$parser->set_data_handler('dataHandler');
				$parser->set_escape_handler('escapeHandler');
				$parser->parse($comment);
				$comment = $handler->getXHTML();
				###################################


				//set cookies =)
				setcookie("sender",$sender,time()+24*60*60*365,"/");
				setcookie("senderMail",$mail,time()+24*60*60*365,"/");
				setcookie("senderURL",$url,time()+24*60*60*365,"/");
				
				//errors =)
				if(empty($sender)){
					$sender = "anonymous @ " . $_SERVER['REMOTE_ADDR'];
				}

				//fix some inputs
				$comment = ereg_replace("[\r\n|\r]","\n",trim($comment));
				$comment = ereg_replace("[\r\n|\n|\r]{3,}","\n\n",$comment);
				$comment = "<p>".nl2br($comment)."</p>";

				$adminReply = false;
			}
			
			
			## COMMENT ADD PREPROCESSORS ##
            #
            foreach(glob('actions/comment_pre/*.php') as $mfile) {
                include_once($mfile);
            }            
            #
            ## //	
                

			//there needs to be some kind of comment, right?
			if(!empty($_POST['comment'])){
				if(eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$",$mail)){
					$notify = 1;
				}else{
					$notify = 0;
				}

				//we are cool, process database query
				$db->query("LOCK TABLE ".PREFIX."comment WRITE");	//start "transaction"

                //level mon
                $level     = (int)$db->getOne("SELECT level + 1 FROM ".PREFIX."comment WHERE commentid = '".$replyTo."'");
                $commentID = (int)$db->getOne("SELECT max(commentid) FROM ".PREFIX."comment") + 1;
                $parent    = $replyTo;
                
                ## yukk (quick hack) ##
                #
                //can we have a comment tree?
                if(!COMMENT_TREE and !$_SESSION['admin']){
                    $replyTo = null;
                }
                
                //figure out sort_order
                if(!empty($replyTo)){
                    $sortOrder = getSortOrder($replyTo) + 1;
                }else{
                    $sortOrder = (int)$db->getOne("SELECT max(sortorder) FROM ".PREFIX."comment WHERE entryid = '".$entryID."'") + 1;
                }
                #
                ## /end yukk ##
                
                //shift the whole list
                $res       = $db->query("UPDATE ".PREFIX."comment SET sortorder = sortorder + 1 WHERE entryid = '".$entryID."' AND sortorder >= '$sortOrder'");
                
				//admin replies are a bit different
				if($adminReply){
					$notify = 0;

					//admin replies have special addslashes rules
					$comment = adds($comment);
					
					$admin  = '1';
				}else{

					//mortal comments have all slashes escaped by default
					if (get_magic_quotes_gpc()) $comment = addslashes($comment);
					
					$admin = '0';
				}

                //comment intime
                $intime = NOW + (int)OFFSET;
                
                //alter table ".PREFIX."comment add senderURL varchar(255);
				$res = $db->query("INSERT INTO ".PREFIX."comment SET
										commentid 	= '$commentID',
										content 	= '".$comment."',
										senderName	= '".adds($sender)."',
										senderMail	= '".adds($mail)."',
										".($url?" senderURL = '".adds($url)."', ":'')."
										entryid		= '$entryID',
										intime		= '".$intime."',
										notify		= '$notify',
										sortorder	= '$sortOrder',
										level		= '$level',
										admin       = '".$admin."',
										ip          = '".ip2long($_SERVER['REMOTE_ADDR'])."',
										parent		= '".$parent."'");

				$db->query("UNLOCK TABLES");	//end "transaction"

				//important, keeps counter intact
				if(!DB::isError($res)){
					$db->query("UPDATE ".PREFIX."entry SET commentcount = commentcount + 1 WHERE entryid = '".$entryID."'");
				}

                
                ## COMMENT ADD POSTPROCESSORS ##
                #
                foreach(glob('actions/comment_post/*.php') as $mfile) {
                include_once($mfile);
            }
                #
                ## //													
			}
		}
	}


	//this is a patch for opera and older versions of IE as they don't recognize internal redirects from other pages as page refresh commands
	if(stristr($_SERVER['HTTP_USER_AGENT'],"opera") or stristr($_SERVER['HTTP_USER_AGENT'],"MSIE 5.")){
		header("Location: ".REDURL);
	}else{
		header("Location: ".REDURL."#".$sortOrder);
	}
	exit;
?>