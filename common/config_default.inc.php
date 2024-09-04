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
	*****************************************************************************/

	/***
	 This is the main configuration file, which shall be used to tune your blog,
	 please follow comments for settings
	***/

	/***
	 database connection details
	***/
	define('DB_HOST','{$db.host}'); 	//database host
	define('DB_NAME','{$db.name}');		//database name
	define('DB_USER','{$db.user}');		//database user
	define('DB_PASS','{$db.pass}');		//database password


	/***
	 TEMPLATE_ROOT -- 	path to the template set of your blog, this
	 					path must be relative to SERVER_ROOT, typically
	 					this setting must not be changed
	 ***/
	define('TEMPLATE_ROOT','templates/main/');

	/***
	 SERVER_ROOT --		absolute WWW path to the root of your application
	 					ending with a slash, eg. http://www.blog.com/
	***/
	define('SERVER_ROOT','{$root}');

	/***
	 SMARTY Specific Settings
	***/
	define('SMARTY_ALLOW_CACHE',false);		// cache pages?
	define('SMARTY_CACHE_TIME',43200);		// cache by default for 10 minutes
	define('SMARTY_FORCE_FLUSH',86400);		// folce full cache flush once every xxxxx seconds
	define('SMARTY_COMPILE_CHECK',true);	// check templates if they need recompilation
	define('SMARTY_DEBUG',false);			// show smarty debug console
	define('SMARTY_SUBDIRS',true);			// is smarty allowed to create subdirs?

	/***
	 BLOGMASTER_P is the blog administration password
	 BLOGMASTER_U is the blog administration login name
	***/
	define('BLOGMASTER_P','{$admin.pass}');
	define('BLOGMASTER_U','{$admin.user}');

	/***
	 MAILBOT is the return path for the e-mail bot who sends out mail notifications
	***/
	define('MAILBOT','{$mailbot}');

	/***
	 BLOGMASTER is the e-mail address of the blog admin
	***/
	define('BLOGMASTER','{$admin.mail}');

	/***
	 paranoid users can disable e-mail notifications
	 ENABLE_BMMAIL - notify blog master of new comments
	 ENABLE_UMAIL - notify users of blog master's answers
	***/
	define('ENABLE_BMMAIL',true);
	define('ENABLE_UMAIL',true);

	/***
	 In case you are really keen on WYSIWYG text editors then
	 you may enable it here and write new posts from a rich
	 text interface. Please keep in mind, that wiki is more
	 welcome ;)
	***/
	define('WYSIWYG',false);

	/***
	 RSS_HEADER header of the RSS feed
	 RSS_DESC description of the RSS feed
	***/
	define('RSS_HEADER','{$rss.header}');
	define('RSS_DESC','{$rss.desc}');

	/***
	 ENABLE_PAGERS - true if pagers are enables, otherwise false, this operation costs 1 database query
	***/
	define('ENABLE_PAGERS',true);

	/***
	 ANTISPAM - true in case comments can be posted only in case the antispam challenge is filled out
	***/
	define('ANTISPAM',false);

	/***
	 PER_PAGE is the number of posts shown per page in section and last enttry views
	***/
	define('PER_PAGE',15);

	/***
	 INDIRECT_PATH is the path to your link tracker, if any, leave commented out if not used
	***/
	//define('INDIRECT_PATH','http://wiki.essentialmind.com/InDirect/');

	/***
	 ALLOW_FILES -- allow file uploads
	***/
	define('ALLOW_FILES',true);

	/***
	 ALLOW_KEYWORDS -- whether keywords are allowed
	***/
	define('ALLOW_KEYWORDS',true);

	/***
	 ALLOW_NICEURLS -- nice URLs will be derived from post headings. This operation costs 1 DB query per page request
	 ALLOW_HANDINPUT -- if true, then you may override the automatic URL convertion with a per-hand defined url name
	***/
	define('ALLOW_NICEURLS', true);
	define('ALLOW_HANDINPUT',false);

	/***
	 ALLOW_TEASER_IMAGE -- allow teaser image for posts, this will links a thumbnail to every post, makes things look sexy
	 sometimes. Two additional settings (if true) are TI_WIDTH and TI_HEIGTH -- these are the resize parameters for the
	 teaser image
	***/
	define('ALLOW_TEASER_IMAGE',false);
	define('TI_WIDTH',125);
	define('TI_HEIGHT',100);
	define('USE_MAGICK',false);			//in case ImageMagick is installed on the server, one can use it instead of GD
	define('MAGICK_PATH',null);			//absolute path to ImageMagick on server

	/***
	 ENABLE_LJ -- is this an LJ syndicated account?
	***/
	define('ENABLE_LJ',{$lj.enable});
	define('LJ_USER','{$lj.user}');
	define('LJ_PASS','{$lj.pass}');

	/***
	 ENABLE_TICKER_C -- when posting to a category, allow the user to choose whether the post will be visible on the
	 main ticker (page) and the main RSS
	***/
	define('ENABLE_TICKER_C',false);
	
	/***
	 COMMENT_DAYS -- the add new comment feature will be automatically disabled for posts older than N days (default 30)
	***/
	define('COMMENT_DAYS',30);
	
	/***
	 COMMENT_TREE -- Allow users to reply to each other's comments?
	***/
	define('COMMENT_TREE',true);
	
	/***
	 COMMENT_TREE_DEPTH -- Maximum depth the tree can go to
	***/
	define('COMMENT_TREE_DEPTH',7);


    /***
     Primary Lang (en or empty)
     ***/
    define('PLANG','{$lang}');
    

	/********** THINGS YOU BETTER NOT CHANGE ARE BELOW *******************************/

	/***
	 CHARSET -- default charset used to encode all text and e-mail, probably no need to change this =)
	***/
	define('CHARSET','windows-1251');

	/***
     SESSION_EXPIRE -- time for the admin session to remain valid, default is two weeks (in seconds)
	***/
	define('SESSION_EXPIRE',1209600);

	/***
     MY_LOCALE -- locale to use for date formation
	***/
	define('MY_LOCALE','{if $lang == 'en'}{else}ru_RU.CP1251{/if}');

	/***
	 NOW -- current server time, can be shifted with an offset
	***/
	define('NOW', time());

	/***
	 ROOT_DIR -- full path to the script
	***/
	define('ROOT_DIR', substr(dirname(__FILE__),0,-6));
	
	/***
	  VERSION -- do not change, needed for auto-update
	 ***/   
	define('VERSION',180);
	
	/***
	  PREFIX -- db table prefix
	 ***/
	define('PREFIX','{$db.prefix}');
?>