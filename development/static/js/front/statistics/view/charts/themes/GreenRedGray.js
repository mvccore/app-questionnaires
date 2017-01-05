Ext.define('Ext.chart.theme.GreenRedGray', {
	extend: 'Ext.chart.theme.Base',
	alias: 'chart.theme.GreenRedGray',
	statics: {
		Colors: ['#6cb61e', '#ff100d', '#3c3f41']
	},
	constructor: function (config) {
		this.callParent([Ext.apply({
			colors: this.self.Colors
		}, config)]);
	}
});