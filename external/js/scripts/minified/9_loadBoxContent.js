
function loadSideBoxContent(obj,loadedFunc)
{if(loadedFunc==undefined)loadedFunc=function(){sideBoxLoaded("sideBoxContent");};var href=null;if(typeof obj=='string'){href=obj;}
else{href=obj.href;$$('.listSelected').invoke('removeClassName','listSelected');obj.addClassName('listSelected');}
ajaxLoad(href,'get',{ajax:1},"sideBoxContent",loadedFunc);}
function sideBoxLoaded(targetBoxId)
{}
function loadBoxContent(obj,loadInMainBox)
{var href=null,targetBoxId=null,callBackFunc=null;if(loadInMainBox==undefined||loadInMainBox==true){targetBoxId="mainBoxContent";callBackFunc=boxLoaded;}
else{targetBoxId="ajaxLoadedContent";callBackFunc=showShadowBoxContent;$("sideBoxContent").innerHTML="&nbsp;";}
if(typeof obj=='string'){href=obj;}
else{href=obj.href;}
if(href.charAt(obj.href.length-1)!='#'){ajaxLoad(href,'get',{ajax:1},targetBoxId,callBackFunc);}}
function showShadowBoxContent()
{var ajaxContent=$("ajaxLoadedContent").innerHTML;$("ajaxLoadedContent").innerHTML='&nbsp;';Shadowbox.open({content:ajaxContent,player:"html",height:500,width:800});}
function boxLoaded()
{}