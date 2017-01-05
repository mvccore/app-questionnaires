Ext.define('Ext.chart.theme.YellowRed', {
	extend: 'Ext.chart.theme.Base',
	alias: 'chart.theme.YellowRed',
	statics: {
		Colors: ['#ffff00', '#ff100d']
	},
	constructor: function (config) {
		this.callParent([Ext.apply({
			colors: this.self.Colors
		}, config)]);
	}
});