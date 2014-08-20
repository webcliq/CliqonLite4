function loadScript(e,t){
	var n=document.createElement("script");
	n.type="text/javascript";
	if(n.readyState){n.onreadystatechange=function(){if(n.readyState=="loaded"||n.readyState=="complete"){n.onreadystatechange=null;t()}}}else{n.onload=function(){t()}}n.src=e;document.getElementsByTagName("head")[0].appendChild(n)};
