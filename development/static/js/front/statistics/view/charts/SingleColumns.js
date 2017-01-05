Ext.define('App.view.charts.SingleColumns', {
	extend: 'App.view.charts.columns.FlexibleConfiguredColumnsChart',
	axes: [
		{
			type: 'numeric3d',
			position: 'left',
			fields: 'Count',
			maximum: 100,
			titleMargin: 10,
			majorTickSteps: 10,
			label: {
				textAlign: 'right'
			},
			grid: {
				odd: { fillStyle: 'rgba(255, 255, 255, 0.06)' },
				even: { fillStyle: 'rgba(0, 0, 0, 0.05)' }
			},
			renderer: function (axis, label, layoutContext) {
				return Ext.util.Format.number(layoutContext.renderer(label), '0');
			}
		}, {
			type: 'category3d',
			position: 'bottom',
			fields: 'Value',
			label: {
				textAlign: 'right'
			},
			// titleMargin: 10,
			grid: true
		}
	],
	series: [
		{
			type: 'bar3d',
			xField: 'Value',
			yField: 'Count',
			highlight: true,
			style: {
				minGapWidth: 10
			},
			subStyle: {
				opacity: 0.95
			},
			label: {
				field: 'Count',
				display: 'insideEnd',
				renderer: function (v) {
					return Ext.util.Format.number(v, '0.0');
				}
			},
			tooltip: {
				trackMouse: true,
				renderer: function (tooltip, record, item) {
					tooltip.setHtml(record.get('Value') + ': ' + Ext.util.Format.number(record.get('Percentage'), '0.0 %'));
				}
			}
		}
	]
});