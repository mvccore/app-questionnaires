Ext.define('App.libs.Helpers', {
	config: {
		loadingImg: null,
		logoImg: null,
		xhrActive: 0,
	},
	constructor: function () {
		var adminLogoCont = Ext.get("admin-logo-cont");
		this.config.logoImg = adminLogoCont.child('.admin-logo');
		this.config.loadingImg = adminLogoCont.child('.ajax-loading');
	},
	initAjaxCommonStates: function () {
		Ext.Ajax.setDisableCaching(true);
		Ext.Ajax.setTimeout(900000);
		Ext.Ajax.setMethod('GET');
		Ext.Ajax.on('requestexception', function (conn, response, options) {
			console.log('xhr request failed');
			// do not remove notification, otherwise user is never informed about server exception (e.g. element cannot
			// be saved due to HTTP 500 Response)
			this._showNotification(
				t('Error'),
				t('Error general'),
				'error',
				this._formatErrorFromAjaxResponseAndOptions(response, options)
			);
			this.config.xhrActive -= 1;
			if (this.config.xhrActive < 1) {
				this._finishLoading();
			}
		}.bind(this));
		Ext.Ajax.on('beforerequest', function () {
			if (this.config.xhrActive < 1) {
				this._startLoading();
			}
			this.config.xhrActive += 1;
		}.bind(this));
		Ext.Ajax.on('requestcomplete', function (conn, response, options) {
			this.config.xhrActive -= 1;
			if (this.config.xhrActive < 1) {
				this._finishLoading();
			}
			// redirect to login-page if session is expired
			if (typeof response.getResponseHeader == "function") {
				if (response.getResponseHeader("X-Pimcore-Auth") == "required") {
					this._showNotification(
						t('Session error'),
						t('Session error text'),
						'error',
						this._formatErrorFromAjaxResponseAndOptions(response, options)
					);
				}
			}
		}.bind(this));
		this.config.loadingImg.addCls('hidden');
		return this;
	},
	initWindowBeforeUnload: function () {
		if (!Settings.alertOnAppClose) return;
		window.onbeforeunload = function () {
			// check for opened tabs and if the user has configured the warnings
			var mainTabs = Ext.getCmp('main-tabs');
			if (mainTabs && mainTabs.items.getCount() > 0) {
				return t("Do you really want to close administration?");
			}
		}.bind(this);
		return this;
	},
	_startLoading: function () {
		this.config.logoImg.addCls('hidden');
		this.config.loadingImg.removeCls('hidden');
	},
	_finishLoading: function () {
		this.config.loadingImg.addCls('hidden');
		this.config.logoImg.removeCls('hidden');
	},
	_formatErrorFromAjaxResponseAndOptions: function (response, options)
	{
		var errorMessage = '';
		try {
			errorMessage = "Status: " + response.status + " | " + response.statusText + "\n";
			errorMessage += "URL: " + options.url + "\n";
			if (options["params"]) {
				errorMessage += "Params:\n";
				Ext.iterate(options.params, function (key, value) {
					errorMessage += ("-> " + key + ": " + value + "\n");
				});
			}
			if (options["method"]) {
				errorMessage += "Method: " + options.method + "\n";
			}
			errorMessage += "Message: \n" + response.responseText;
		} catch (e) {
			errorMessage += "\n\n";
			errorMessage = response.responseText;
		}
		return errorMessage;
	},
	_showNotification: function (title, text, type, errorText, hideDelay) {
		// icon types: info,error,success
		if (type == "error"){
			if (errorText != null && errorText != undefined){
				text = text + '<br /><hr /><br />' +
					'<pre style="font-size:11px;word-wrap: break-word;">'
						+ String(errorText).stripTags() +
					"</pre>";
			}
			var errWin = new Ext.Window({
				modal: true,
				iconCls: "icon_notification_error",
				title: title,
				width: 700,
				height: 500,
				html: text,
				autoScroll: true,
				bodyStyle: "padding: 10px; background:#fff;",
				buttonAlign: "center",
				shadow: false,
				closable: false,
				buttons: [{
					text: "OK",
					handler: function () {
						errWin.close();
					}
				}]
			});
			errWin.show();
		} else {
			var notification = Ext.create('Ext.window.Toast', {
				iconCls: 'icon_notification_' + type,
				title: title,
				html: text,
				autoShow: true
				//autoDestroy: true
				//,
				//hideDelay:  hideDelay | 1000
			});
			notification.show(document);
		}
	}
});