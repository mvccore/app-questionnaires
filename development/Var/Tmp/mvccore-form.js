var MvcCoreForm=function(a,b){this.b=a;this.c=b||[];MvcCoreForm.I[a.id]=this;h(this)};MvcCoreForm.T=/MSIE [6-8]/g.test(navigator.userAgent);MvcCoreForm.I={};MvcCoreForm.GetInstance=function(a){return MvcCoreForm.I[a]};
MvcCoreForm.prototype={F:MvcCoreForm.T,h:function(a,b,c,d){function e(a){a=a||window.event;var b=!1;a.preventDefault||(a.preventDefault=function(){b=!0});c(a);if(b)return!1}this.F?a.attachEvent("on"+b,e):a.addEventListener(b,e,!!d)},P:function(a){return document.createElement(a)},O:function(a,b){return this.F?a.insertAdjacentElement("beforeEnd",b):a.appendChild(b)},u:function(a,b){a.className+=" "+b},G:function(a,b){for(var c=" "+b+" ",d=new RegExp(c,"g"),e=String(" "+a.className+" ");-1<e.indexOf(c);)e=
e.replace(d," ");a.className=e.replace(/\s+/g," ")},R:function(a,b){return a.getAttribute(b)},w:function(a,b,c){return a.setAttribute(b,c)},v:function(a,b){return a.removeAttribute(b)}};function m(a,b){for(var c=0,d=a.length;c<d&&!1!==b(c,a[c],a);c+=1);}
function n(a,b){var c=a.b,d=!0,e=[];m(a.c,function(a,b){var f=b.name,g=[],f="undefined"!=typeof c[f]?c[f].value:"";if("Validate"in b){try{g=b.Validate(f)}catch(l){g=[l.message]}0<g.length&&(d=!1,e.push(g.join(String.fromCharCode(10))))}});d||(alert(e.join(String.fromCharCode(10))),b.preventDefault())}function h(a){m(a.c,function(b,c){if("Init"in c)try{c.Init(a)}catch(d){console?console.log(d,d.stack):alert(d.message)}});a.b.onsubmit=function(b){n(a,b||window.event)}}
;