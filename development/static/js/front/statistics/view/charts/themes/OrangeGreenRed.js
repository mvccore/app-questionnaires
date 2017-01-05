Ext.define('Ext.chart.theme.OrangeGreenRed', {
	extend: 'Ext.chart.theme.Base',
	alias: 'chart.theme.OrangeGreenRed',
	statics: {
		Colors: ['#ffa500', '#6cb61e', '#ff100d']
	},
	constructor: function (config) {
		this.callParent([Ext.apply({
			colors: this.self.Colors
		}, config)]);
	}
});