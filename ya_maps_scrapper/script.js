// ==UserScript==
// @name         New Userscript
// @namespace    http://tampermonkey.net/
// @version      0.1
// @description  try to take over the world!
// @author       You
// @match        https://yandex.ru/maps/*
// @grant GM_xmlhttpRequest
// ==/UserScript==

(function(open) {

    XMLHttpRequest.prototype.open = function(method, url, async, user, pass) {

        this.addEventListener("readystatechange", function() {
            if (this.responseURL.match(/https:\/\/yandex.ru\/maps\/api\/search(.*)/)) {
                var a = eval("(" + this.response + ")");
                var results={}; 
                var text = a.data.features[0].properties.GeocoderMetaData.text;
                console.log(text+": ");
                for (i=0; i<10000; i++) {
                    if (typeof a.data.features[0].geometries[1].geometries[i] == 'undefined')
                        break;
                    results[i] = [];
                    a.data.features[0].geometries[1].geometries[i].coordinates[0].forEach(function(j) {results[i].push(j[0] + "," + j[1])});
                }
                console.log(JSON.stringify({"text": text, "coords": results}));

                GM_xmlhttpRequest( {
                    method:     "POST",
                    url:        "http://unishop.local/main/default/yandex",
                    data:       JSON.stringify({"text": text, "coords": results}),
                    headers:    {
                        "Content-Type": "application/json"
                    },
                    onload:     function (response) {
                        console.log ("gut response");                      
                    }
                } );
            }
        }, false);

        open.call(this, method, url, async, user, pass);
    };

})(XMLHttpRequest.prototype.open);
