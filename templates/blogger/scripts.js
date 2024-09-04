function unhide(elementName){
	document.getElementById(elementName).style.display="";
}

function hide(elementName){
	document.getElementById(elementName).style.display="none";
}

function confirmLink(theLink, theSqlQuery)
{
    var is_confirmed = confirm(theSqlQuery);    
    return is_confirmed;
}