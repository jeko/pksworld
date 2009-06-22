
function ajaxLoad(url, method, parameters, targetBoxId, callBackFunctionWhenFinished)
{
	enableLoadingPicture();
	if (targetBoxId == undefined || targetBoxId == false) targetBoxId = "ajaxLoadedContent";
	new Ajax.Updater(targetBoxId, url,
			{
			method: method,
			parameters: parameters,
			onComplete: function() { removeWhiteSpaces(targetBoxId); setTimeout(callBackFunctionWhenFinished, 10); disableLoadingPicture(); },
			onFailure: function() { alert("Ajax call failed."); },
			evalScripts: true
			}
	);
}

function removeWhiteSpaces(targetBoxId)
{
	var box = $(targetBoxId);
	for (var j=box.childNodes.length-1;j>-1;j--)
	{
		if (box.childNodes[j].nodeType != 1) box.removeChild(box.childNodes[j]);
	}
}

function ajaxLoadPeriodically(url, method, parameters, targetBoxId, callBackFunctionWhenFinished, period)
{
	enableLoadingPicture();
	if (targetBoxId == undefined || targetBoxId == false) targetBoxId = "ajaxLoadedContent";
	new Ajax.PeriodicalUpdater(targetBoxId, url,
			{
			method: method,
			parameters: parameters,
			frequency: period,
			onComplete: function() { setTimeout(callBackFunctionWhenFinished, 1); disableLoadingPicture(); },
			onFailure: function() { alert("Ajax call failed.") },
			evalScripts: true
			}
	);
}

function enableLoadingPicture()
{
	$("loadingPic").setStyle({visibility: 'visible'});
	new Effect.Opacity("loadingPic", {from:1, to:1, duration:0.1});
}

function disableLoadingPicture()
{
	new Effect.Opacity("loadingPic", {from:1, to:0, duration:.3, queue:'end'});
}