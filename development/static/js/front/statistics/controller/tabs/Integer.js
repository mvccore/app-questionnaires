Ext.define('App.controller.tabs.Integer', {
	extend: 'App.controller.Tab',
	requires: [],
	/*****************************************************************************************************************/
	onLaunch: function () {
		this.deelay(function () {
			this.renderSummaryIfAnyData();
			this.deelay(function () {
				this._renderOverviewGraphIfAnyData();
				this.deelay(function () {
					this._renderCorrectAnswersGraphIfAnyData();
					this.deelay(function () {
						this.renderInvolvedGraphIfAnyData();
					});
				});
			});
		});
		this.callParent(arguments);
	},
	_renderOverviewGraphIfAnyData: function () {
		var data = this._statistics.Overview,
			store = Ext.create('App.store.charts.SingleDimension', {
				data: data
			}),
			countsDisplaying = data.length > 0 && typeof (data[0].Count) == 'number',
			verticalColumn = countsDisplaying ? 'Count' : 'Percentage',
			translations = this._statistics.Translations,
			verticalTitle = translations[countsDisplaying ? 'Respondents Counts' : 'Respondents Percentages'],
			tooltipValueLabel = translations[countsDisplaying ? 'Respondents Count' : 'Respondents Percentage'],
			presentedValue = translations['Presented value'],
			verticalLegendTmpl = countsDisplaying ? '0' : '0 %',
			graphMaximum = store.max(verticalColumn),
			majorTickSteps = countsDisplaying ? graphMaximum : Math.round(graphMaximum / 10),
			chart = {};
		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			chart = Ext.create('App.view.charts.SingleColumns', {
				store: store,
				theme: 'YellowCustom',
				axes: [{
					maximum: store.max(verticalColumn),
					fields: verticalColumn,
					title: verticalTitle,
					majorTickSteps: majorTickSteps,
					renderer: function (axis, label, layoutContext) {
						return Ext.util.Format.number(layoutContext.renderer(label), verticalLegendTmpl);
					}
				}, {
					fields: 'Value',
					title: this._statistics.Translations['Presented values']
				}],
				series: [{
					yField: verticalColumn,
					label: {
						field: 'Label',
						renderer: function (value, labelInstance, config, rendererDataWithStore, storeRecordIndex) {
							return value.replace(/\n/g, ' ');
						}
					},
					tooltip: {
						trackMouse: true,
						renderer: function (tooltip, record, item) {
							var codeParts = [
								presentedValue + ': ' + record.get('Value'),
								tooltipValueLabel + ': '
							];
							if (countsDisplaying) {
								codeParts[1] += String(record.get('Count')) + ' (' + Ext.util.Format.number(record.get('Percentage'), '0.0 %') + ')';
							} else {
								codeParts[1] += Ext.util.Format.number(record.get('Percentage'), '0.0 %');
							}
							tooltip.setHtml(codeParts.join("<br />"));
						}
					},
					renderer: function (sprite, config, data, index) {
						var yellowAndRedColors = Ext.chart.theme.YellowRed.Colors;
						return { fillStyle: yellowAndRedColors[index > 0 ? 0 : 1] };
					}
				}]
			});
			this.$$view.addGraphHeading(this._statistics.Translations.Overview);
			this.$$view.content.add(chart);
		} else {
			this.$$view.addNoDataMsg();
		}
	},
	_renderCorrectAnswersGraphIfAnyData: function () {
		var data = this._statistics.CorrectAnswers,
			totalPercentCnt = 0,
			store,
			chart;
		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			for (var i = 0, l = data.length; i < l; i += 1) {
				totalPercentCnt += data[i].Percentage;
				data[i].Label = String(data[i].Value).replace(/\s/g, "\n") + ': ' + data[i].Label;
			}
			if (totalPercentCnt > 0) {
				store = Ext.create('App.store.charts.SingleDimension', {
					data: data
				});
				chart = Ext.create('App.view.charts.Pie', {
					store: store,
					theme: 'GreenRedGray'
				});
				this.$$view.addGraphHeading(this._statistics.Translations.CorrectAnswers);
				this.$$view.content.add(chart);
			} else {
				this.$$view.addNoDataMsg();
			}
		}
	}
});