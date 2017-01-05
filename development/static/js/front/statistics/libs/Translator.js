Class.Define('App.libs.Translator', {
	Static: {
		_instance: null,
		Translations: {},
		SetUsedKey: function (key) {
			if (!this._instance) {
				this._instance = Class.Create(this.Fullname);
			}
			this._instance._setUsedKey(key);
		}
	},
	Constructor: function () {
		this.usage = [];
		this.timeout = 15;
		this.id = 0;
	},
	_setUsedKey: function (key) {
		var item = {};
		item[key] = Date.unixTimestamp();
		this.usage.push(item);
		if (!this.id) this._timeoutSync();
	},
	_timeoutSync: function () {
		this.id = setTimeout(
			function () {
				this._sync();
			}.bind(this),
			this.timeout * 1000
		);
	},
	_sync: function () {
		if (this.usage.length > 0) {
			// temporary disabled:
			/*
			Ext.Ajax.request({
				url: Settings.USED_TRANSLATIONS_SYNC_URL,
				method: 'POST',
				params: {
					usage: JSON.stringify(this.usage)
				},
				success: function (response, opts) {
					this.usage = [];
					this._timeoutSync();
				}.bind(this),
				failure: function (response, opts) {
					this.usage = [];
					this._timeoutSync();
				}.bind(this)
			});
			*/
			this._timeoutSync();
		} else {
			this._timeoutSync();
		}
	}
});

var t = function (key) {
	var translator = App.libs.Translator;
	if (typeof (translator.Translations[key]) != 'undefined') {
		translator.SetUsedKey(key);
		return translator.Translations[key];
	} else {
		return key;
	}
}