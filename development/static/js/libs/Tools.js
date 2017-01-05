Class.Define('Tools', {
    Static: {
        Isset: (function () {
            var test = function (val) {
                return (typeof (val) != 'undefined' && val !== null) ? true : false;
            };
            var isset = function (object, indexesStr) {
                var result = true;
                var arr = [object];
                var iterator = 0;
                if (typeof (indexesStr) == 'string') {
                    var indexes = indexesStr.split('.');
                }
                if (test(object)) {
                    if (typeof (indexesStr) != 'string') {
                        return test(object[indexesStr]);
                    }
                    for (var i = 0, l = indexes.length; i < l; i += 1) {
                        arr[iterator + 1] = arr[iterator][indexes[i]];
                        if (!test(arr[iterator + 1])) {
                        	result = false;
                        	break;
                        }
                        iterator += 1;
                    }
                } else {
                    result = false;
                };
                return result;
            };
            return isset;
        })(),
        TypeOf: function (o) {
            var r = Object.prototype.toString.apply(o); // "[object Something]"
            return r.substr(8, r.length - 9); // Something
        },
        JsonAttr: function (elmId) {
        	var elm = document.getElementById(elmId),
				result = {
					success: false,
					data: null,
					message: ''
				};
        	if (elm) {
        		result = this.GetEvaluated(decodeURIComponent(elm.value).replace(/%25/g, '%'));
        	} else {
        		result.message = '[Tools.JsonAttr] Element id: "' + elmId + '" not found.';
        	}
        	return result;
        },
        GetEvaluated: function (val) {
        	var result = {
        		success: false,
        		data: val,
        		message: ''
        	};
        	if (String(val).length > 0) {
        		try {
        			result.success = true;
        			result.data = (new Function('return (' + val + ');'))();
        		} catch (e) {
        			result.message = '[Tools.JsonAttr] ' + e.message;
        		}
        	} else {
        		result.message = '[Tools.JsonAttr] No data from: ' + arguments.callee.caller + '  from: ' + arguments.callee.caller.caller;
        	}
        	if (!result.success) {
        		log(result);
        	}
        	return result;
        },
    }
});