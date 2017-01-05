Ext.Loader.setConfig({
	enabled: false,
	//preserveScripts: false, // false for synchronous loading
	disableCaching: true // true for development mode
});

//Ext.Loader.setPath('Ext.chart.Chart', '../../libs/ext/6.0.0/packages/charts');

Ext.application({
	name: 'App',
	appProperty: 'instance',
	autoCreateViewport: false,
	requires: [,
		'App.libs.Config',
		'App.libs.Translator',
		'App.libs.Helpers'
	],
	controllers: ['Base'],
	launch: function () {
		var config = App.libs.Config;
		if (config.Init()) {
			App.instance.appFolder = config.BasePath + 'static/js/front/statistics';
			App.libs.Translator.Translations = config.Translations;
		}
	}
});