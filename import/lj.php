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
	    -- from livejournal.com
	
	  Version 1.1
	  Author: Kulikov Alexey
	  Docu: http://wiki.inses.ru/InTerra/using/import/lj/
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
	
	//require LJ libraries
	require(ROOT_DIR . 'lib/xmlrpc/xmlrpc.inc');
	require(ROOT_DIR . 'lib/lj.class.php');
		
	//login
	$myLJ = new lj(LJ_USER,LJ_PASS);
	
	if($_GET['process']){
		//open database connection
		
		require(ROOT_DIR . "lib/pear/DB.php");
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
		$db->query("ALTER TABLE `".PREFIX."entry` ADD UNIQUE (`ljurl`)"); //update table to exclude possibility of double post import
		
		//transliterate
		//create url transformation handler
		require(ROOT_DIR . "lib/translit/php/translit.php");
		$myTrans = new Translit();
		
		
		//get entries
		$done = array();
		$counter = (int)$_GET['counter'];
		$type = (int)$_GET['type'];
		$entries = $myLJ->getPosts($_GET['stamp'],10);
		$finished = false;
        
        //process the lot
		if(!empty($entries)){
			foreach($entries as $entry){
				
			    //check that only the desired posts are imported
			    if($type == 1){
			        if($entry['security'] == "private"){
			            continue;   
			        }
			    }elseif($type == 0){
			        if($entry['security'] == "usemask" or $entry['security'] == "private"){
			            continue;   
			        }  
			    }
			    
				$ljURL = $entry['itemid']*256 + $entry['anum'];
				$stamp = $entry['eventtime'];
				
				//check if subject is set
				if(empty($entry['subject'])){
					$entry['subject'] = "Нет Темы";	
				}
				
				//create URL cache
				$urlCache = $myTrans->UrlTranslit($entry['subject']);
				
				//bogus check
				if(empty($urlCache)){
					$urlCache = "net_temy";
				}
				
				//check if this cache is occupied
				$day 			    = date("d",$entry['eventtime']);
				$month 			    = date("m",$entry['eventtime']);
				$year 			    = date("Y",$entry['eventtime']);
				$dayStartTimeStamp 	= mktime(0,0,0,$month,$day,$year);
				$dayEndTimeStamp 	= mktime(23,59,59,$month,$day,$year);
				
				if($db->getOne("SELECT entryid FROM ".PREFIX."entry WHERE intime >= '$dayStartTimeStamp' AND intime <= '$dayEndTimeStamp' AND urlcache = '$urlCache'")){
					$urlCache = $urlCache . "_" . $ljURL; //darn, just append the entryid to it, it will then be for sure unique
				}
				
				//process LJ names
				$entry['event'] = preg_replace("/<lj user=\"?(.*?)\"?>/si","<nobr><a href=\"http://www.livejournal.com/users/\\1/info/\" target=\"_blank\"><img src=\"".SERVER_ROOT . TEMPLATE_ROOT . "images/user.gif\" alt=\"[info]\" style=\"border: 0pt none ; vertical-align: center;\" /></a><a href=\"http://www.livejournal.com/users/\\1/\" target=\"_blank\"><b>\\1</b></a></nobr>",$entry['event']);	
				
				//process cuts
				$entry['event'] = str_replace('<lj-cut>','<lj-cut text="LJ CUT">',$entry['event']);
				$entry['event'] = str_replace('<lj-cut','<a name="cut1"></a><lj-cut',$entry['event']);
				
				if(strpos($entry['event'],'lj-cut') and !strpos($entry['event'],'/lj-cut')){
				    $entry['event'] = $entry['event'].'</lj-cut>';
				}
				
				//process database insert
				$res = $db->query("INSERT INTO ".PREFIX."entry SET
									subject 	  = '".addslashes($entry['subject'])."',
									content 	  = '<#<p>".addslashes($entry['event'])."</p>#>',
									content_p 	  = '<p>".addslashes($entry['event'])."</p>',
									intime		  = '".$entry['eventtime']."',
									ljid		  = ".$entry['itemid'].",
									ljurl		  = ".$ljURL.",
									urlcache	  = '".$urlCache."',
									comments	  = '".$entry['comments']."'");	

				//just to make sure mon
				if(!DB::isError($res)){
					//prepare reports
					$counter++;
					$done[] = $counter . ". Done with -- " . $entry['subject'] . " @ " . date("d/m/Y H:i:s",$entry['eventtime']);
				}
			}
		}else{
			//mark that the processing of LJ content is done
			$finished = true;
		}
		
		//sentinel
		$validUser = true;
	}else{
		
		//sentinel
		$validUser = $myLJ->login();	
		$finished = true;
	}
?>
<html>
<head>
<?
	if(!$finished){
?>
		<meta http-equiv="Refresh" content="2; URL=<?=SERVER_ROOT?>import/lj.php?type=<?=$type?>&stamp=<?=$entry['eventtime']?>&counter=<?=$counter?>&process=true">
<?
	}
?>
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
		if($_GET['process']){
			if(!$finished){
				echo implode($done,"<br />");
				echo "<br /><br />still working... please wait";
			}else{
				echo "<b>done!</b> imported " . $counter . " posts!!!<br /> <a href=\"../\">Go to blog...</a>";	
			}
		}else{
	?>
		<p>This script will import ALL of the posts found in the livejournal of user <font color="#0000aa"><b><?=LJ_USER?></b></font>.</p>
		<p>The import procedure will work in packets of 10! This may take several minutes, please be patient!</p>
	<?
			if($validUser){
	?>
				<form action="lj.php" method="GET">
					<b>Import: </b>
					<select name="type">
						<option value="0">Only Open To All (no friends only and private posts)</option>
						<option value="1">All Except Private Posts</option>
						<option value="2">All Posts</option>
					</select>
					<input type="submit" name="process" value="Go" />
				</form>
	<?
			}else{
	?>
				<p><b><font color="#ff0000">The supplied LJ username and password do not verify with the LJ server! Please correct this in your config.inc.php file!</font></b></p>
	<?
			}
		}
	?>
	</div>
</body>