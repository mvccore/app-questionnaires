var SimpleForm=function(a,b){this.b=a;this.c=b||[];SimpleForm.M[a.id]=this;k(this)};SimpleForm.S=/MSIE [6-8]/g.test(navigator.userAgent);SimpleForm.M={};SimpleForm.GetInstance=function(a){return SimpleForm.M[a]};
SimpleForm.prototype={F:SimpleForm.S,h:function(a,b,c,d){function f(a){a=a||window.event;var b=!1;a.preventDefault||(a.preventDefault=function(){b=!0});c(a);if(b)return!1}this.F?a.attachEvent("on"+b,f):a.addEventListener(b,f,!!d)},P:function(a){return document.createElement(a)},O:function(a,b){return this.F?a.insertAdjacentElement("beforeEnd",b):a.appendChild(b)},u:function(a,b){a.className+=" "+b},G:function(a,b){for(var c=" "+b+" ",d=new RegExp(c,"g"),f=String(" "+a.className+" ");-1<f.indexOf(c);)f=
f.replace(d," ");a.className=f.replace(/\s+/g," ")},R:function(a,b){return a.getAttribute(b)},w:function(a,b,c){return a.setAttribute(b,c)},v:function(a,b){return a.removeAttribute(b)}};function n(a,b){for(var c=0,d=a.length;c<d&&!1!==b(c,a[c],a);c+=1);}
function p(a,b){var c=a.b,d=!0,f=[];n(a.c,function(a,b){var h=b.name,l=[],h="undefined"!=typeof c[h]?c[h].value:"";if("Validate"in b){try{l=b.Validate(h)}catch(m){l=[m.message]}0<l.length&&(d=!1,f.push(l.join(String.fromCharCode(10))))}});d||(alert(f.join(String.fromCharCode(10))),b.preventDefault())}function k(a){n(a.c,function(b,c){if("Init"in c)try{c.Init(a)}catch(d){console?console.log(d,d.stack):alert(d.message)}});a.b.onsubmit=function(b){p(a,b||window.event)}}
;