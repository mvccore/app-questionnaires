Ext.define('App.view.tabs.Content', {
	extend: 'Ext.panel.Panel',
	require: [
		// 'Ext.container.Container',
		// 'App.store.charts.SingleDimension',
		// 'App.view.charts.Pie',
	],
	layout: 'fit',
	cls: 'question-statistics-tab',
	border: false,
	_question: {},
	_statistics: {},
	content: null,
	constructor: function (config) {
		this._question = config.question;
		this._statistics = config.statistics;
		this.callParent(arguments);
	},
	initComponent: function () {
		this.content = Ext.create('Ext.container.Container', {
			layout: {
				type: 'vbox',
				pack: 'start',
				align: 'stretch'
			}
		});
		this.header = {
			cls: 'question-statistics-tab-header',
			title: String.format(
				"{0} <i>({1})</i>",
				String.format(
					t('{0}. Question'),
					this._question.Id + 1
				),
				this._question.Required ? t("required") : t("not required")
			),
			padding: 15
		};
		this.items = [{
			xtype: 'container',
			cls: 'question-statistics-tab-content',
			layount: 'fit',
			items: [
				{
					xtype: 'container',
					html: [
						String.format(
							'<h3>{0}</h3>',
							this._question.Text
						),
						'<div class="hr"></div>'
					].join('')
				},
				this.content
			]
		}];
		this.callParent(arguments);
	},
	addGraphHeading: function (headingText) {
		this.content.add({
			xtype: 'container',
			html: String.format('<h4>{0}</h4>', headingText)
		});
	},
	completeSummaryTableCode: function (summaryData) {
		var tableTmpl = '<table class="summary"><tbody>{0}</tbody></table>';
		var rowTmpl = '<tr><td class="label">{0}</td><th class="count">{1}</th><td class="percentage">{2}</td></tr>';
		var rows = [];
		for (var i = 0, l = summaryData.length; i < l; i += 1) {
			rows.push(String.format(
				rowTmpl, summaryData[i][0], summaryData[i][1], typeof(summaryData[i][2]) != 'undefined' ? summaryData[i][2] + ' %' : ''
			));
		}
		return String.format(tableTmpl, rows.join(''));
	},
	addNoDataMsg: function () {
		this.content.add({
			xtype: 'container',
			html: String.format(
				'<div class="no-graph-data-msg">{0}</div>',
				String(t('No graph data msg')).replace("\n", '<br />')
			)
		});
	}
});