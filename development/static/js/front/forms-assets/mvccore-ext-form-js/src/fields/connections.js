(function (MvcCoreForm) {
	MvcCoreForm['Connections'] = function (name, required) {
		this['Name'] = name;
		this._base = null;
		this._required = !!required;
		this._fields = [];
		this._errors = {};
	};
	MvcCoreForm['Connections'].prototype = {
		'Init': function (base) {
			var scope = this,
				addEvent = base.AddEvent,
				fields = base.Form[scope['Name']],
				index = 0, 
				length = fields['length'];
			scope._base = base;
			scope._fields = fields;
			for (; index < length; index += 1) {
				addEvent(fields[index], 'change', function () {
					scope.changeHandler();
				});
			};
			scope.changeHandler();
		},
		changeHandler: function () {
			var scope = this;
			scope.completeErrors();
			scope.setUpErrorClasses();
		},
		completeErrors: function () {
			var scope = this,
				fields = scope._fields,
				field = {},
				lengthStr = 'length',
				errors = {},
				values = {},
				value = '',
				intValue = 0,
				intsRegExp = /[^0-9]/g;
			for (var i = 0, l = fields[lengthStr]; i < l; i += 1) {
				field = fields[i];
				value = field['value']['replace'](intsRegExp, '');
				intValue = parseInt(value, 10);
				if (value[lengthStr] > 0 && !(intValue > 0 && intValue < l + 1)) {
					errors[i] = true;
				} else if (value[lengthStr] > 0 && typeof(values[value]) == 'undefined') {
					values[value] = i;
				} else if (value[lengthStr] > 0) {
					errors[i] = true;
					errors[values[value]] = true;
				}
			}
			scope._errors = errors;
		},
		setUpErrorClasses: function () {
			var scope = this,
				fields = scope._fields,
				field = {},
				label = {};
			for (var i = 0, l = fields['length']; i < l; i += 1) {
				field = fields[i];
				label = field['parentNode'];
				if (scope._errors[i]) {
					scope.addOrRemoveErrorClass(field, true);
					scope.addOrRemoveErrorClass(label, true);
				} else {
					scope.addOrRemoveErrorClass(field, false);
					scope.addOrRemoveErrorClass(label, false);
				}
			}
		},
		addOrRemoveErrorClass: function (element, add) {
			var base = this._base,
				fn = add ? base.AddCls : base.RemoveCls;
			return fn(element, 'error');
		}
	}
})(window['MvcCoreForm']);