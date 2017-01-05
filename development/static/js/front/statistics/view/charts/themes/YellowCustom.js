Ext.define('Ext.chart.theme.YellowCustom', {
	extend: 'Ext.chart.theme.Base',
	alias: 'chart.theme.YellowCustom',
	statics: {
		Colors: ['#ffff00']
	},
	constructor: function (config) {
		this.callParent([Ext.apply({
			colors: this.self.Colors
		}, config)]);
	}
});
