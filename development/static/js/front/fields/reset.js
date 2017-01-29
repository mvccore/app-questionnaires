MvcCoreForm.Reset=function(a){this.a=null;this.Name=a};MvcCoreForm.Reset.prototype={aa:function(a){var b=this;b.a=a;this.a.h(this.a.b[this.Name],"click",function(a){E(b,a)})}};
function E(a,b){var c={submit:0,button:0,reset:1,radio:1,checkbox:1};m(a.a.b,function(a,b){var d=b.type;"string"==typeof d&&"number"==typeof c[d]?1!=!c[d]&&(b.checked=!1):b.value=""});b.preventDefault()};
;