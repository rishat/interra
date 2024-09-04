<?
    //wiki process content (only if not in WYSIWYG mode)
    if(!WYSIWYG){
        $parsedContent = $parser->process($_POST["content"]);
    }else{
        $parsedContent = $_POST["content"];
    }
?>