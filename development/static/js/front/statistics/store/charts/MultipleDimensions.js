Ext.define('App.store.charts.MultipleDimensions', {
	extend: 'Ext.data.Store',
	fields: [
		'Value',
		'Percentage0', 'Count0', 'Label0',
		'Percentage1', 'Count1', 'Label1'
	]
});