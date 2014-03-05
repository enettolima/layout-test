// TODO: Get rid of this
function getHTTPObject() {
	if (window.ActiveXObject) 
		return new ActiveXObject("Microsoft.XMLHTTP");
	else if (window.XMLHttpRequest) 
		return new XMLHttpRequest();
	else {
		alert("Your browser does not support AJAX.");
		return null;
	}
}

function setLoading(loading) {
	var loadingObj = document.getElementById('loading-status');
	
	loadingObj.style.display = (loading ? 'block' : 'none');
}