Ext.define('App.view.Tab', {
	extend: 'Ext.container.Container',
	requires: [
	],
	border: false,
	layout: 'fit',
	statics: {
		questionTypesIcons: {
			'boolean'			: 'fa-toggle-off',
			'boolean-and-text'	: 'fa-toggle-on',
			'text'				: 'fa-comment',
			'textarea'			: 'fa-file-text',
			'integer'			: 'fa-sort-numeric-asc',
			'float'				: 'fa-sort-numeric-asc',
			'radios'			: 'fa-dot-circle-o',
			'checkboxes'		: 'fa-check-square-o', // fa-check-circle
			'connections'		: 'fa-random'
		}
	},
	initComponent: function () {
		var question = this.initialConfig.question,
			questionNumber = this.initialConfig.index + 1,
			questionsCount = this.initialConfig.questionsCount,
			title = t('{0}. Question'),
			totalCiphers = questionsCount.toString().length,
			currentCiphers = questionNumber.toString().length,
			titlePreSpace = '';
		for (var i = 0, l = totalCiphers - currentCiphers; i < l; i += 1) {
			titlePreSpace += '&nbsp;&nbsp;';
		}
		this.title = String.format(title, titlePreSpace + questionNumber);
		this.iconCls = 'question-statistics-tab-title-icon fa ' + this.self.questionTypesIcons[question.Type];
		// this.items = []; // all items are added in 'App.view.tabs.Content' class and in tab controller class onLaunch method
		this.callParent();
	}
});