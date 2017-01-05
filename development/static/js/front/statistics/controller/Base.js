Ext.define('App.controller.Base', {
	extend: 'Ext.app.Controller',
	/*requires: [
		'App.view.Layout',
		'App.view.Tab',
		'App.view.tabs.Content',
		'App.controller.tabs.*'
	],*/
	/*****************************************************************************************************************/
	$tabs: null,
	$form: null,
	$submit: null,
	_loading: null,
	_idQuestionnaire: -1,
	_questions: [],
	_formSubmitMethod: '',
	_statisticsUrl: '',
	_currentQuestion: 0,
	_requestInProcess: false,
	_requestQueue: [],
	_hashRegExpBody: '^{0}\-([0-9]*)$',
	_hashRegExp: null,
	_locationHashTmpl: '{0}-{1}',
	init: function () {
		this.callParent(arguments);
		this._startupChechIfAppIsNotInIframe();
	},
	onLaunch: function () {
		this._initBaseProperties();
		this._parseLocationHashTabIndex();
		this._initHtmlFormElmsAndEvents();
		this._initTabsView();
		this._initWindowOnHashChangeEvent();
		this.callParent(arguments);
	},
	/*****************************************************************************************************************/
	_initBaseProperties: function () {
		var cfg = App.libs.Config;
		this._questions = cfg.Questions;
		this._statisticsUrl = cfg.StatisticsUrl;
		this._locationHashTmpl = String.format(this._locationHashTmpl, t('question-tab'), '{0}');
		this._hashRegExpBody = String.format(this._hashRegExpBody, t('question-tab'));
		this._hashRegExp = new RegExp(this._hashRegExpBody, 'g');
	},
	_initTabsView: function () {
		this.$tabs = Ext.create('App.view.Layout', {
			renderTo: App.libs.Config.ContainerId
		});
		for (var i = 0, l = this._questions.length; i < l; i += 1) {
			this._addTab(i);
		}
		// render first tab manualy - not standardly first one by boxready event
		if (this._currentQuestion > 0) {
			this.$tabs.setActiveTab(this._currentQuestion);
		} else {
			this._tabOnBoxReadyHandler(0, this.$tabs.items.items[0]);
		}
	},
	_addTab: function (questionIndex) {
		var question = this._questions[questionIndex];
		var tab = Ext.create('App.view.Tab', {
			question: question,
			index: questionIndex,
			questionsCount: this._questions.length,
			listeners: {
				/*boxready: function (tab, width, height, eOpts) {
					this._tabOnBoxReadyHandler(questionIndex, tab);
					location.hash = String.format(this._locationHashTmpl, questionIndex + 1);
				}.bind(this),*/
				show: function (tab, eOpts) {
					if (!tab.initialized) this._tabOnBoxReadyHandler(questionIndex, tab);
					this._currentQuestion = questionIndex;
					location.hash = String.format(this._locationHashTmpl, questionIndex + 1);
				}.bind(this)
			}
		});
		this.$tabs.add(tab);
	},
	_initHtmlFormElmsAndEvents: function () {
		var config = App.libs.Config,
			form = document.getElementById(config.FilterFormId),
			input = {};
		for (var i = 0, l = form.length; i < l; i += 1) {
			input = form[i];
			if (input.type == 'range' || input.type == 'checkbox') {
				Ext.get(input).on('change', this._onSubmitHandler.bind(this));
			} else if (input.type == 'submit') {
				this.$submit = Ext.get(input);
			}
		}
		this.$form = Ext.get(form);
		this.$form.on('submit', this._onSubmitHandler.bind(this));
		this._formSubmitMethod = this.$form.dom.getAttribute('method');
		this.$loading = Ext.get(config.LoadingId);
	},
	_tabOnBoxReadyHandler: function (questionIndex, tab) {
		tab.initialized = true;
		this._currentQuestion = questionIndex;
		this._deelay(function () {
			this._loadQuestionStatistics(questionIndex);
		});
	},
	_onSubmitHandler: function (e, form) {
		e.preventDefault();
		this._deelay(function () {
			this._loadQuestionStatistics(this._currentQuestion);
		});
    },
    _loadQuestionStatistics: function (questionIndex) {
    	var lastIndex = 0,
    		question = {};
    	this._requestQueue.push(questionIndex);
    	if (!this._requestInProcess) {
    		this._requestInProcess = true;
    		this.$loading.removeCls('visibility-hidden');
    		this.$submit.addCls('disabled');
    		lastIndex = this._requestQueue[this._requestQueue.length - 1];
    		this._requestQueue = [];
    		question = this._questions[lastIndex];
    		Ext.Ajax.request({
    			url: this._statisticsUrl.replace('__ID_QUESTION__', question.Id),
    			method: this._formSubmitMethod,
    			params: Ext.dom.Element.serializeForm(this.$form.dom),
    			success: function (xhr, conn, options, eOpts) {
    				this._deelay(function () {
    					this._onQuestionStatisticsLoadedHandler(xhr, lastIndex);
    					this.$loading.addCls('visibility-hidden');
    					this.$submit.removeCls('disabled');
    					this._requestInProcess = false;
    					if (this._requestQueue.length > 0) {
    						lastIndex = this._requestQueue[this._requestQueue.length - 1];
    						this._requestQueue = [];
    						this._loadQuestionStatistics(lastIndex);
    					}
    				});
    			}.bind(this)
    		});
    	}
    },
    _onQuestionStatisticsLoadedHandler: function (xhr, questionIndex) {
    	var rawResult = Tools.GetEvaluated(xhr.responseText),
    		result = {};
    	if (rawResult.success) {
    		result = rawResult.data;
    		if (result.success) {
    			this._setUpQuestionTab(questionIndex, result.data);
    		}
    	}
    },
    _setUpQuestionTab: function (questionIndex, statistics) {
    	var question = this._questions[questionIndex],
    		questionTypeCamelCase = question.Type.replace(/-([a-z])/g, function (m, w) {
    			return w.toUpperCase();
    		}),
			ctrlClass = 'App.controller.tabs.' + questionTypeCamelCase.upperCaseFirst(),
    		currentTab = this.$tabs.items.getAt(questionIndex),
			tabCtrl = Ext.create(ctrlClass, {
				question: question,
				statistics: statistics
			});
    		tabContentView = Ext.create('App.view.tabs.Content', {
    			$$controller: tabCtrl,
    			question: question,
    			statistics: statistics
    		});
    	tabCtrl.$$view = tabContentView;
    	tabCtrl.init();
    	this.$tabs.setActiveTab(currentTab);
    	if (currentTab.items.length > 0) currentTab.removeAll(true);
    	currentTab.add(tabContentView);
    	tabCtrl.onLaunch();
    },
    _startupChechIfAppIsNotInIframe: function () {
    	if (window !== window.top) {
    		window.top.location.href = location.href; // ensure we are not inside an iframe
    		return false;
    	}
    	return true;
    },
    _parseLocationHashTabIndex: function () {
    	var hash = location.hash.replace('#', ''),
			indexStr = '',
			indexInt = 0;
    	if (this._hashRegExp.test(hash)) {
    		indexStr = hash.replace(this._hashRegExp, '$1');
    		indexInt = parseInt(indexStr, 10);
    		indexInt -= 1;
    		this._currentQuestion = indexInt;
    	}
    },
    _initWindowOnHashChangeEvent: function () {
    	if (Ext.feature.has.History) {
    		window.addEventListener('hashchange', Ext.bind(this._onHashChangeHandler, this));
    	} else {
    		setInterval(Ext.bind(this._onHashChangeHandler, this), 200);
    	}
    },
    _onHashChangeHandler: function () {
    	var tab;
    	this._parseLocationHashTabIndex();
    	tab = this.$tabs.items.items[this._currentQuestion];
    	if (!tab.initialized) {
    		this._tabOnBoxReadyHandler(this._currentQuestion, tab);
    	} else {
    		this.$tabs.setActiveTab(tab);
    	}
    },
    _deelay: function (cb) {
    	setTimeout(cb.bind(this), 1);
    }
});