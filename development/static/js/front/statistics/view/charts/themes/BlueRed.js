Ext.define('Ext.chart.theme.BlueRed', {
	extend: 'Ext.chart.theme.Base',
	alias: 'chart.theme.BlueRed',
	statics: {
		Colors: ['#154097', '#ff100d']
	},
	constructor: function (config) {
		this.callParent([Ext.apply({
			colors: this.self.Colors
		}, config)]);
	}
});