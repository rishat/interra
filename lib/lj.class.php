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
	* 							All Rights Reserved. 							*
	* 																			*
	*****************************************************************************/
	
	class lj{
		var $user;	//@private
		var $pass;	//@private
		var $lj;	//@private
		
		/***
		 Concstructor
		 
		 @param user	-	user name
		 @param pass	-	password
		***/
		function lj($user,$pass){
			$this->createClient();
			$this->setUser($user);
			$this->setPass($pass);
		}
		
		/***
		 function lj::addPost
		 
		 purpose	add a new entry to the specified livejournal
		 
		 @param subject (string)
		 @param content (string)
		 @param date	(int -- unixtimestamp)
		 
		 @return array(itemid, anum), in order to calculate the itemid URL
		 you have to use the following formula: itemid*256 + anum
		***/
		function addPost($subject, $content, $date, $tags=null, $nocomments=false){
			//set message handler
			$message = new xmlrpcmsg('LJ.XMLRPC.postevent');
			
			//prepate message
			$message->addParam(xmlrpc_encode2(array(
													"username"			=>	$this->getUser(),
													"hpassword"			=>	md5($this->getPass()),
													"clientversion"		=>	"InTerra Blog Machine",
													"event"				=>	$this->utf_encode("<lj-raw>".stripslashes($content)."</lj-raw>"),
													"subject"			=>	$this->utf_encode(stripslashes($subject)),
													"lineendings"		=>	"0x0A",
													"year"				=>	date("Y",$date),
													"mon"				=>	date("m",$date),
													"day"				=>	date("d",$date),
													"hour"				=>	date("H",$date),
													"min"				=>	date("i",$date),
													"ver"				=> 1,
													"props"				=>	array(
																					"taglist"			=>	$this->utf_encode($tags),
																					"opt_nocomments"	=>	(bool)$nocomments
																				)

													)));
			
			//send the message to the server
			$response = $this->lj->send($message);
			
			//check what the reply is
			if($response->value()){	# all cool
				$response = xmlrpc_decode2($response->value());
				return $response;
			}
			return false;
		}
		
		
		/***
		 function lj::updatePost
		 
		 purpose	updates an entry in the specified livejournal
		 
		 @param	entryid	(int)
		 @param subject (string)
		 @param content (string)
		 @param date	(int -- unixtimestamp)
		 @return bool
		***/
		function updatePost($entryid,$subject,$content,$date,$tags=null,$nocomments=false){
			//set message handler
			$message = new xmlrpcmsg('LJ.XMLRPC.editevent');
			
			//prepare content such that deletion works =)
			if($content){
				$content = "<lj-raw>".stripslashes($content)."</lj-raw>";	
			}
			
			//prepate message
			$message->addParam(xmlrpc_encode2(array(
													"username"			=>	$this->getUser(),
													"hpassword"			=>	md5($this->getPass()),
													"clientversion"		=>	"InTerra Blog Machine",
													"event"				=>	$this->utf_encode($content),
													"subject"			=>	$this->utf_encode(stripslashes($subject)),
													"lineendings"		=>	"0x0A",
													"year"				=>	date("Y",$date),
													"mon"				=>	date("m",$date),
													"day"				=>	date("d",$date),
													"hour"				=>	date("H",$date),
													"min"				=>	date("i",$date),
													"itemid"			=>	$entryid,
													"ver"				=>	1,
													"props"				=>	array(
																					"taglist"			=>	$this->utf_encode($tags),
																					"opt_nocomments"	=>	(bool)$nocomments
																				)
													)));

			
			//send the message to the server
			$response = $this->lj->send($message);
			
			//check what the reply is
			if($response->value()){	# all cool
				return true;
			}
			return false;
		}
		
		
		/***
		 function lj::dropPost()
		 
		 purpose	deletes an entry in the specified livejournal
		 
		 @param	entryid	(int)
		***/
		function dropPost($entryid){
			return $this->updatePost($entryid,null,null,time());
		}
		
		
		/***
		 function lj::getPosts()
		 
		 purpose	returns an array of posts from a selected LJ (max 50)
		 
		 @param		beforedate (timestamp)
		 @param		howmany	(int) 
		***/
		function getPosts($beforedate=null,$howmany=50){
			$message = new xmlrpcmsg('LJ.XMLRPC.getevents');
			
			//prepare delim
			if(empty($beforedate)){
				$beforedate = date("Y-m-d H:i:s");	
			}else{
				$beforedate = date("Y-m-d H:i:s",$beforedate);	
			}
			
			//prepate message
			$message->addParam(xmlrpc_encode2(array(
													"username"			=>	$this->getUser(),
													"hpassword"			=>	md5($this->getPass()),
													"ver"				=>	1,
													"selecttype"		=>	"lastn",
													"beforedate"		=>	$beforedate,
													"howmany"			=>	$howmany,
													"lineendings"		=>	"0x0A"
													)));
			
			//send the message to the server
			$response = $this->lj->send($message);
			
			//check what the reply is
			if($response->value()){	# all cool
				$response = xmlrpc_decode2($response->value());
				
				//utf decode events & pre-process raw data
				foreach($response['events'] as $key => $val){
					$response['events'][$key]['event'] 		= $this->utf_decode($val['event']);
					$response['events'][$key]['subject'] 	= $this->utf_decode(strip_tags($val['subject']));
					$response['events'][$key]['eventtime'] 	= strtotime($val['eventtime']);
					
					//process props
					if(!$val['props']['opt_preformatted']){
						$response['events'][$key]['event'] = nl2br($response['events'][$key]['event']);
					}
					
					//adjust comments on/off feature
					if($val['props']['opt_nocomments']){
						$response['events'][$key]['comments'] = 0;
					}else{
						$response['events'][$key]['comments'] = 1;
					}
					
					//adjust security mask
					/*
					if($toget == 1){ //open & friends
						if($response['events'][$key]['security'] == "private"){ //get rid of "private" posts
							unset($response['events'][$key]);
						}
					}elseif($toget == 0){ //only open
						if($response['events'][$key]['security'] == "private" or $response['events'][$key]['security'] == "usemask"){ //get rid of "private" and "friends" posts
							unset($response['events'][$key]);
						}
					}
					*/
				}
				
				return $response['events'];
			}
			return false;
		}
		
		
		
		/***
		 function lj::friendOf()
		 
		 purpose	returns an array of friend of from a selected LJ
		***/
		function friendOf(){
			$message = new xmlrpcmsg('LJ.XMLRPC.friendof');		
			
			//prepate message
			$message->addParam(xmlrpc_encode2(array(
													"username"			=>	$this->getUser(),
													"hpassword"			=>	md5($this->getPass()),
													"ver"				=>	1
													)));
													
			//send the message to the server
			$response = $this->lj->send($message);
			
			//check what the reply is
			if($response->value()){	# all cool
				$response = xmlrpc_decode2($response->value());
				
				//utf decode events
				foreach($response['friendofs'] as $key => $val){
					$response['friendofs'][$key]['fullname'] = $this->utf_decode($val['fullname']);
				}
				
				return $response['friendofs'];
			}
			return false;
		}
		
		
		
		/***
		 function lj::getFriends()
		 
		 purpose	returns an array of friends from a selected LJ
		***/
		function getFriends(){
			$message = new xmlrpcmsg('LJ.XMLRPC.getfriends');		
			
			//prepare message
			$message->addParam(xmlrpc_encode2(array(
													"username"			=>	$this->getUser(),
													"hpassword"			=>	md5($this->getPass()),
													"ver"				=>	1
													)));
													
			//send the message to the server
			$response = $this->lj->send($message);
			
			//check what the reply is
			if($response->value()){	# all cool
				$response = xmlrpc_decode2($response->value());
				
				//utf decode events
				foreach($response['friends'] as $key => $val){
					$response['friends'][$key]['fullname'] = $this->utf_decode($val['fullname']);
				}
				
				return $response['friends'];
			}
			return false;	
		}
		
		
		
		/***
		 function lj::addFriends()
		 
		 purpose	adds a list of friends to the selected LJ
		 
		 @param friends (array)
		***/
		function addFriends($friends){	
			//bogus check
			if(!is_array($friends)){
				return false;	
			}
			
			//start message
			$message = new xmlrpcmsg('LJ.XMLRPC.editfriends');	
			
			//prepare friends list
			$userdata = array();
			foreach($friends as $user){
				$userdata[] = new xmlrpcval(array("username" => new xmlrpcval($user,'string')),'struct');
			}
			
			//prepare message
			$message->addParam(new xmlrpcval(
											array(
												"username" 			=> new xmlrpcval($this->getUser(),'string'),
												"hpassword"			=> new xmlrpcval(md5($this->getPass()),'string'),
												"clientversion"		=> new xmlrpcval("InTerra Blog Machine",'string'),
												"add"				=> new xmlrpcval($userdata,'array'),
											),
									'struct')
								);
			
			//send the message to the server
			$response = $this->lj->send($message);
			
			//check what the reply is
			if($response->value()){	# all cool
				$response = xmlrpc_decode2($response->value());
				return $response;
			}
			return false;
		}
		
		
		/***
		 function lj::dropFriends()
		 
		 purpose	drops a list of friends from the selected LJ
		 
		 @param friends (array)
		***/
		function dropFriends($friends){
			//bogus check
			if(!is_array($friends)){
				return false;	
			}
			
			//start message
			$message = new xmlrpcmsg('LJ.XMLRPC.editfriends');	
			
			//prepare friends list
			$userdata = array();
			foreach($friends as $user){
				$userdata[] = new xmlrpcval($user,'string');
			}
			
			//prepare message
			$message->addParam(new xmlrpcval(
											array(
												"username" 			=> new xmlrpcval($this->getUser(),'string'),
												"hpassword"			=> new xmlrpcval(md5($this->getPass()),'string'),
												"clientversion"		=> new xmlrpcval("InTerra Blog Machine",'string'),
												"delete"			=> new xmlrpcval($userdata,'array'),
											),
									'struct')
								);
			
			//send the message to the server
			$response = $this->lj->send($message);
			
			//check what the reply is
			if($response->value()){	# all cool
				$response = xmlrpc_decode2($response->value());
				return $response;
			}
			return false;	
		}
		
		
		/***
		 function lj::login()
		 
		 purpose	verifies if the password and user pair match with the LJ server
		 
		 @return bool
		***/
		function login(){
			$message = new xmlrpcmsg('LJ.XMLRPC.login');	
			
			//prepate message
			$message->addParam(xmlrpc_encode2(array(
													"username"			=>	$this->getUser(),
													"hpassword"			=>	md5($this->getPass()),
													"ver"				=>	1,
													)));
			
			//send the message to the server
			$response = $this->lj->send($message);
			
			//check what the reply is
			if($response->value()){	# all cool
				return true;
			}
			
			return false;
		}
		
		
		
		/***
		 lj::setUser()
		 
		 sets the current LJ user
		 
		 @param user (string)
		***/
		function setUser($user){
			$this->user = $user;
		}
		
		
		/***
		 lj::setPass()
		 
		 sets the current LJ password
		 
		 @param pass (string)
		***/
		function setPass($pass){
			$this->pass = $pass;	
		}
		
		
		/***
		 lj::createClient()
		 
		 initializes a livejournal RPC client
		 
		 @param server (string)
		 @param interface (string)
		 @param port (int)
		 @access private
		***/
		function createClient($server='www.livejournal.com', $interface='/interface/xmlrpc', $port=80){
			$this->lj = new xmlrpc_client($interface, $server, $port);
		}
		
		
		/***
		 lj::getUser()
		 
		 @return the current LJ user
		***/
		function getUser(){
			return $this->user;	
		}
		
		
		/***
		 lj::getPass()
		 
		 @return the current lj pass
		***/
		function getPass(){
			return $this->pass;
		}

		
		/***
		 lj:utf_encode()
		 
		 to encode win1251 data into pure utf-8
		 
		 @param s (string)
		 @access private
		***/
		function utf_encode($s){
		    if(function_exists('iconv')){
				return iconv("CP1251","UTF-8",$s);
			}
			else
			{
				return $this->CP1251toUTF8($s);
			}
		}
		
		
		/***
		 lj:utf_decode()
		 
		 to decode utf-8 data into pure win1251
		 
		 @param s (string)
		 @access private
		***/
		function utf_decode($s){
			if(function_exists('iconv')){
				return iconv("UTF-8","CP1251",$s);
			}
			else
			{
				return $this->UTF8toCP1251($s);
			}
		}
		
		
		/*
		
		    Author: unknow, please tell me who you are!!! Thanks for the two functions below!
		
		*/
		function UTF8toCP1251($s){
            $out = "";
            for ($i=0; $i<strlen($s); $i++)
            {
                    $c1 = substr ($s, $i, 1);
                    $byte1 = ord ($c1);
                    if ($byte1>>5 == 6) // 110x xxxx, 110 prefix for 2 bytes unicode
                    {
                        $i++;
                        $c2 = substr ($s, $i, 1);
                        $byte2 = ord ($c2);
                        $byte1 &= 31; // remove the 3 bit two bytes prefix
                        $byte2 &= 63; // remove the 2 bit trailing byte prefix
                        $byte2 |= (($byte1 & 3) << 6); // last 2 bits of c1 become first 2 of c2
                        $byte1 >>= 2; // c1 shifts 2 to the right
                        $word = ($byte1<<8) + $byte2;
                        if ($word==1025) 
                            $out .= chr(168);                    // ?
                        elseif ($word==1105) 
                            $out .= chr(184);                // ?
                        elseif ($word>=0x0410 && $word<=0x044F) 
                            $out .= chr($word-848); // ???
                        else
                        { 
                             $a = dechex($byte1);
                             $a = str_pad($a, 2, "0", STR_PAD_LEFT);
                             $b = dechex($byte2);
                             $b = str_pad($b, 2, "0", STR_PAD_LEFT);
                             $out .= "&#x".$a.$b.";";
                        }
                }
                else
                {
                    $out .= $c1;
                }
            }
            return $out;  
        }
    
        function CP1251toUTF8($string){
          $out = '';
          for ($i = 0; $i<strlen($string); ++$i){
           $ch = ord($string{$i});
           if ($ch < 0x80) $out .= chr($ch);
           else
             if ($ch >= 0xC0)
               if ($ch < 0xF0)
                     $out .= "\xD0".chr(0x90 + $ch - 0xC0); // &#1040;-&#1071;, &#1072;-&#1087; (A-YA, a-p)
               else $out .= "\xD1".chr(0x80 + $ch - 0xF0); // &#1088;-&#1103; (r-ya)
             else
               switch($ch){
                 case 0xA8: $out .= "\xD0\x81"; break; // YO
                 case 0xB8: $out .= "\xD1\x91"; break; // yo
                 // ukrainian
                 case 0xA1: $out .= "\xD0\x8E"; break; // &#1038; (U)
                 case 0xA2: $out .= "\xD1\x9E"; break; // &#1118; (u)
                 case 0xAA: $out .= "\xD0\x84"; break; // &#1028; (e)
                 case 0xAF: $out .= "\xD0\x87"; break; // &#1031; (I..)
                 case 0xB2: $out .= "\xD0\x86"; break; // I (I)
                 case 0xB3: $out .= "\xD1\x96"; break; // i (i)
                 case 0xBA: $out .= "\xD1\x94"; break; // &#1108; (e)
                 case 0xBF: $out .= "\xD1\x97"; break; // &#1111; (i..)
                 // chuvashian
                 case 0x8C: $out .= "\xD3\x90"; break; // &#1232; (A)
                 case 0x8D: $out .= "\xD3\x96"; break; // &#1238; (E)
                 case 0x8E: $out .= "\xD2\xAA"; break; // &#1194; (SCH)
                 case 0x8F: $out .= "\xD3\xB2"; break; // &#1266; (U)
                 case 0x9C: $out .= "\xD3\x91"; break; // &#1233; (a)
                 case 0x9D: $out .= "\xD3\x97"; break; // &#1239; (e)
                 case 0x9E: $out .= "\xD2\xAB"; break; // &#1195; (sch)
                 case 0x9F: $out .= "\xD3\xB3"; break; // &#1267; (u)
               }
          }
          return $out;
        }
	}
?>