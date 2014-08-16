(function(m,n,e,p){var h={text:"1.1.0",major:1,minor:1,sub:0},q=function(g,e,d,k){Date.now=Date.now||function(){return(new Date).getTime()};d.version=h;d.storage={};g.localStorage?(d.storage.get=function(b,a){a==k&&(a=3600);a*=1E3;var c=g.localStorage.getItem(b);if(null!=c)try{var d=JSON.parse(c);if(d&&d.timestamp&&d.timestamp+a>=Date.now())return d.data}catch(e){}return null},d.storage.set=function(b,a){var c=JSON.stringify({timestamp:Date.now(),data:a});g.localStorage.setItem(b,c)}):g.sessionStorage&& (d.storage.get=function(b,a){a==k&&(a=3600);a*=1E3;var c=g.sessionStorage.getItem(b);if(null!=c)try{var d=JSON.parse(c);if(d&&d.timestamp&&d.timestamp+a>=Date.now())return d.data}catch(e){}return null},d.storage.set=function(b,a){var c=JSON.stringify({timestamp:Date.now(),data:a});g.sessionStorage.setItem(b,c)});d.isImageCompatible=null;d.loadImage=function(b,a){a=a||{};var c={},f;for(f in a)c[f]=a[f];null===d.isImageCompatible&&(d.isImageCompatible="function"==typeof btoa);"object"==typeof b&&b.getAttribute? (f=c.attributeName||"resource","done"!==b.getAttribute("data-"+f+"-src-action")&&null!==b.getAttribute("data-"+f+"-src")&&(d.isImageCompatible?(c.imageElement=b,d.load(b.getAttribute("data-"+f+"-src"),c)):(b.setAttribute("src",b.getAttribute("data-"+f+"-src")),c.complete&&c.complete.call&&(c.loadedFrom="web/direct",c.rawsrc=b.getAttribute("data-"+f+"-src"),c.src=a.rawsrc,c.imageElement=b,c.complete.call(a.self,a))),b.setAttribute("data-"+f+"-src-action","done"))):"string"==typeof b&&d.loadImage(e.getElementById(b), c)};d.loadImagesInterval=null;d.loadImages=function(b){b=b||{};null===d.loadImagesInterval&&(d.loadImagesInterval=setInterval(function(){d.loadImages(b)},50));var a=e.getElementsByTagName("img");if(0<a.length)for(var c=0;c<a.length;c++){var f=a[c],l=b.attributeName||"resource";null==f.getAttribute("data-"+l+"-src-action")&&f.getAttribute&&null!==f.getAttribute("data-"+l+"-src")&&d.loadImage(f,b)}};d.stopImageLoading=function(){null!==d.loadImagesInterval&&(clearInterval(d.loadImagesInterval),d.loadImagesInterval= null)};d.load=function(b,a){var c;if("string"==typeof b){a=a||{};a.loadCount=a.loadCount||0;a.loadedFrom=null;a.waitfor=a.waitfor||a.waitFor;if(a.waitfor&&a.waitfor.call&&!1===a.waitfor.call(this,a))return setTimeout(function(){d.load(b,a)},10),this;if(a.test)if(a.test.call){if(a.test.call(this,a))return a.loadedFrom="notLoaded/test",this}else if(a.test)return a.loadedFrom="notLoaded/test",this;a.rawsrc=b;b.match(/^js\!/g)?(b=b.substring(3,b.length),c="js"):b.match(/^css\!/g)?(b=b.substring(4,b.length), c="css"):b.match(/^(jpe|jpeg|jpg|gif|png)\!/g)?(a.imageType=b.substring(0,b.indexOf("!")-1),b=b.substring(b.indexOf("!"),b.length),c="image"):b.match(/\.css$/g)?c="css":(a.mediaType=b.match(/\.(jpe|jpeg|jpg|gif|png)$/g))?(a.mediaType=a.mediaType[0].substring(1,a.mediaType[0].length),c="image"):c="js";a.cacheName=!0===a.cache?b.replace(/[^a-zA-Z0-9]/g,"_"):!1;a.src=b;"js"==c?this._loadScript(b,a):"css"==c?this._loadStyle(b,a):"image"==c&&this._loadImage(b,a)}else if("object"==typeof b)for(c in b)this.load(b[c]); return this};d._loadScript=function(b,a){var c=null;a=a||{};if(!1!==a.cacheName&&(c=this.storage.get(a.cacheName,a.cacheTimeout),null!=c))return a.loadedFrom="cache",d._appendScript(c,a);d._fetchData(b,a,d._appendScript)};d._appendScript=function(b,a){var c=e.getElementsByTagName("head")[0],d=e.createElement("script");d.appendChild(e.createTextNode(b));c.appendChild(d);a.loadCount--;a&&a.complete&&a.complete.call&&a.complete.call(a.self,a)};d._loadStyle=function(b,a){var c=null;a=a||{};if(!1!==a.cacheName&& (c=this.storage.get(a.cacheName,a.cacheTimeout),null!=c))return a.loadedFrom="cache",d._appendStyle(c,a);d._fetchData(b,a,d._appendStyle)};d._appendStyle=function(b,a){var c=e.getElementsByTagName("head")[0],d=e.createElement("style");d.appendChild(e.createTextNode(b));c.appendChild(d);a.loadCount--;a&&a.complete&&a.complete.call&&a.complete.call(a.self,a)};d._loadImage=function(b,a){var c=null;a=a||{};if(!1!==a.cacheName&&(c=this.storage.get(a.cacheName,a.cacheTimeout),null!=c))return a.loadedFrom= "cache",d._assignImage(c,a);d._fetchData(b,a,d._assignImage)};d._assignImage=function(b,a){a.loadCount--;var c="data:",c="png"==a.mediaType?c+"image/png":"gif"==a.mediaType?c+"image/gif":c+"image/jpeg";a.imageElement.setAttribute("src",c+";base64,"+b);a&&a.complete&&a.complete.call&&a.complete.call(a.self,a)};d._fetchData=function(b,a,c){a.self=this;if(g.jQuery&&!a.mediaType)g.jQuery.ajax({url:b,dataType:"text",success:function(b){a&&!1!==a.cacheName&&a.self.storage.set(a.cacheName,b);a.loadedFrom= "web/jquery";c&&c.call?c.call(a.self,b,a):a&&a.complete&&a.complete.call&&a.complete.call(a.self,a)}});else if(g.XMLHttpRequest){var d=new g.XMLHttpRequest;d.open("GET",b,!0);a.loadCount++;a.mediaType&&(d.responseType="arraybuffer");d.onreadystatechange=function(){if(4==d.readyState){a.loadedFrom="web/xhr";if(a.mediaType)var b=new Uint8Array(this.response),b=String.fromCharCode.apply(null,b),b=btoa(b);else b=d.responseText;a&&!1!==a.cacheName&&a.self.storage.set(a.cacheName,b);c&&c.call?c.call(a.self, b,a):a&&a.complete&&a.complete.call&&a.complete.call(a.self,a)}};d.send(null)}};d.getVersion=function(){return this.version.text};g.resourceLoader=d};if(e.version==p||e&&e.version&&(e.version.major<h.major||e.version.major==h.major&&(e.version.minor<h.minor||e.version.minor==h.minor&&e.version.sub<h.sub)))return q(m,n,e)})(window,document,window.resourceLoader||{});