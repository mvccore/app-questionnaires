Class.Define('App.libs.Config', {
	Static: {
		ContainerId: 'statistics-questions-results',
		ConfigurationId: 'statistics-questions-configuration',
		LoadingId: 'statistics-questions-loading',
		FilterFormId: '',
		BasePath: '',
		Questions: [],
		StatisticsUrl: '',
		Translations: {},
		_initialized: false,
		_success: false,
		Init: function () {
			if (this._initialized) return this._success;
			this._initBasePath();
			this._initJsonAttrConfiguration();
			this._initialized = true;
			return this._success;
		},
		_initBasePath: function () {
			var
				baseElms = document.getElementsByTagName('base'),
				basePath = '/';
			if (baseElms.length > 0) {
				basePath = baseElms[0].getAttribute('href');
			}
			this.BasePath = basePath;
		},
		_initJsonAttrConfiguration: function () {
			var jsonAttr = Tools.JsonAttr(this.ConfigurationId);
			if (jsonAttr.success) {
				this._success = true;
				this.Questions			= jsonAttr.data.Questions;
				this.StatisticsUrl		= jsonAttr.data.StatisticsUrl;
				this.FilterFormId		= jsonAttr.data.FilterFormId;
				this.Translations		= jsonAttr.data.Translations;
			}
		}
	}
});
