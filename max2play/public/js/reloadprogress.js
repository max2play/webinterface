var reloadcount = 0;
function reloadprogress(msgboxid, successurl, reloadWindowWhenFinished){
	$("body").addClass("loading");
	$.ajax({
        url : document.URL,
        type : "get",
        data : "ajax=1&loadprogress=1"
    }).done(function (data) {
    	//reload message box (still in progress) OR reload window (finished) -> variable finished
    	var Finished = data.match(/finished|Finished/g);
    	if (Finished){
    		if(reloadWindowWhenFinished){
    			window.open(successurl,'_self');
    		}else{
    			document.getElementById(msgboxid).innerHTML = data;
    		}
    	}else{
    		document.getElementById(msgboxid).innerHTML = data;    	
    		setTimeout(function(){reloadprogress("msgprogress", successurl, reloadWindowWhenFinished)}, 3000);
    	}
    	    	
    	$("body").removeClass("loading");
    }).fail(function (data) {
        //count tries
    	reloadcount = reloadcount + 1;
    	if(reloadcount < 10){    		
    		setTimeout(function(){reloadprogress("msgprogress", successurl, reloadWindowWhenFinished)}, 3000);
    		$("body").removeClass("loading");
    	}else{
    		alert('ERROR');
    		$("body").removeClass("loading");    		
    	}
    }).always({        
    });
}