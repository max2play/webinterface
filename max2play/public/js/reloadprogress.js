function reloadprogress(msgboxid){
	$("body").addClass("loading");
	$.ajax({
        url : document.URL,
        type : "get",
        data : "ajax=1&loadprogress=1"
    }).done(function (data) {
    	//reload message box (still in progress) OR reload window (finished) -> variable finished
    	var Finished = data.match(/finished/g);
    	if (Finished){
    		window.open('/plugins/max2play_settings/controller/Squeezeserver.php','_self');
    	}else{
    		document.getElementById(msgboxid).innerHTML = data;    	
    		setTimeout(function(){reloadprogress("msgprogress")}, 3000);
    	}
    	    	
    	$("body").removeClass("loading");
    }).fail({
        
    }).always({        
    });
}