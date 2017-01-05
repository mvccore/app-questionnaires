Ext.define('App.controller.tabs.Boolean', {
	extend: 'App.controller.Tab',
	/*requires: [
		'App.store.charts.SingleDimension',
		'App.view.charts.Pie'
	],*/
	onLaunch: function () {
		this.deelay(function () {
			if (Tools.TypeOf(this._statistics.Summary) == 'Array') {
				this.renderSummaryIfAnyData();
			} else {
				this.$$view.addGraphHeading(this._statistics.Translations.Summary);
			}
			this.deelay(function () {
				this.renderBooleanGraph(this._statistics.Overview);
			});
		});
		
		this.callParent(arguments);
	}
});