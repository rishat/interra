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
	*
	*****************************************************************************/

	/***
	 This is -- the heart and brain of this application, it will figure out
	 what portions of code to load from the 'actions' folder, which, in
	 turn are small sniplets that do various stuff with the data found
	 in the database

	 @2Do -- n/a
	 @author -- Kulikov Alexey
	***/
	error_reporting(E_ALL ^ E_NOTICE);	// just making sure there are no idiots around =)

	/***
	 So, do we install? ;)
	***/
	if(!file_exists("common/config.inc.php")){
		require("actions/install.php");
		exit;
	}

	/***
	 Load configuration settings, this is very important!
	***/
	require("common/config.inc.php");
	require(ROOT_DIR."lib/smarty/Smarty.class.php");
	require(ROOT_DIR."common/mySmarty.class.php");





	//session works for all now
	if(ANTISPAM){
        session_start();

        //requesting an image?
        if($_GET['action'] == 'antispam'){
            include("actions/antispam.php");
        }
	}

	/***
	 Now, initialize some mandatory resources, such as the template engine =)
	***/
	$smarty = new mySmarty();
	@setlocale(LC_ALL, MY_LOCALE);

	//session is active for admin only
	if($_COOKIE['adminExpire']){

	    if(!ANTISPAM){ //wicked yeah
            session_start();
        }

		//process autologin
		if(!$_SESSION['admin']){
			$data = explode("%%separator%%",$_COOKIE['adminExpire']);

			//autologin details match
			if($data[0] == md5(BLOGMASTER_U) and $data[1] == md5(BLOGMASTER_P)){
				$_SESSION['admin'] = true;
			}else{	//no match
				setcookie("adminExpire");
			}
		}
	}


	/***
	 Important -- disable caching on case this is a bot, as we do not want
	 to flood the disk with rarely callable pages =)
	***/
	if(SMARTY_ALLOW_CACHE and stristr($_SERVER['HTTP_USER_AGENT'], "Mozilla") === false){
		//also check if a forced cache clear is required
		$stamp = @(int)file_get_contents("cache/cacheStamp.txt");

		if($stamp < NOW){
			$smarty->clear_all_cache();
			$stamp = time() + SMARTY_FORCE_FLUSH;
			$file = fopen("cache/cacheStamp.txt","w");
			fwrite($file,$stamp);
			fclose($file);
		}

		//disable cache for the bot
		$smarty->caching = false;
	}



	/***
	 Mod_Rewrite will tranform the defined URL into a reference to index.php
	 with some GET parameters which need to be processed, this is exactly what
	 happens below.
	***/

	//in case $_GET['data'] does not end in a slash, I will add a slash
	$_GET['data'] = strtolower(preg_replace( "/\/+/", "/", $_GET['data'])); // lower all strings and get rid of double slashes

	//get cache id
	$cacheID  = implode("|",array_diff(explode("/",$_GET['data']),array('')));	// create cache ID
	if(empty($cacheID)){
		$cacheID = "______rootpage";
	}

	/***
	 Now, that we have a valid cache ID we can check, whether the desired page
	 is present in local cache, if it is, then we just have to deliever the page
	 and not waste any more system resources, otherwise we need to open a database
	 connection and do a lot of jazz.
	***/
	if($smarty->is_cached('index.htm',$cacheID)){
		$smarty->assign("CACHE",true);	//set a smarty variable to indicate that the page is loaded from cache
		$smarty->display('index.htm',$cacheID);	//send the page to the browser and basta!
	}else{
		//initialize variables
		$breadCrumbs = array(); //this is the breadCrumbs array which stores the location whithin the site

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

		//check if connection has been successful
		if(DB::isError($db)){	//no
			trigger_error("Connection to database failed!",E_USER_ERROR);
		}else{	//yes
			$db->setFetchMode(DB_FETCHMODE_ASSOC);		//set associative set retrieval
			$db->setOption('optimize', 'portability');	//set optimizer
		}

                                                                                                                          
                                                                                                                                     
		//include lang file processor
		if(!defined('PLANG')){ define('PLANG',''); }
		include(ROOT_DIR . "lib/smarty/Config_File.class.php");	# config file reader
		$conf = new Config_File(ROOT_DIR . 'templates/system/');		
		$langData = $conf->get('lang'.PLANG.'.txt','code');


		//check urlcache (v1.1)
		if($_GET['urlcache']){
			define("YEAR",$_GET['year']);
			define("MONTH",$_GET['month']);
			define("DAY",$_GET['day']);
			define("URLCACHE",$_GET['urlcache']);
			define("REDURL",SERVER_ROOT.YEAR."/".MONTH."/".DAY."/".URLCACHE."/");

			$dayStartTimeStamp = mktime(0,0,0,MONTH,DAY,YEAR);
			$dayEndTimeStamp = mktime(23,59,59,MONTH,DAY,YEAR);

			if(!$_GET['id'] = $db->getOne("SELECT entryid FROM ".PREFIX."entry WHERE intime >= '$dayStartTimeStamp' AND intime <= '$dayEndTimeStamp' AND urlcache = '".URLCACHE."'")){
				$_GET['action'] = '404'; //route to 404
			}

		}else{
			define("REDURL",SERVER_ROOT."entry/".(int)$_GET['id']."/");
			
			if($_GET['id'] and !$db->getOne("SELECT entryid FROM ".PREFIX."entry WHERE entryid = '".(int)$_GET['id']."'")){
				$_GET['action'] = '404'; //route to 404
			}
		}


		//now see what we have to do, this parameter is passed from
		//apache's mod_rewrite. By default we will load the index page
		// @update С yeah, i know this is lame and needs a shift towards MVC
		//           yet I am lazy :) the blog was a one day experiment to get
		//           some notes online, it has grown a bit since then
		switch($_GET['action']){

			//the requested page is a selection of some date
			case 'date':	{
								//validate inputs
								$year = (int)$_GET['year'];
								if($year < 1971){
									$year = 1971;
								}elseif($year > 2030){
									$year = 2030;
								}

								$month = (int)$_GET['month'];
								if($month < 1){
									$month = 1;
								}elseif($month > 12){
									$month = 12;
								}

								$day = (int)$_GET['day'];
								if($day < 1){
									$day = 1;
								}elseif($day > 31){
									$day = 31;
								}

								//we have to check what part of the date is available
								switch($_GET['part']){
									//we have only the year, hence show 12 months
									case 'year':	{include("actions/dateYear.php"); break;}

									//we have year and month, hence show all entries for that month
									case 'month':	{include("actions/dateMonth.php"); break;}

									//we have the whole date, hence load all entries for that day
									case 'day':		{include("actions/dateDay.php"); break;}
								}
								break;
							}

			//the requested page is a selection of some keyword
			case 'keyword':	{

								if(!empty($_GET['keyword'])){
									include("actions/keyword.php");
								}else{
									include("actions/keywords.php");
								}
								break;
							}

			//the reuqested page is a selection of some section
			case 'section':	{
			                    
			                    
								include("actions/section.php");
								break;
							}

			//the requested page is a selection of some specific entry
			case 'entry':	{
								include("actions/entry.php");
								break;
							}

			//the requested page is a 404 =))
			case '404':		{
								include("actions/404.php");
								break;
							}

			//we are adding a new entry! HalleLuja
			case 'add':		{
								$smarty->caching = false;
								include("actions/add.php");
								break;
							}

			//we are addding a comment
			case 'addcom':	{
								include("actions/addcomment.php");
								break;
							}

			//we are addding a comment
			case 'dropcom':	{
								include("actions/dropcomment.php");
								break;
							}

			//drop teaser image
			case 'dropim':	{
								include("actions/dropimage.php");
								break;
							}

			//login procedure
			case 'login':	{
								include("actions/login.php");
								break;
							}

			//logout procedure
			case 'logout':	{
								include("actions/logout.php");
								break;
							}

			//edit entry
			case 'edit':	{
								$smarty->caching = false;
								include("actions/add.php");
								break;
							}

			//delete entry
			case 'delete':	{
								include("actions/deleteentry.php");
								break;
							}

			//fetch random post
			case 'random':  {
			                    include("actions/random.php");
			                    break;
			                }

			//clear all cache
			case 'clearc':	{
			                    
			                    //oncly admin can clear cache
			                    if($_SESSION['admin']){
								    $smarty->clear_all_cache();
								    $smarty->clear_compiled_tpl();
								}
								
								
								if(!empty($_SERVER['HTTP_REFERER'])){
									header("Location: " . $_SERVER['HTTP_REFERER']);
								}else{
									header("Location: " . SERVER_ROOT);
								}
								exit;
							}

			//lets search it all ;)
			case 'search':	{
								include("actions/search.php");
								break;
							}
							
			//system configurator
			case 'config':  {
			                    $smarty->caching = false;
			                    include("actions/config.php");
								break;
			                }
			                
			//ajax system call
			case 'ajax':    {
			                    $smarty->caching = false;
			                    include("actions/ajax.php");
			                    exit;
								
			                }

			//the requested page is the root page
			default:		{
								include("actions/rootpage.php");
							}
		}

		//prepare breadCrumbs for perfect addressing
		$tempCrumb = null;
		foreach($breadCrumbs as $key => $val){
			if(is_array($val)){	//this is a version 1.1 fix for more complete breadcrumbs to ensure backwards compatibility
				$tempCrumb .= $val['link'] . "/";
				$val = $val['name'];
			}else{
				$tempCrumb .= $val . "/";
			}
			$breadCrumbs[$key] = array("link"=>$tempCrumb,"name"=>$val);
		}

		//assign breadCrumbs to template
		$smarty->assign('breadCrumbs',$breadCrumbs);

		//now show template
		$smarty->display('index.htm',$cacheID);
	}
?>