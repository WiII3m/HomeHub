window.HttpClient = (function() {
    'use strict';

    var defaults = {
        timeout: 10000,
        retries: 2,
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    };

    function createRequest(method, url, options) {
        options = options || {};

        return new Promise(function(resolve, reject) {
            var xhr = window.DashboardUtils ? window.DashboardUtils.createXHR() : new XMLHttpRequest();
            var timeout = options.timeout || defaults.timeout;

            xhr.open(method.toUpperCase(), url, true);
            xhr.timeout = timeout;

            var headers = PolyfillUtils.mergeObjects(defaults.headers, options.headers || {});
            for (var key in headers) {
                if (headers.hasOwnProperty(key)) {
                    xhr.setRequestHeader(key, headers[key]);
                }
            }

            var csrfToken = window.DashboardUtils ? window.DashboardUtils.getCsrfToken() : null;
            if (csrfToken) {
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            }

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        var response = parseResponse(xhr);
                        resolve(response);
                    } else {
                        var error = createError(xhr, url, method);
                        reject(error);
                    }
                }
            };

            xhr.ontimeout = function() {
                reject(createError(xhr, url, method, 'Request timeout'));
            };

            xhr.onerror = function() {
                reject(createError(xhr, url, method, 'Network error'));
            };

            var body = prepareBody(options.data, headers['Content-Type']);
            xhr.send(body);
        });
    }

    function parseResponse(xhr) {
        var contentType = xhr.getResponseHeader('Content-Type') || '';
        var responseText = xhr.responseText;

        if (contentType.indexOf('application/json') !== -1) {
            return PolyfillUtils.safeJsonParse(responseText, { error: 'Invalid JSON' });
        }

        return responseText;
    }

    function createError(xhr, url, method, message) {
        var error = new Error(message || 'HTTP Error');
        error.status = xhr.status;
        error.statusText = xhr.statusText;
        error.url = url;
        error.method = method;
        error.response = xhr.responseText;

        if (xhr.responseText) {
            var errorData = PolyfillUtils.safeJsonParse(xhr.responseText);
            if (errorData && errorData.error) {
                error.message = errorData.error;
            }
        }

        return error;
    }

    function prepareBody(data, contentType) {
        if (!data) {
            return null;
        }

        if (typeof data === 'string') {
            return data;
        }

        if (!contentType || contentType.indexOf('application/json') !== -1) {
            return JSON.stringify(data);
        }

        if (contentType === 'application/x-www-form-urlencoded') {
            var params = [];
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    params.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
                }
            }
            return params.join('&');
        }

        return data;
    }

    function get(url, options) {
        return createRequest('GET', url, options);
    }

    function post(url, data, options) {
        options = options || {};
        options.data = data;

        if (!options.headers) {
            options.headers = {};
        }
        if (!options.headers['Content-Type']) {
            options.headers['Content-Type'] = 'application/json';
        }

        return createRequest('POST', url, options);
    }

    function put(url, data, options) {
        options = options || {};
        options.data = data;

        if (!options.headers) {
            options.headers = {};
        }
        if (!options.headers['Content-Type']) {
            options.headers['Content-Type'] = 'application/json';
        }

        return createRequest('PUT', url, options);
    }

    function del(url, options) {
        return createRequest('DELETE', url, options);
    }

    function requestWithRetry(method, url, options, retriesLeft) {
        retriesLeft = retriesLeft !== undefined ? retriesLeft : (options && options.retries || defaults.retries);

        return createRequest(method, url, options).catch(function(error) {
            if (retriesLeft > 0 && error.status >= 500) {
                return new Promise(function(resolve) {
                    setTimeout(function() {
                        resolve(requestWithRetry(method, url, options, retriesLeft - 1));
                    }, 1000);
                });
            }
            throw error;
        });
    }

    return {
        get: get,
        post: post,
        put: put,
        delete: del,
        request: createRequest,
        requestWithRetry: requestWithRetry,

        setDefaults: function(newDefaults) {
            defaults = PolyfillUtils.mergeObjects(defaults, newDefaults);
        }
    };
})();
