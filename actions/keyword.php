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
	* 																			*
	* Read the full license in the license.rtf file distributed along with		*
	* this software package.													*
	*****************************************************************************/
	
	//fix trailing slash problem
	if(eregi("\/$",$_GET['keyword'])){
		$_GET['keyword'] = substr($_GET['keyword'],0,-1);	
	}
	
	
    
    //AND or OR clause?
    if(strpos($_GET['keyword'],' ') !== false or strpos($_GET['keyword'],'|') !== false){    //AND / OR
        if(strpos($_GET['keyword'],' ') !== false){
            $words = explode(' ',$_GET['keyword']);
        }else{
            $words = explode('|',$_GET['keyword']);
        }
        $sql   = array();
        foreach($words as $word){
            $sql[] = "unixword = '".$word."'";
        }
        $wordid = $db->getAll("SELECT wordid AS id, word FROM ".PREFIX."keyword WHERE ".implode(' OR ',$sql));
        
    }else{  // SIMPLE
        $wordid = $db->getAll("SELECT wordid AS id, word FROM ".PREFIX."keyword WHERE unixword = '".$_GET['keyword']."'");
    }
    

    $words = array();
    $names = array();
    $wids  = array();
    foreach($wordid as $word){
        $words[] = '".PREFIX."keywords.wordid = '.(int)$word['id'];
        $names[] = $word['word'];
        $wids[]  = (int)$word['id'];
    }

    //set breadcrumbs
    $breadCrumbs[] = array("link"=>"keyword","name"=>$langData['keywords']);
    $breadCrumbs[] = array("link"=>$_GET['keyword'],"name"=>implode(', ',$names));
    
    //get all the neccessary data
    if(strpos($_GET['keyword'],' ') !== false){ // AND
    
        //complicated :(
        $selected = array();
        foreach($wordid as $word){
            if($selected){
                $addString = "AND entryid IN (".implode(',',$selected).")";
            }else{
                $addString = null;
            }
            
            $tData    = $db->getCol("SELECT entryid FROM ".PREFIX."keywords WHERE wordid = ".$word['id']." ".$addString);
            $selected = array_merge($selected,$tData);
        }
        
        $tData[] = 0;
        $data    = $db->getAll("SELECT subject, comments, commentcount, intime, ".PREFIX."entry.entryid, ".PREFIX."entry.content_p AS content, urlcache AS url FROM
                                ".PREFIX."entry
                                WHERE ".PREFIX."entry.entryid IN (".implode(',',$tData).") ORDER BY intime DESC");
                    
    }else{ // OR / Stable
        
        $tData   = $db->getCol("SELECT DISTINCT entryid FROM ".PREFIX."keywords WHERE wordid IN (".implode(',',$wids).")");
        $tData[] = 0;
        $data    = $db->getAll("SELECT subject, comments, commentcount, intime, ".PREFIX."entry.entryid, ".PREFIX."entry.content_p AS content, urlcache AS url FROM
                                ".PREFIX."entry
                                WHERE entryid IN (".implode(',',$tData).") ORDER BY intime DESC");
                                
    }
    

    if(!$data){
        array_pop($breadCrumbs);
        include("actions/404.php");
    }else{
    
        //preformat urls	
        foreach($data as $key => $entry){
            if(ALLOW_NICEURLS){
                $data[$key]['url'] = SERVER_ROOT . date("Y/m/d/",$entry['intime']).$entry['url'].".html";
            }else{
                $data[$key]['url'] = SERVER_ROOT . "entry/" . $entry['entryid'] . "/";
            }
        }
        
        //pass relevant data to smarty
        $smarty->assign(array(
                            "keyword"	=>	implode(', ',$names),
                            "entries"	=>	$data
                            ));	
                            
        //select smarty template
        $smarty->assign("template","keyword.htm");	
    }
?>