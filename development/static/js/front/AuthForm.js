Class.Define('AuthForm', {
	Static: {
		FORM_SELECTOR: 'form.authentication',
		_instance: null,
		GetInstance: function () {
			if (!this._instance) {
				this._instance = Class.Create(this.Fullname);
			}
			return this._instance;
		}
	},
	_form: null,
	_toggleBtn: null,
	_formMinimized: true,
	Constructor: function () {
		if (this._initElms()) {
			this._initEvents();
		}
	},
	_initElms: function () {
		var btns = [], firstBtn = null;
		this._form = document.querySelector(this.self.FORM_SELECTOR);
		btns = this._form.getElementsByTagName('button');
		firstBtn = btns[0];
		if (firstBtn.className.indexOf('toggle') > -1) {
			this._toggleBtn = firstBtn;
		}
		return this._form === null ? false : true;
	},
	_initEvents: function () {
		if (!this._toggleBtn) return;
		this._toggleBtn.onclick = function(e) {
			e = e || window.event;
			if (this._formMinimized) {
				this._formMinimized = false;
				this._form.className = this._form.className.replace('minimized', 'full');
				this._form.parentNode.className = this._form.parentNode.className + ' full';
				if (e.preventDefault) e.preventDefault();
				return false;
			}
		}.bind(this);
	}
});
AuthForm.GetInstance();
