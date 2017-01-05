Ext.define('App.view.Layout', {
	extend: 'Ext.tab.Panel',
	requires: [
		'App.view.Tab'
	],
	layout: 'fit',
	minHeight: 300,
	border: false,
	tabPosition: 'left',
	tabRotation: 0,
	border: false,
	defaults: {
		bodyPadding: 10,
		scrollable: false,
		closable: false,
		border: false
	},
	initComponent: function () {
		var oneTabHeight = 40.7,
			allTabsHeight = 0,
			questions = App.libs.Config.Questions;
		allTabsHeight = questions.length * oneTabHeight;
		this.minHeight = Math.max(allTabsHeight, this.minHeight);
		this.callParent();
	}
});