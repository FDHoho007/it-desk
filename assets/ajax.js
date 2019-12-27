function ajaxGet(url, callback) {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200)
	 		callback(this.responseText);
	 	};
	xhttp.open("GET", "/wp-json/" + ITCROWD_URL_API + url, true);
	xhttp.send();
}

function ajaxPost(url, data, callback) {
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200)
	 		callback(this.responseText);
	 	};
	xhttp.open("POST", "/wp-json/" + ITCROWD_URL_API + url, true);
	xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhttp.send(data);
}