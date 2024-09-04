<?
    //============== CATEGORIES ================================
    //section check
    if($_POST['section'] == "new"){

        if(!$_POST['sectionNewUnix']){
            $_POST['sectionNewUnix'] = $_POST['sectionNewName'];
        }

        //get rid of spaces if any
        $_POST['sectionNewUnix'] = $myTrans->UrlTranslit($_POST['sectionNewUnix']);

        //check if this name is already taken...
        if($tempID = $db->getOne("SELECT catid FROM ".PREFIX."category WHERE name = '".$_POST['sectionNewUnix']."'")){
            $catID = $tempID;

        }else{
            $catID = (int)$db->getOne("SELECT max(catid) FROM ".PREFIX."category") + 1;

            //insert new category
            $res = $db->query("INSERT INTO ".PREFIX."category(catid, name, fullName, hidden) VALUES('".$catID."','".$_POST['sectionNewUnix']."','".$_POST['sectionNewName']."','".$_POST['sectionNewHidden']."')");
        }

    }else{
        $catID = $_POST['section'];
    }
    //============= END CATEGORIES ==============================
?>