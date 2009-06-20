
function preloadImages(arr, loadingDisplayId, finishedLinkId, progressBarId)
{
    if(typeof arr != 'object' || !arr.length) return;
    if (loadingDisplayId != false) {
	    $(loadingDisplayId).setStyle({display: 'block'});
	    $(finishedLinkId).setStyle({display: 'none'});
	    new Effect.Opacity(loadingDisplayId, {from:0, to:1})
	    $(progressBarId).setStyle({width: '0%'});
    }
    
    ok = not_ok = size = 0;
    var img = new Array();
    var total = arr.length;
    for(var i = 0; i < arr.length;i++)
    {
    	if (arr[i] == false) {
    		total--;
    		continue;
    	}
        img[i] = new Image();
        img[i].onload = function() {
            ok++;
            if (loadingDisplayId != false) {
	            var width = (ok + not_ok) / total * 100;
	            $(progressBarId).setStyle({width: width+"%"});
	            if(ok + not_ok == total) {
	            	finishImagePreload(loadingDisplayId, finishedLinkId);
	            }
            }
        }
        img[i].onerror = function() {
            not_ok++;
            if (loadingDisplayId != false) {
	            var width = (ok + not_ok) / total * 100;
	            $(progressBarId).setStyle({width: width+"%"});
	            if(ok + not_ok == total) {
	            	finishImagePreload(loadingDisplayId, finishedLinkId);
	            }
            }
        }
        img[i].src = arr[i];
        if( document.all && img[i].complete) img[i].onload();
    }
}

function finishImagePreload(loadingDisplayId, finishedLinkId)
{
    new Effect.Fade(loadingDisplayId);
    $(finishedLinkId).setStyle({display: 'block'});
    window.location.href = "/world/";
}