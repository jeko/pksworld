
function loadSideBoxContent(obj, loadedFunc)
{
	if (loadedFunc == undefined) loadedFunc = function() { sideBoxLoaded("sideBoxContent"); };
	// new Effect.Opacity("sideBoxContent", {duration:0.1, from: 1, to: .90});
	var href = null;
	if (typeof obj == 'string') {
		href = obj;
	}
	else {
		href = obj.href;
		$$('.listSelected').invoke('removeClassName', 'listSelected');
		obj.addClassName('listSelected');
	}
	ajaxLoad(href, 'get', {ajax: 1}, "sideBoxContent", loadedFunc);
}

function sideBoxLoaded(targetBoxId)
{
	// new Effect.Opacity(targetBoxId, {duration:0.2, from: 0.75, to: 1, queue: 'end'});
}

function loadBoxContent(obj, loadInMainBox)
{
	var href = null, targetBoxId = null, callBackFunc = null;
	if (loadInMainBox == undefined || loadInMainBox == true) {
		targetBoxId = "mainBoxContent";
		callBackFunc = boxLoaded;
	}
	else {
		targetBoxId = "ajaxLoadedContent";
		callBackFunc = showShadowBoxContent;
		$("sideBoxContent").innerHTML = "&nbsp;";
	}
	if (typeof obj == 'string') {
		href = obj;
	}
	else {
		href = obj.href;
	}
	if (href.charAt(obj.href.length-1) != '#') {
		// new Effect.Opacity(targetBoxId, {duration:0.1, from: 1, to: .90});
		ajaxLoad(href, 'get', {ajax: 1}, targetBoxId, callBackFunc);
	}
}

function showShadowBoxContent()
{
	var ajaxContent = $("ajaxLoadedContent").innerHTML;
	$("ajaxLoadedContent").innerHTML = '&nbsp;';
    Shadowbox.open({
        content:    ajaxContent,
        player:     "html",
        height:     500,
        width:      800
    });
}

function boxLoaded()
{
	// new Effect.Opacity(targetBoxId, {duration:0.2, from: 0.75, to: 1, queue: 'end'});
}