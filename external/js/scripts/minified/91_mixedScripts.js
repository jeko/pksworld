
function deleteMessage(obj)
{new Effect.Opacity(obj,{duration:.5,from:1,to:0})
new Effect.BlindUp(obj,{duration:1});}
function changeContentTitle(titleId,newText)
{if($(titleId).firstChild.nodeValue!=newText){$(titleId).firstChild.nodeValue=newText;}}