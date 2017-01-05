Ext.define('App.controller.Tab', {
	extend: 'Ext.app.Controller',
	requires: [],
	/*****************************************************************************************************************/
	statics: {
		TEXT_QUANTITY_TYPES: {
			GRAPH: {
				MAX_CHARS: 15,
				MAX_ROWS: 8
			},
			GRID: {
				MAX_CHARS: Infinity,
				MAX_ROWS: Infinity,
				ITEMS_PER_SCREEN: 20
			}
		}
	},
	_question: {},
	_statistics: {},
	_textsQuantityType: '',
	constructor: function (config) {
		this._question = config.question;
		this._statistics = config.statistics;
		this.callParent(arguments);
	},
	init: function () {
		this.callParent(arguments);
	},
	onLaunch: function () {
		this.callParent(arguments);
	},
	deelay: function (cb) {
		setTimeout(cb.bind(this), 1);
	},
	renderSummaryIfAnyData: function (headingText) {
		var headingText = headingText || this._statistics.Translations.Summary,
			data = this._statistics.Summary;
		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			this.$$view.addGraphHeading(headingText);
			this.$$view.content.add({
				xtype: 'container',
				html: this.$$view.completeSummaryTableCode(data)
			});
		}
	},
	renderTextsSummaryGraphOrGrid: function (data, graphHeadingText) {
		this._textsQuantityType = this.determinateTextsQuantityType(data);
		if (this._textsQuantityType == 'GRAPH') {
			this._renderTextsGraphIfAnyData(data, graphHeadingText);
		} else if (this._textsQuantityType == 'GRID') {
			this._renderTextsGridIfAnyData(data, graphHeadingText);
		}
	},
	renderInvolvedGraphIfAnyData: function () {
		var data = this._statistics.Involved,
			store = {},
			chart = {};

		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			data = this._statistics.Involved;
			store = Ext.create('App.store.charts.SingleDimension', {
				data: data
			});
			chart = Ext.create('App.view.charts.Pie', {
				store: store,
				theme: 'BlueRed'
			});
			this.$$view.addGraphHeading(this._statistics.Translations.Involved);
			this.$$view.content.add(chart);
		}
	},
	renderBooleanGraph: function (data) {
		var totalPercentCnt = 0,
			store,
			chart;
		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			for (var i = 0, l = data.length; i < l; i += 1) {
				data[i].Label = data[i].Label.replace(/ /g, '\n');
			}
			store = Ext.create('App.store.charts.SingleDimension', {
				data: data
			});
			chart = Ext.create('App.view.charts.Pie', {
				store: store,
				theme: 'GreenRedGray'
			});
			this.$$view.content.add(chart);
		}
	},
	truncateDataTextField: function (data, truncateField, truncateLength, originalField) {
		var before = '',
			after = '',
			lastSpaceIndex = 0;
		for (var i = 0, l = data.length; i < l; i += 1) {
			before = String(data[i][truncateField]).replace(/\n/g, ' ');
			after = before + ' ';
			lastSpaceIndex = after.lastIndexOf(' ', truncateLength);
			if (lastSpaceIndex === -1) continue;
			after = after.substr(0, lastSpaceIndex);
			data[i][truncateField] = before != after ? after + '...' : before;
			data[i][originalField] = before;
		}
	},
	determinateTextsQuantityType: function (data, textFieldName) {
		var textFieldName = textFieldName || 'Value',
			textQuantityTypes = App.controller.Tab.TEXT_QUANTITY_TYPES,
			textQuantityType = {},
			dataRowsCount = data.length,
			dataItemTextLength = 0,
			dataMaxChars = 0,
			result = '';
		for (var i = 0, l = data.length; i < l; i += 1) {
			dataItemTextLength = String(data[i][textFieldName]).length;
			if (dataItemTextLength > dataMaxChars) dataMaxChars = dataItemTextLength;
		}
		textQuantityType = textQuantityTypes.GRAPH;
		if (dataMaxChars < textQuantityType.MAX_CHARS && dataRowsCount < textQuantityType.MAX_ROWS) {
			result = 'GRAPH';
		} else {
			result = 'GRID';
		}
		return result;
	},
	_renderTextsGraphIfAnyData: function (data, graphHeadingText) {
		var store = Ext.create('App.store.charts.SingleDimension', {
				data: data
			}),
			countsDisplaying = data.length > 0 && typeof (data[0].Count) == 'number',
			verticalColumn = countsDisplaying ? 'Count' : 'Percentage',
			verticalTitle = this._statistics.Translations[countsDisplaying ? 'Respondents Counts' : 'Respondents Percentages'],
			verticalLegendTmpl = countsDisplaying ? '0.0' : '0 %',
			graphMaximum = store.max(verticalColumn),
			majorTickSteps = countsDisplaying ? Math.round(Math.sqrt(graphMaximum)) : 10,
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
					title: this._statistics.Translations['Answered Texts']
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
					renderer: function (sprite, config, stores, index) {
						var colors = Ext.chart.theme.GreenRedGray.Colors,
							record = stores.store.getAt(index),
							recordData = record.data;
						if (typeof (recordData.Correct) == 'boolean') {
							if (recordData.Correct) {
								return { fillStyle: colors[0] };
							} else {
								return { fillStyle: colors[1] };
							}
						}
						return config;
					}
				}]
			});
			this.$$view.addGraphHeading(graphHeadingText);
			this.$$view.content.add(chart);
		} else {
			this.$$view.addNoDataMsg();
		}
	},
	_renderTextsGridIfAnyData: function (data, graphHeadingText) {
		var itemsPerScreen = App.controller.Tab.TEXT_QUANTITY_TYPES.GRID.ITEMS_PER_SCREEN,
			store = Ext.create('App.store.charts.SingleDimension', {
				data: data,
				pageSize: itemsPerScreen
			}),
			translations = this._statistics.Translations,
			countsDisplaying = Tools.TypeOf(data) == 'Array' && data.length > 0 && typeof (data[0].Count) == 'number',
			gridColumnsConfig = [
				{ text: translations['Answered Texts'], flex: 1, dataIndex: 'Value', sortable: true, align: 'left' },
				{ text: translations['Counts'], width: 90, dataIndex: 'Count', sortable: true, align: 'right' },
				{ text: translations['Percentages'], width: 100, dataIndex: 'Percentage', sortable: true, align: 'right', renderer: function (value) {
					return value + ' %';
				}}
			],
			gridConfig = {},
			grid = {};
		if (Tools.TypeOf(data) == 'Array' && data.length > 0) {
			if (!countsDisplaying) {
				gridColumnsConfig = [gridColumnsConfig[0], gridColumnsConfig[2]];
			} else if (data.length > 0 && typeof (data[0].Correct) == 'boolean') {
				gridColumnsConfig.push({ text: translations['Correct'], width: 100, dataIndex: 'Correct', sortable: true, align: 'right', renderer: function (value) {
					return value ? translations['Yes'] : translations['No'];
				}});
			}
			gridConfig = {
				bufferedRenderer: true,
				store: store,
				columns: gridColumnsConfig,
				forceFit: true,
				//height: 210,
				margin: '0 25 25 25',
				split: true,
				region: 'center'
			};
			if (data.length > itemsPerScreen) {
				/*gridConfig.dockedItems = [{
					xtype: 'pagingtoolbar',
					store: store,   // same store GridPanel is using
					dock: 'bottom',
					displayInfo: true
				}];
				*/
				gridConfig.height = ((itemsPerScreen + 1) * 34);
			};
			grid = Ext.create('Ext.grid.Panel', gridConfig);
			this.$$view.addGraphHeading(graphHeadingText);
			this.$$view.content.add(grid);
		} else {
			this.$$view.addNoDataMsg();
		}
	}
});