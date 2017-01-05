Ext.define('App.view.charts.Pie', {
	extend: 'Ext.chart.PolarChart',
	requires: [
		//'Ext.chart.theme.GreenRedGray'
	],
	reference: 'chart',
	width: '100%',
	height: 450,
	innerPadding: 90,
	border: false,
	// store: store, // store has to be defined by Ext.create()
	theme: 'Muted',
	interactions: ['itemhighlight', 'rotatePie3d'],
	/*legend: {
		docked: 'bottom'
	},*/
	series: [{
		type: 'pie3d',
		angleField: 'Percentage',
		donut: 0,
		distortion: 0.6,
		thickness: 50,
		highlight: {
			margin: 15
		},
		label: {
			field: 'Label',
			// renderer is not working in 3D Pie chart
			/*renderer: function (tooltip, record, item) {
				console.log(arguments);
				tooltip.setHtml(record.get('Value') + ': ' + Ext.util.Format.number(record.get('Percentage'), '0,0 %'));
			}*/
		},
		subStyle: {
			bevelWidth: 13,
			opacity: 0.8
		}/*,
		tooltip: {
			trackMouse: true,
			renderer: function (tooltip, record, item) {
				tooltip.setHtml(record.get('Answer') + ': ' + record.get('Percentage') + '%');
			}
		}*/
	}]
});