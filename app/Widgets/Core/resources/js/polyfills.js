window.PolyfillUtils = (function() {
    'use strict';

    function copyObject(source) {
        var copy = {};
        for (var key in source) {
            if (source.hasOwnProperty(key)) {
                copy[key] = source[key];
            }
        }
        return copy;
    }

    function mergeObjects(target, source) {
        var merged = copyObject(target);
        for (var key in source) {
            if (source.hasOwnProperty(key)) {
                merged[key] = source[key];
            }
        }
        return merged;
    }

    function forEach(array, callback) {
        for (var i = 0; i < array.length; i++) {
            callback(array[i], i, array);
        }
    }

    function find(array, predicate) {
        for (var i = 0; i < array.length; i++) {
            if (predicate(array[i], i, array)) {
                return array[i];
            }
        }
        return undefined;
    }

    function filter(array, predicate) {
        var result = [];
        for (var i = 0; i < array.length; i++) {
            if (predicate(array[i], i, array)) {
                result.push(array[i]);
            }
        }
        return result;
    }

    function generateId() {
        return 'widget_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    function safeJsonParse(jsonString, defaultValue) {
        try {
            return JSON.parse(jsonString);
        } catch (error) {

            return defaultValue || null;
        }
    }

    function isEmpty(obj) {
        if (!obj || typeof obj !== 'object') {
            return true;
        }
        for (var key in obj) {
            if (obj.hasOwnProperty(key)) {
                return false;
            }
        }
        return true;
    }

    function capitalize(str) {
        if (!str || typeof str !== 'string') {
            return '';
        }
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    function timeAgo(timestamp) {
        var now = Date.now();
        var diff = now - timestamp;
        var seconds = Math.floor(diff / 1000);
        var minutes = Math.floor(seconds / 60);
        var hours = Math.floor(minutes / 60);
        var days = Math.floor(hours / 24);

        if (days > 0) {
            return days + ' jour' + (days > 1 ? 's' : '');
        } else if (hours > 0) {
            return hours + ' heure' + (hours > 1 ? 's' : '');
        } else if (minutes > 0) {
            return minutes + ' minute' + (minutes > 1 ? 's' : '');
        } else {
            return 'maintenant';
        }
    }

    return {

        copyObject: copyObject,
        mergeObjects: mergeObjects,

        forEach: forEach,
        find: find,
        filter: filter,

        generateId: generateId,
        safeJsonParse: safeJsonParse,
        isEmpty: isEmpty,
        capitalize: capitalize,
        timeAgo: timeAgo
    };
})();
