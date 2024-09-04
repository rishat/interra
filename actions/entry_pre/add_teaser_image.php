<?
    //============= UPLOAD ======================================
    ## CASE file upload ##
    if(ALLOW_TEASER_IMAGE){
        //prepare files
        if($_FILES['myFile']['tmp_name']){
            //init library
            require("lib/uploader.class.php");			# image uploader

            //create uploader
            $myUpload = new uploader();

            //check for existence of root path
            $uploadPath = "files" . DIRECTORY_SEPARATOR . ENTRY_ID . DIRECTORY_SEPARATOR;
            $myUpload->preparePath($uploadPath);

            //check if file with this name exists
            $myUpload->checkImage($uploadPath);

            //check file to be uploaded is a jpeg, and create a thumbnail for it
            $myUpload->copyFile($_FILES['myFile']['tmp_name'],$uploadPath."image.jpg");
            $myUpload->copyJPEG($uploadPath."image.jpg",$uploadPath."thumb.jpg",TI_WIDTH,TI_HEIGHT);

            //chmod
            @chmod($uploadPath."thumb.jpg", 0777);
            @chmod($uploadPath."image.jpg", 0777);

            $imageTag = 1;
        }else{
            if($_POST['image']){
                $imageTag = 1;
            }else{
                $imageTag = 0;
            }
        } //end file prepare
    }else{
        $imageTag = 0;
    }
    ## end CASE file upload ##
    //============= END UPLOAD ==================================
?>