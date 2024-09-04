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
	
	require_once 'lib/ajax/ajax.class.php';
    
    //pack the library with results and send'em to the client
    $R = new AjaxResponse;
    $R->set('ajax_status',true);
	
	
    switch($_REQUEST['ajaxevent']){
        case 'saveDraft':   {
                                if(!$eid = $db->getOne("SELECT entryid FROM ".PREFIX."entry WHERE entryid = '999999' AND urlcache = 'AJAX DRAFT'")){
                                    $db->query("INSERT INTO ".PREFIX."entry SET entryid = '999999', catid = 0, format = '0', urlcache = 'AJAX DRAFT', subject = '".$_REQUEST['subject']."', content = '".$_REQUEST['content']."'");
                                }else{
                                    $db->query("UPDATE ".PREFIX."entry SET subject = '".$_REQUEST['subject']."', content = '".$_REQUEST['content']."' WHERE entryid = $eid");
                                }
                                
                                $R->set('saved',date('Y-m-d H:i:s'));
                                break;
                            }
                            
        case 'checkDraft':  {
                                if($eid = $db->getRow("SELECT subject, content FROM ".PREFIX."entry WHERE entryid = '999999' AND urlcache = 'AJAX DRAFT'")){
                                    $R->set('subject',$eid['subject']);
                                    $R->set('content',$eid['content']);
                                }
                                break;
                            }
    }

	
	$R->send();
?>