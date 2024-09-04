function addValue(){
    if(document.getElementById('keywords').value != '' && document.getElementById('keywordsDropdown').value != ''){
        document.getElementById('keywords').value = document.getElementById('keywords').value + ", " + document.getElementById('keywordsDropdown').value;
    }else if(document.getElementById('keywordsDropdown').value != ''){
        document.getElementById('keywords').value = document.getElementById('keywordsDropdown').value;
    }
    document.getElementById('keywordsDropdown').options[document.getElementById('keywordsDropdown').selectedIndex] = null;
        
    if(document.getElementById('keywordsDropdown').options.length == 0){
        document.getElementById('keywordsDropdown').disabled = true;
        document.getElementById('keywordsDropdownButton').disabled = true;
    }
}

function checkNewSection(){
    if(document.getElementById('section').value == 'new'){
        document.getElementById('newSection').style.display="";
        document.getElementById('replicate').style.display="none";
    }else{
        if(document.getElementById('section').value > 0){
            document.getElementById('replicate').style.display="";
        }else{
            document.getElementById('replicate').style.display="none";
        }
        document.getElementById('newSection').style.display="none";
    }
}

function iFrameHeight(iframe, rows) {
    var height;
    height = rows * 18 + 73;
    
    if(document.getElementById && !(document.all)) {
        document.getElementById(iframe).style.height = height+'px';
    }else if(document.all) {
        document.frames(iframe).style.height = height+'px';
    }
}

function str_replace(search,Êreplace,Êsubject){
    alert(subject);
    return subject.split(search).join(replace);
}

function addImg(mes){
    document.getElementById('postText').value = document.getElementById('postText').value + '\n' + mes;
    return false;
}

function addThumb(mes){
    var fullImage = mes.split('THUMB_').join('');
    document.getElementById('postText').value = document.getElementById('postText').value + '\n' + '<# <div align="center"><a href="'+fullImage+'" rel="lightbox"><img src="'+mes+'" class="thumb_image" border="0" /></a></div> #>';
    return false;
}

function saveDraft(path){
    
    var calledTime = new Date();
    
    if(calledTime - loadedTime > 5000){   
        (new AjaxRequest).send({
            url      : path+"ajax/",
            data    : {
              ajaxevent:  'saveDraft',
              subject: document.getElementById('subject').value,
              content: document.getElementById('postText').value
            },
            
            onSuccess  : function(data, info)
            {
                unhide('chernovik');
                document.getElementById('cherDate').innerHTML = data.saved;
            }
        });
        
        loadedTime = new Date();
    }
}

function checkDraft(path){
    (new AjaxRequest).send({
        url      : path+"ajax/",
        data    : {
          ajaxevent:  'checkDraft'
        },
        
        onSuccess  : function(data, info)
        {
            if(data.content){
                if(confirm(restoreMessage)){
                    document.getElementById('subject').value  = unescape(data.subject);
                    document.getElementById('postText').value = unescape(data.content);
                }
            }
        }
    });
}