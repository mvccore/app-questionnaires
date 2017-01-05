Ext.define('App.controller.tabs.BooleanAndText', {
	extend: 'App.controller.Tab',
	//requires: [],
	/*****************************************************************************************************************/
	onLaunch: function () {
		this.deelay(function () {
			if (Tools.TypeOf(this._statistics.Summary) == 'Array') {
				this.renderSummaryIfAnyData();
			} else {
				this.$$view.addGraphHeading(this._statistics.Translations.Summary);
			}
			this.deelay(function () {
				this.renderBooleanGraph(this._statistics.Overview);
				this.deelay(function () {
					this.renderTextsSummaryGraphOrGrid(this._statistics.AllTextAnswers, this._statistics.Translations.AllTextAnswers);
				});
			});
		});
		this.callParent(arguments);
	}
});