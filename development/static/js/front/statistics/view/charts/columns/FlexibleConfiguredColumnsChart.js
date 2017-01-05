Ext.define('App.view.charts.columns.FlexibleConfiguredColumnsChart', {
	extend: 'Ext.chart.CartesianChart',
	require: [
		// 'App.view.charts.vbar.HorizontalAxeLabelTextCfgs'
	],
	reference: 'chart',
	//flipXY: true,
	width: '100%',
	minHeight: 400,
	insetPadding: '25 25 25 25',
	innerPadding: '0 5 0 5',
	// Base, Default, Blue, BlueGradients, Category1, Category1Gradients, Category2, Category2Gradients, Category3, Category3Gradients, Category4, Category4Gradients, Category5, Category5Gradients, Category6, Category6Gradients, DefaultGradients, Green, GreenGradients, Midnight, Muted, Purple, PurpleGradients, Red, RedGradients, Sky, SkyGradients, Yellow, YellowGradients, GreenRedGray
	theme: 'Muted',
	// store: store, // store has to be defined by Ext.create()
	animation: {
		easing: 'easeOut',
		duration: 500
	},
	border: false,
	interactions: ['itemhighlight'],
	axes: [
		{
			type: 'numeric3d',
			position: 'left',
			fields: ['Count0', 'Count1'],
			minimum: -6,
			maximum: 11,
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
			yField: ['Count0', 'Count1'],
			highlight: true,
			style: {
				minGapWidth: 10
			},
			subStyle: {
				opacity: 0.95
			},
			label: {
				field: ['Count0', 'Count1'],
				display: 'insideEnd',
				// orientation: 'horizontal',
				renderer: function (v) {
					return Ext.util.Format.number(v, '0.0');
				}
			},
			tooltip: {
				trackMouse: true,
				/*renderer: function (tooltip, record, item) {
					tooltip.setHtml(record.get('Value') + ': ' + Ext.util.Format.number(record.get('Percentage0'), '0.0 %'));
				}*/
			},
			renderer: function (sprite, config, data, index) {
				var colors = Ext.chart.theme.GreenRedGray.Colors;
				var fieldName = sprite._field;
				if (fieldName.indexOf('0') > -1) {
					return {
						fillStyle: colors[1]
					};
				} else {
					return {
						fillStyle: colors[0]
					};
				}
			}
		}
	],
	constructor: function (cfg) {
		this._customizeAxes(cfg);
		this._customizeSeries(cfg);
		this._customizeBottomHorizontalAxeLabelByStoreTextsLengths(cfg);
		this.callParent([cfg]);
	},
	_customizeAxes: function (cfg) {
		var axesCustomCfg = cfg.axes,
			axesCfg = this.config.axes,
			axesCustomItem = {},
			axeItem = {};
		if (Tools.TypeOf(axesCustomCfg) == 'Array') {
			for (var i = 0, l = axesCfg.length; i < l; i += 1) {
				axeItem = axesCfg[i];
				if (typeof (axesCustomCfg[i]) != 'undefined') {
					axesCustomItem = axesCustomCfg[i];
					cfg.axes[i] = Ext.merge(axeItem, axesCustomItem);
				} else {
					cfg.axes[i] = axeItem;
				}
			}
		}
	},
	_customizeSeries: function (cfg) {
		var seriesCustomCfg = cfg.series,
			seriesCfg = this.config.series,
			seriesCustomItem = {},
			serieItem = {};
		if (Tools.TypeOf(seriesCustomCfg) == 'Array') {
			for (var i = 0, l = seriesCfg.length; i < l; i += 1) {
				serieItem = seriesCfg[i];
				if (typeof (seriesCustomCfg[i]) != 'undefined') {
					seriesCustomItem = seriesCustomCfg[i];
					cfg.series[i] = Ext.merge(serieItem, seriesCustomItem);
				} else {
					cfg.series[i] = axeItem;
				}
			}
		}
	},
	_customizeBottomHorizontalAxeLabelByStoreTextsLengths: function (cfg) {
		var biggestTextLength = 0;
		var horizontalAxeLabelTextCfgs = App.view.charts.columns.HorizontalAxeLabelTextCfgs.Data;
		var horizontalAxeCfg = {};
		var axes = cfg.axes;
		var axeFields = [];
		var textField = '';
		var axe;
		for (var i = 0, l = axes.length; i < l; i += 1) {
			axe = axes[i];
			if (axe.position == 'bottom') {
				axeFields = Tools.TypeOf(axe.fields) == 'Array' ? axe.fields : [axe.fields.toString()];
				textField = axeFields[0];
				cfg.store.each(function (record, itemIndex, itemsCount) {
					var text = String(record.data[textField]);
					if (text.length > biggestTextLength) biggestTextLength = text.length;
				});
				biggestTextLength -= 1;
				for (var j = 0, k = horizontalAxeLabelTextCfgs.length; j < k; j += 1) {
					horizontalAxeCfg = horizontalAxeLabelTextCfgs[j];
					if (biggestTextLength < horizontalAxeCfg.MaxChars) {
						break;
					}
				}
				axe.label = Ext.merge(axe.label, horizontalAxeCfg.LabelCfg);
				for (var key in horizontalAxeCfg.BaseCfg) {
					cfg[key] = horizontalAxeCfg.BaseCfg[key];
				}
				break;
			}
		}
	}
});