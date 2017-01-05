Ext.define('App.controller.tabs.Checkboxes', {
	extend: 'App.controller.Tab',
	requires: [
		// 'App.store.charts.SingleDimension',
		// 'App.view.charts.SingleColumns'
	],
	/*****************************************************************************************************************/
	onLaunch: function () {
		this.deelay(function () {
			this.renderSummaryIfAnyData();
			this.deelay(function () {
				this._renderOverviewGraphIfAnyData();
				this.deelay(function () {
					this._renderSelectedOptionsCountsInAnswerGraph();
					this.deelay(function () {
						this._renderAnswersCorrectnessGraph();
						this.deelay(function () {
							this._renderPeopleCorrectnessGraph();
							this.deelay(function () {
								this.renderInvolvedGraphIfAnyData();
							});
						});
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
			verticalTitle = this._statistics.Translations[countsDisplaying ? 'Selections Counts' : 'Selections Percentages'],
			verticalLegendTmpl = countsDisplaying ? '0' : '0 %',
			graphMaximum = store.max(verticalColumn),
			majorTickSteps = countsDisplaying ? graphMaximum : Math.round(graphMaximum / 2),
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
					title: this._statistics.Translations['Question Options']
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
							var code = '';
							if (countsDisplaying) {
								code = record.get('Value') + ': '
									+ String(record.get('Count')) + ' ('
									+ Ext.util.Format.number(record.get('Percentage'), '0.0 %') + ')';
							} else {
								code = record.get('Value') + ': '
									+ Ext.util.Format.number(record.get('Percentage'), '0.0 %');
							}
							tooltip.setHtml(code);
						}
					},
					renderer: function (sprite, config, data, index) {
					}
				}]
			});
			this.$$view.addGraphHeading(this._statistics.Translations.Overview);
			this.$$view.content.add(chart);
		} else {
			this.$$view.addNoDataMsg();
		}
	},
	_renderSelectedOptionsCountsInAnswerGraph: function () {
		var data = this._statistics.SelectedOptionsCountsInAnswer,
			countsDisplaying = (data.length > 0 && typeof (data[0].Count) == 'number'),
			verticalColumn = countsDisplaying ? 'Count' : 'Percentage',
			translations = this._statistics.Translations,
			verticalTitle = translations[countsDisplaying ? 'Respondents Counts' : 'Respondents Percentages'],
			verticalLegendTmpl = (countsDisplaying ? '0' : '0 %'),
			graphMax = 0,
			majorTickSteps = 0,
			store = {},
			chart = {};
		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			store = Ext.create('App.store.charts.SingleDimension', {
				data: data
			});
			graphMax = store.max(verticalColumn);
			majorTickSteps = countsDisplaying ? graphMax : Math.round(graphMax / 2);
			chart = Ext.create('App.view.charts.SingleColumns', {
				store: store,
				theme: 'Blue',
				axes: [{
					maximum: graphMax,
					fields: verticalColumn,
					title: verticalTitle,
					majorTickSteps: majorTickSteps,
					renderer: function (axis, label, layoutContext) {
						return Ext.util.Format.number(layoutContext.renderer(label), verticalLegendTmpl);
					},
				}, {
					fields: 'Value',
					title: translations['Selected Options Counts In Answer']
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
							var code = '';
							if (countsDisplaying) {
								code = record.get('Value') + ': '
									+ String(record.get('Count')) + ' ('
									+ Ext.util.Format.number(record.get('Percentage'), '0.0 %') + ')';
							} else {
								code = record.get('Value') + ': '
									+ Ext.util.Format.number(record.get('Percentage'), '0.0 %');
							}
							tooltip.setHtml(code);
						}
					},
					renderer: function (sprite, config, data, index) {
						var blueAndRedColors = Ext.chart.theme.BlueRed.Colors;
						return { fillStyle: blueAndRedColors[index > 0 ? 0 : 1] };
					}
				}]
			});
			this.$$view.addGraphHeading(translations.SelectedOptionsCountsInAnswer);
			this.$$view.content.add(chart);
		}
	},
	_renderAnswersCorrectnessGraph: function () {
		var data = this._statistics.OptionsCorrectness,
			min = 0, max = 0, diff = 0,
			translations = this._statistics.Translations,
			store = {},
			chart = {};
		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			// switch first z-level keyed as 'Count0' into negative values to display columns in negative position
			for (var i = 0, l = data.length; i < l; i += 1) {
				data[i].Count0 = data[i].Count0 > 0 ? data[i].Count0 * (-1) : data[i].Count0;
				data[i].Percentage0 = data[i].Percentage0 > 0 ? data[i].Percentage0 * (-1) : data[i].Percentage0;
			}
			store = Ext.create('App.store.charts.MultipleDimensions', {
				data: data
			});
			min = store.min('Count0') - 4;
			max = store.max('Count1') + 4;
			diff = max - min;
			chart = Ext.create('App.view.charts.MultiColumns', {
				store: store,
				axes: [{
					title: {
						text: translations['Number Of Correct And Incorrect Respondents'],
						fontSize: '11px'
					},
					minimum: min,
					maximum: max,
					majorTickSteps: diff,
					renderer: function (axis, label, layoutContext) {
						return Ext.util.Format.number(layoutContext.renderer(Math.abs(label)), '0');
					}
				}, {
					title: translations['Question Options'],
					titleMargin: 10
				}],
				series: [{
					label: {
						font: '11px Helvetica',
						display: 'outside',
						//orientation: 'horizontal',
						renderer: function (value, markerInstance, markerConfig, stores, index) {
							var record = stores.store.getAt(index);
							if (value > 0) {
								return record.data.Label1;
							} else if (value < 0) {
								return record.data.Label0;
							} else {
								return '0 (0 %)';
							}
						}
					},
					tooltip: {
						renderer: function (tooltip, record, item) {
							var fieldName = item.field;
							var recordData = item.record.data;
							tooltip.setHtml([
								recordData.Value,
								translations['Correctly answered respondents'] + ': ' + recordData.Label1,
								translations['Incorrectly answered respondents'] + ': ' + recordData.Label0
							].join('<br />'));
						}
					}
				}]
			});
			this.$$view.addGraphHeading(translations.OptionsCorrectness);
			this.$$view.content.add(chart);
		}
	},
	_renderPeopleCorrectnessGraph: function () {
		var rawData = this._statistics.PeopleCorrectness,
			rawItem = {},
			data = [],
			translations = this._statistics.Translations,
			valueTmpl = '',
			min = 0, max = 0, diff = 0,
			store = {},
			chart = {};
		if (Tools.TypeOf(rawData) == 'Array' && rawData.length > 0) {
			rawData = this._statistics.PeopleCorrectness;
			// switch first z-level keyed as 'Count0' into negative values to display columns in negative position
			for (var i = 0, l = rawData.length; i < l; i += 1) {
				rawItem = rawData[i];
				data.push({
					Value: String.format(
						'{0}, {1}',
						this._translateCorrectAndIncorectAnswers(rawItem.CorrectlyAnsweredOptions, true),
						this._translateCorrectAndIncorectAnswers(rawItem.IncorrectlyAnsweredOptions, false)
					),
					Count: rawItem.PersonsCount,
					Label: rawItem.PersonsLabel
				});
			}
			store = Ext.create('App.store.charts.SingleDimension', {
				data: data
			});
			chart = Ext.create('App.view.charts.SingleColumns', {
				store: store,
				theme: 'Sky',
				axes: [{
					title: translations['Respondents Counts'],
					renderer: function (axis, label, layoutContext) {
						return Ext.util.Format.number(layoutContext.renderer(label), '0');
					},
				}, {
					fields: 'Value',
					title: {
						text: translations['Combination Of Correctly And Incorrectly Answered Options Counts'],
						fontSize: '12px'
					}
				}],
				series: [{
					label: {
						field: 'Label',
						renderer: function (value, labelInstance, config, rendererDataWithStore, storeRecordIndex) {
							return value.replace(/\n/g, ' ');
						}
					},
					renderer: function (sprite, config, data, index) {
					}
				}]
			});
			this.$$view.addGraphHeading(translations.PeopleCorrectness);
			this.$$view.content.add(chart);
		}
	},
	_translateCorrectAndIncorectAnswers: function (value, correctAnswer) {
		var allTranslationsKeys = [
			['1 right answer', '{0} right answers (plural: 2-4)', '{0} right answers (plural: 0,5-Infinite)'],
			['1 wrong answer', '{0} wrong answers (plural: 2-4)', '{0} wrong answers (plural: 0,5-Infinite)']
		];
		var translationsKeys = correctAnswer ? allTranslationsKeys[0] : allTranslationsKeys[1];
		var translations = this._statistics.Translations;
		if (value === 1) {
			return translations[translationsKeys[0]];
		} else if (value > 1 && value < 5) {
			return String.format(translations[translationsKeys[1]], value);
		} else {
			return String.format(translations[translationsKeys[2]], value);
		}
	}
});