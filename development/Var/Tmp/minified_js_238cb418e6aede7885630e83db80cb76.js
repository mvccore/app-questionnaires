
/* /static/js/front/LoginForm.js */
Class.Define('LoginForm',{Static:{FORM_ID:'login',_instance:null,GetInstance:function(){if(!this._instance){this._instance=Class.Create(this.Fullname);}
return this._instance;}},_form:null,_toggleBtn:null,_formMinimized:true,Constructor:function(){if(this._initElms()){this._initEvents();}},_initElms:function(){var firstBtn=null;this._form=document.getElementById(this.self.FORM_ID);firstBtn=this._form[0];if(firstBtn.className.indexOf('toggle')>-1){this._toggleBtn=firstBtn;}
return this._form===null?false:true;},_initEvents:function(){console.log(this._toggleBtn);if(!this._toggleBtn)return;this._toggleBtn.onclick=function(e){e=e||window.event;if(this._formMinimized){this._formMinimized=false;this._form.className=this._form.className.replace('minimized','full');this._form.parentNode.className=this._form.parentNode.className+' full';console.log("asdf");if(e.preventDefault)e.preventDefault();return false;}}.bind(this)}});LoginForm.GetInstance();
