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
	
	/****************************
	  Import Tools
	    -- from e2
	
	  Version 1.0
	  Author: Kulikov Alexey
	  Docu: http://wiki.inses.ru/InTerra/using/import/e2/
	*****************************/
	
	error_reporting(E_ALL ^ E_NOTICE);
	session_start();
	
	//tool bogus check
	if(!$_SESSION['admin']){
		echo "Please login as admin first!";
		exit;	
	}

	//let's dance
	define('ROOT_DIR', substr(dirname(__FILE__),0,-6));
	require(ROOT_DIR . 'common/config.inc.php');
	
	//dance baby yeah
	if($_POST){
		require(ROOT_DIR . "lib/pear/DB.php");
		
		//open database connection to e2 (if any)
		$db_e2 = DB::connect(array(
	    	'phptype'  => 'mysql',
	    	'dbsyntax' => false,
	    	'protocol' => false,
	    	'hostspec' => $_POST['host'],
	    	'database' => $_POST['name'],
	    	'username' => $_POST['user'],
	    	'password' => $_POST['pass']
		));
		
		if(!DB::isError($db_e2)){
			$db_e2->setFetchMode(DB_FETCHMODE_ASSOC);		//set associative set retrieval
		    $db_e2->setOption('optimize', 'portability');	//set optimizer
		
    		//open database connection to InTerra
    		$db = DB::connect(array(
    	    	'phptype'  => 'mysql',
    	    	'dbsyntax' => false,
    	    	'protocol' => false,
    	    	'hostspec' => DB_HOST,
    	    	'database' => DB_NAME,
    	    	'username' => DB_USER,
    	    	'password' => DB_PASS
    		));
    		
    		$db->setFetchMode(DB_FETCHMODE_ASSOC);		//set associative set retrieval
    		$db->setOption('optimize', 'portability');	//set optimizer
    		
    		//transliterate
    		//create url transformation handler
    		require(ROOT_DIR . "lib/translit/php/translit.php");
    		$myTrans = new Translit();
    		
    		//get some libraries
    		require(ROOT_DIR . "lib/nwacko/classes/macroProcessor.class.php");
    		$parser = &new macroProcessor();
    		
    		//get keywords
    		$keywords = $db_e2->getAll("SELECT * FROM e2keywords");
    		foreach($keywords as $keyword){
    		    $db->query("INSERT INTO ".PREFIX."keyword SET word = '".$keyword['Keyword']."', unixword = '".$myTrans->UrlTranslit($keyword['Keyword'])."'");
    		}
    		
    		//get entries
    		$done = array();
    		$counter = (int)$_GET['counter'];
    		$entries = $db_e2->getAll("SELECT * FROM e2notes");
    
    		//process the lot
    		if(!empty($entries)){
    			foreach($entries as $entry){
    				//get ID of new post
    			    $newPostID = (int)$db->getOne("SELECT max(entryid) FROM ".PREFIX."entry")+1;
    			    
    			    //***************** GET COMMENTS **********************//
    			    $comments = $db_e2->getAll("SELECT * FROM e2comments WHERE ForID = '".$entry['ID']."' ORDER BY Stamp ASC");
    			   
    			    $order = 0;
    			    foreach($comments as $comment){
    			         $order++;
    			         
    			         //get comment id
    			         $newComID = (int)$db->getOne("SELECT max(commentid) FROM ".PREFIX."comment")+1;
    			         
    			         //actual comment
    			         $db->query("INSERT INTO ".PREFIX."comment SET
    			                                         commentid = '".$newComID."',
    			                                         content = '".$parser->process($comment['Text'])."',
    			                                         senderName = '".$comment['AuthorName']."',
    			                                         senderMail = '".$comment['AuthorEmail']."',
    			                                         entryid = '".$newPostID."',
    			                                         intime = '".$comment['Stamp']."',
    			                                         notify = '1',
    			                                         sortorder = '".$order."',
    			                                         level = '0',
    			                                         parent = '0'");   
    			         
    			         //admin reply if any
    			         if(!empty($comment['Reply'])){
    			              $order++;
    			              $db->query("INSERT INTO ".PREFIX."comment SET
    			                                         content = '".$parser->process($comment['Reply'])."',
    			                                         senderName = '".BLOGMASTER."',
    			                                         senderMail = '".BLOGMASTER."',
    			                                         entryid = '".$newPostID."',
    			                                         intime = '".$comment['Stamp']."',
    			                                         notify = '0',
    			                                         sortorder = '".$order."',
    			                                         level = '1',
    			                                         parent = '".$newComID."'");   
    			         }
    			         
    			    }
    			    //***************** END GET COMMENTS ******************//
    			    
    			    
    			    
    			    //***************** GET KEYWORDS **********************//
    			    $keys = $db_e2->getAll("SELECT * FROM e2noteskeywords LEFT JOIN e2keywords ON (e2noteskeywords.KeywordID = e2keywords.ID) WHERE NoteID = '".$entry['ID']."'");
    			    $entryKeys = array();
    			    foreach($keys as $key){
    			        //figure out the wordid
    			        $wordID = $db->getOne("SELECT wordid FROM ".PREFIX."keyword WHERE word = '".$key['Keyword']."'");
    			        $db->query("INSERT INTO ".PREFIX."keywords SET entryid = '".$newPostID."', wordid = '".$wordID."'");
    			        $entryKeys[] = $key['Keyword']."|".$myTrans->UrlTranslit($key['Keyword']);
    			    }
    			    //***************** END GET KEYWORDS ******************//
    			    
    				
    			    
    				//***************** INSERT POST ***********************//
    				//check if subject is set
    				if(empty($entry['Title'])){
    					$entry['Title'] = "Нет Темы";	
    				}
    				
    				//create URL cache
    				$urlCache = $myTrans->UrlTranslit($entry['Title']);
    				
    				//bogus check
    				if(empty($urlCache)){
    					$urlCache = "net_temy";
    				}
    				
    				//check if this cache is occupied
    				$day 			    = date("d",$entry['Stamp']);
    				$month 			    = date("m",$entry['Stamp']);
    				$year 			    = date("Y",$entry['Stamp']);
    				$dayStartTimeStamp 	= mktime(0,0,0,$month,$day,$year);
    				$dayEndTimeStamp 	= mktime(23,59,59,$month,$day,$year);
    				
    				if($db->getOne("SELECT entryid FROM ".PREFIX."entry WHERE intime >= '$dayStartTimeStamp' AND intime <= '$dayEndTimeStamp' AND urlcache = '$urlCache'")){
    					$urlCache = $urlCache . "_" . $newPostID; //darn, just append the entryid to it, it will then be for sure unique
    				}
    				
    				//parse content with wiki processor
    				$parsedContent = $parser->process($entry["Text"]);
    				
    				//process database insert
    				$res = $db->query("INSERT INTO ".PREFIX."entry SET
    				                    entryid       = '".$newPostID."',
    									subject 	  = '".addslashes($entry['Title'])."',
    									content 	  = '".addslashes($entry['Text'])."',
    									content_p 	  = '".$parsedContent."',
    									intime		  = '".$entry['Stamp']."',
    									ljid		  = null,
    									ljurl		  = null,
    									urlcache	  = '".$urlCache."',
    									commentCount  = '".$order."',
    									keywordcache  = '".implode(",",$entryKeys)."',
    									comments	  = '".$entry['IsCommentable']."'");	
    				
    				//just to make sure mon
    				if(!DB::isError($res)){
    					//prepare reports
    					$counter++;
    					$done[] = $counter . ". Done with -- " . $entry['Title'] . " @ " . date("d/m/Y H:i:s",$entry['Stamp']);
    				}
    				//******************** END INSERT POST *********************//
    			}
    		}
		}else{
		    $error = true;   
		}
	}
?>
<html>
<head>
<style>
	body, td {  font-family: Tahoma; font-size: 11px; color: #000000; padding: 0; margin: 0; text-align: center;}
	
	#content { 	width: 400px;
				
				margin: 100px auto; 
				padding: 15px;
				color: #aa0000;
				border: solid 1px #ccc;
				background: #eee;}
</style>
</head>
<body>
	<div id="content">
	<?
		if($_POST and !$error){
				echo implode($done,"<br />");
				
				echo "<br /><br /><b>done!</b> imported " . $counter . " posts!!!<br /> <a href=\"../\">Go to blog...</a>";	
		}else{
		    if($error){
		      echo "<p><font color=\"#ff0000\">Please check the database connection details, as they do not work!</font></p>";   
		    }
	?>
		<p>This script will import ALL of the posts found in the e2 blog on this server.</p>
				<form action="e2.php" method="POST">
				    <table align=center>
				    <tr><td align=right>
					<b>e2 DB host:&nbsp;</b></td><td>
					<input type="text" name="host" value="localhost" />
					</td></tr><tr><td align=right>
					<b>e2 DB name:&nbsp;</b></td><td>
					<input type="text" name="name" value="<?=$_POST['name']?>" />
					</td></tr><tr><td align=right>
					<b>e2 DB user:&nbsp;</b></td><td>
					<input type="text" name="user" value="<?=$_POST['user']?>" />
					</td></tr><tr><td align=right>
					<b>e2 DB pass:&nbsp;</b></td><td>
					<input type="text" name="pass" value="<?=$_POST['pass']?>" />
					</td></tr></table>
					<input type="submit" name="process" value="Go" />
				</form>	
	<?			
		}
	?>
	</div>
</body>