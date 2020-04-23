function obj(id) {
	return document.getElementById(id);
}
function ajaxRequest(url, objID, callback) {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) {
			if(obj(objID) != null)
				obj(objID).innerHTML = this.responseText;
			if(callback != undefined && typeof callback == 'function')
				callback(this.responseText);
		}
	};
	xhttp.open("GET", url, true);
	xhttp.send();
}
function ajaxRequestPost(url, body, objID, callback) {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if(this.readyState == 4 && this.status == 200) {
			if(obj(objID) != null)
				obj(objID).innerHTML = this.responseText;
			if(callback != undefined && typeof callback == 'function')
				callback(this.responseText);
		}
	};
	xhttp.open("POST", url, true);
	xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	xhttp.send(body);
}
function urlencodeObject(srcjson){
	if(typeof srcjson !== "object")
		return null;
	var urljson = "";
	var keys = Object.keys(srcjson);
	for(var i=0; i <keys.length; i++){
		urljson += encodeURIComponent(keys[i]) + "=" + encodeURIComponent(srcjson[keys[i]]);
		if(i < (keys.length-1))urljson+="&";
	}
	return urljson;
}
function PopupCenter(url, title) {
	var w = 480, h = 340, x = 0, y = 0;
	if(window.screenTop) {
		w = document.body.clientWidth;
		h = document.body.clientHeight;
		x = window.screenTop;
		y = window.screenLeft;
	}
	else if(window.screenX) {
		w = window.innerWidth;
		h = window.innerHeight;
		x = window.screenX;
		y = window.screenY;
	}
	var popW = 800, popH = 600;
	var leftPos = ((w-popW)/2)+y, topPos = ((h-popH)/2)+x;
	return window.open(url,title,'width='+popW+',height='+popH+',top='+topPos+',left='+leftPos);
}
