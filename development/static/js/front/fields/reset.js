SimpleForm.Reset=function(a){this.a=null;this.Name=a};SimpleForm.Reset.prototype={aa:function(a){var b=this;b.a=a;this.a.h(this.a.b[this.Name],"click",function(a){E(b,a)})}};
function E(a,b){var c={submit:0,button:0,reset:1,radio:1,checkbox:1};n(a.a.b,function(a,b){var e=b.type;"string"==typeof e&&"number"==typeof c[e]?1!=!c[e]&&(b.checked=!1):b.value=""});b.preventDefault()};
