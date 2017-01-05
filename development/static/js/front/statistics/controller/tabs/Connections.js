Ext.define('App.controller.tabs.Connections', {
	extend: 'App.controller.Tab',
	requires: [],
	/*****************************************************************************************************************/
	init: function () {
		this.callParent(arguments);
	},
	onLaunch: function () {
		this.deelay(function () {
			this.renderSummaryIfAnyData();
			this.deelay(function () {
				this._renderPresentedOptionsOrAnswersCountsGraphIfAnyData({
					dataSourceKey: 'PresentedOptionsCounts',
					height: 400,
					translationKeyVerticalTitleCount: 'Answers counts, where option was presented',
					translationKeyVerticalTitlePercentage: 'Answers percentages, where option was presented',
					translationKeyTooltipPresentedValue: 'Question Option',
					translationKeyBottomAxeTitle: 'Question Options'
				});
				this.deelay(function () {
					this._renderPresentedOptionsOrAnswersCountsGraphIfAnyData({
						dataSourceKey: 'PresentedAnswersCounts',
						height: 500,
						translationKeyVerticalTitleCount: 'Any presented option count',
						translationKeyVerticalTitlePercentage: 'Any presented option percentage',
						translationKeyTooltipPresentedValue: 'Question Answer',
						translationKeyBottomAxeTitle: 'Question Answers'
					});
					this.deelay(function () {
						this._renderConnectionsCorrectnessGraph();
						this.deelay(function () {
							this._renderMostOfftenConnectionsGraph();
							this.deelay(function () {
								this._renderPeopleCorrectnessGraph();
								this.deelay(function () {
									this._renderPersonsAnswersCountsGraph();
									this.deelay(function () {
										this._renderCorrectAnswersGraphIfAnyData();
										this.deelay(function () {
											this.renderInvolvedGraphIfAnyData();
										});
									});
								});
							});
						});
					});
				});
			});
		});
		this.callParent(arguments);
	},
	_renderPresentedOptionsOrAnswersCountsGraphIfAnyData: function (cfg) {
		var data = this._statistics[cfg.dataSourceKey],
			countsDisplaying = data.length > 0 && typeof (data[0].Count) == 'number',
			verticalColumn = countsDisplaying ? 'Count' : 'Percentage',
			translations = this._statistics.Translations,
			verticalTitle = translations[countsDisplaying ? cfg.translationKeyVerticalTitleCount : cfg.translationKeyVerticalTitlePercentage],
			presentedValue = translations[cfg.translationKeyTooltipPresentedValue],
			verticalLegendTmpl = countsDisplaying ? '0' : '0 %',
			graphMaximum = 0,
			majorTickSteps = 0,
			store = {},
			chart = {};
		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			this.truncateDataTextField(data, 'Value', 30, 'OriginalValue');
			store = Ext.create('App.store.charts.SingleDimension', {
				data: data
			});
			graphMaximum = store.max(verticalColumn);
			majorTickSteps = countsDisplaying ? graphMaximum : Math.round(graphMaximum / 10);
			chart = Ext.create('App.view.charts.SingleColumns', {
				store: store,
				theme: 'YellowCustom',
				height: cfg.height,
				axes: [{
					maximum: store.max(verticalColumn),
					fields: verticalColumn,
					title: {
						fontSize: '13px',
						text: verticalTitle
					},
					majorTickSteps: majorTickSteps,
					renderer: function (axis, label, layoutContext) {
						return Ext.util.Format.number(layoutContext.renderer(label), verticalLegendTmpl);
					}
				}, {
					fields: 'Value',
					title: translations[cfg.translationKeyBottomAxeTitle],
					renderer: function (axis, labelValue, layoutContext, lastLabel) {
						return labelValue;
					}
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
								presentedValue + ": " + record.get('OriginalValue'),
								verticalTitle + ': '
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
					}
				}]
			});
			this.$$view.addGraphHeading(this._statistics.Translations[cfg.dataSourceKey]);
			this.$$view.content.add(chart);
		} else {
			this.$$view.addNoDataMsg();
		}
	},
	_renderMostOfftenConnectionsGraph: function () {
		var data = this._statistics.MostOfftenConnections,
			countsDisplaying = (data.length > 0 && typeof (data[0].Count) == 'number'),
			verticalColumn = countsDisplaying ? 'Count' : 'Percentage',
			translations = this._statistics.Translations,
			verticalTitle = translations[countsDisplaying ? 'Answered Connections Counts' : 'Answered Connections Percentages'],
			questionOption = translations['Question Option'],
			questionAnswer = translations['Question Answer'],
			verticalLegendTmpl = (countsDisplaying ? '0' : '0 %'),
			graphMax = 0,
			majorTickSteps = 0,
			store = {},
			chart = {};
		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			this.truncateDataTextField(data, 'Value', 30, 'OriginalValue');
			store = Ext.create('App.store.charts.SingleDimension', {
				data: data
			});
			graphMax = store.max(verticalColumn);
			majorTickSteps = countsDisplaying ? graphMax : Math.round(graphMax / 10);
			chart = Ext.create('App.view.charts.SingleColumns', {
				store: store,
				theme: 'OrangeGreenRed',
				height: 500,
				axes: [{
					maximum: graphMax,
					fields: verticalColumn,
					title: verticalTitle,
					majorTickSteps: majorTickSteps,
					renderer: function (axis, label, layoutContext, lastLabel) {
						return Ext.util.Format.number(layoutContext.renderer(label), verticalLegendTmpl);
					},
				}, {
					fields: 'Value',
					title: translations['Most Often Answered Connections'],
					renderer: function (axis, labelValue, layoutContext, lastLabel) {
						return labelValue;
					}
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
								questionOption + ": ",
								record.get('Option'),
								questionAnswer + ": ",
								record.get('Answer'),
								verticalTitle + ': '
							];
							if (countsDisplaying) {
								codeParts[4] += String(record.get('Count')) + ' (' + Ext.util.Format.number(record.get('Percentage'), '0.0 %') + ')';
							} else {
								codeParts[4] += Ext.util.Format.number(record.get('Percentage'), '0.0 %');
							}
							tooltip.setHtml(codeParts.join("<br />"));
						}
					},
					renderer: function (sprite, config, stores, index) {
						var orangeGreenRed = Ext.chart.theme.OrangeGreenRed.Colors,
							record = stores.store.getAt(index),
							recordData = record.data,
							correct = -1,
							colorIndex = 0;
						if (typeof (recordData['Correct']) == 'number') {
							correct = recordData['Correct'];
							colorIndex = correct === 1 ? 1 : (correct === -1 ? 0 : 2);
							config.fillStyle = orangeGreenRed[colorIndex];
						}
						return config;
					}
				}]
			});
			this.$$view.addGraphHeading(translations.MostOfftenConnections);
			this.$$view.content.add(chart);
		}
	},
	_renderPersonsAnswersCountsGraph: function () {
		var data = this._statistics.PersonsAnswersCounts,
			countsDisplaying = (data.length > 0 && typeof (data[0].Count) == 'number'),
			verticalColumn = countsDisplaying ? 'Count' : 'Percentage',
			translations = this._statistics.Translations,
			verticalTitle = translations[countsDisplaying ? 'Answered Connections Counts' : 'Answered Connections Percentages'],
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
			majorTickSteps = countsDisplaying ? graphMax : 5;
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
					title: translations['Connections Counts In Answer']
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
			this.$$view.addGraphHeading(translations.PersonsAnswersCounts);
			this.$$view.content.add(chart);
		}
	},
	_renderConnectionsCorrectnessGraph: function () {
		var data = this._statistics.ConnectionsCorrectness,
			min = 0, max = 0, diff = 0,
			translations = this._statistics.Translations,
			questionOption = translations['Question Option'],
			questionAnswer = translations['Question Answer'],
			correctlyAnsweredRespondents = translations['Correctly answered respondents'],
			incorrectlyAnsweredRespondents = translations['Incorrectly answered respondents'],
			store = {},
			chart = {};
		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			this.truncateDataTextField(data, 'Value', 30, 'OriginalValue');
			// switch first z-level keyed as 'Count0' into negative values to display columns in negative position
			for (var i = 0, l = data.length; i < l; i += 1) {
				data[i].Count0 = data[i].Count0 > 0 ? data[i].Count0 * (-1) : data[i].Count0;
				data[i].Percentage0 = data[i].Percentage0 > 0 ? data[i].Percentage0 * (-1) : data[i].Percentage0;
			}
			store = Ext.create('App.store.charts.MultipleDimensions', {
				data: data
			});
			min = Math.round(store.min('Count0') * 1.6);
			max = Math.round(store.max('Count1') * 1.5);
			diff = max - min;
			chart = Ext.create('App.view.charts.MultiColumns', {
				store: store,
				height: 700,
				axes: [{
					title: {
						text: translations['Number Of Correct And Incorrect Respondents'],
						fontSize: '11px'
					},
					minimum: min,
					maximum: max,
					majorTickSteps: Math.round(Math.sqrt(diff)),
					renderer: function (axis, label, layoutContext) {
						return Ext.util.Format.number(layoutContext.renderer(Math.abs(label)), '0');
					}
				}, {
					title: translations['Options Connections'],
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
							tooltip.setHtml([
								questionOption + ": ",
								record.get('Option'),
								questionAnswer + ": ",
								record.get('Answer'),
								correctlyAnsweredRespondents + ': ' + record.get('Label1'),
								incorrectlyAnsweredRespondents + ': ' + record.get('Label0')
							].join("<br />"));
						}
					}
				}]
			});
			this.$$view.addGraphHeading(translations.ConnectionsCorrectness);
			this.$$view.content.add(chart);
		}
	},
	_renderPeopleCorrectnessGraph: function () {
		var rawData = this._statistics.PeopleCorrectness,
			countsDisplaying = Tools.TypeOf(rawData) == 'Array' && rawData.length > 0 && typeof (rawData[0].Count) == 'number',
			rawItem = {},
			data = [],
			translations = this._statistics.Translations,
			verticalTitle = translations[countsDisplaying ? 'Respondents Counts' : 'Respondents Percentages'],
			valueTmpl = '',
			min = 0, max = 0, diff = 0,
			store = {},
			chart = {};
		if (Tools.TypeOf(rawData) == 'Array' && rawData.length > 0) {
			// switch first z-level keyed as 'Count0' into negative values to display columns in negative position
			for (var i = 0, l = rawData.length; i < l; i += 1) {
				rawItem = rawData[i];
				data.push({
					OriginalValue: rawItem.Value,
					Value: this._translateCorrectAnswers(rawItem.Value),
					Count: rawItem.Count,
					Label: rawItem.Label
				});
			}
			store = Ext.create('App.store.charts.SingleDimension', {
				data: data
			});
			max = store.max('Count');
			chart = Ext.create('App.view.charts.SingleColumns', {
				store: store,
				theme: 'Sky',
				axes: [{
					title: translations['Respondents Counts'],
					maximum: max,
					majorTickSteps: Math.round(Math.sqrt(max)),
					renderer: function (axis, label, layoutContext) {
						return Ext.util.Format.number(layoutContext.renderer(label), '0');
					},
				}, {
					fields: 'Value',
					title: {
						text: translations['Correctly Connected Options Count'],
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
					tooltip: {
						renderer: function (tooltip, record, item) {
							var codeParts = [
								record.get('Value'),
								verticalTitle + ': '
							];
							if (countsDisplaying) {
								codeParts[1] += String(record.get('Count')) + ' (' + Ext.util.Format.number(record.get('Percentage'), '0.0 %') + ')';
							} else {
								codeParts[1] += Ext.util.Format.number(record.get('Percentage'), '0.0 %');
							}
							tooltip.setHtml(codeParts.join("<br />"));
						}
					},
					renderer: function (sprite, config, stores, index) {
						var record = stores.store.getAt(index);
						var blueAndRedColors = Ext.chart.theme.BlueRed.Colors;
						if (record.data.OriginalValue === 0) {
							return { fillStyle: blueAndRedColors[1] };
						}
						return config;
					}
				}]
			});
			this.$$view.addGraphHeading(translations.PeopleCorrectness);
			this.$$view.content.add(chart);
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
},
	_translateCorrectAnswers: function (value) {
		var translationsKeys = ['1 right answer', '{0} right answers (plural: 2-4)', '{0} right answers (plural: 0,5-Infinite)'];
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