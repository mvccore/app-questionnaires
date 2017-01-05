Ext.define('App.controller.tabs.Textarea', {
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
				this.renderTextsSummaryGraphOrGrid(this._statistics.Overview, this._statistics.Translations.Overview);
				this.deelay(function () {
					this.renderInvolvedGraphIfAnyData();
				});
			});
		});
		this.callParent(arguments);
	}
});