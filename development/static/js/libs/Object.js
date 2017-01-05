(function () {
    if (!Object.clone) {
        var cloner = {
            primitives: {
                'Boolean'     : 1,
                'Null'        : 1,
                'Undefined'   : 1,
                'Number'      : 1,
                'String'      : 1,
                'Symbol'      : 1
            },
            customCloners: {
                'Function'          : 'cloneFunction',
                'Date'              : 'cloneDate',
                'RegExp'            : 'cloneRegExp'
            },
            clone: function (source) {
                var type = cloner.getType(source);
                if (type in cloner.primitives) {
                    return source;
                } else if (type in cloner.customCloners) {
                    return cloner.customCloners[type](source);
                } else {
                    return cloner.cloneArrayOrObject(source, type);
                }
            },
            getType: function (source) {
                var type = Object.prototype.toString.apply(source);
                return type.substr(8, type.length - 9);
            },
            cloneFunction: function (source) {
                var name = source.prototype.constructor.name,
                    fnSrc = source.toString(),
                    argsBegin = fnSrc.indexOf('('),
                    argsEnd = fnSrc.indexOf('{'),
                    argsStr = fnSrc.substr(argsBegin, argsEnd - argsBegin),
                    bodyPart = fnSrc.substr(argsEnd);
                return new Function('return function ' + name + argsStr + bodyPart)();
            },
            cloneDate: function (source) {
                var r = new Date();
                r.setTime(source.getTime());
                return r;
            },
            cloneRegExp: function (source) {
                return new RegExp(source.source, source.flags);
            },
            cloneArrayOrObject: function (source, type) {
                if (type.substr(type.length - 5) == 'Array') {
                    // Array, ArrayBuffer, Int8Array, Uint8Array, Uint8ClampedArray, Int16Array, 
                    // Uint16Array, Int32Array, Uint32Array, Float32Array, Float64Array
                    return source.slice(0);
                } else {
                    return Object.assign(
                        Object.create(
                            source.__proto
                        ), 
                        source
                    );
                }
            }
        };
        Object.clone = cloner.clone;
    }
})();