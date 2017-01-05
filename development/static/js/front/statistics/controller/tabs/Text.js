Ext.define('App.controller.tabs.Text', {
	extend: 'App.controller.Tab',
	requires: [],
	/*****************************************************************************************************************/
	onLaunch: function () {
		this.deelay(function () {
			this.renderSummaryIfAnyData();
			this.deelay(function () {
				this.renderTextsSummaryGraphOrGrid(this._statistics.Overview, this._statistics.Translations.Overview);
				this.deelay(function () {
					if (Tools.TypeOf(this._statistics.CorrectAnswers) == 'Array') {
						this.$$view.addGraphHeading(this._statistics.Translations.CorrectAnswers);
						this.renderBooleanGraph(this._statistics.CorrectAnswers);
					}
					this.deelay(function () {
						this.renderInvolvedGraphIfAnyData();
					});
				});
			});
		});
		this.callParent(arguments);
	}
});