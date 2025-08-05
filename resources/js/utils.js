

window.DashboardUtils = (function() {
    'use strict';

    function showStatusMessage(message, type) {
        var messageDiv = document.getElementById('status-message');
        var textElement = document.getElementById('status-text');

        if (!messageDiv || !textElement) return;

        messageDiv.className = 'alert';
        textElement.textContent = message;

        switch(type) {
            case 'success':
                messageDiv.classList.add('alert-success');
                break;
            case 'error':
                messageDiv.classList.add('alert-danger');
                break;
            default:
                messageDiv.classList.add('alert-info');
        }

        messageDiv.classList.remove('hidden');
        messageDiv.classList.add('fade-in');

        setTimeout(function() {
            messageDiv.classList.add('hidden');
        }, 3000);
    }

    function createXHR() {
        return new XMLHttpRequest();
    }

    function getCsrfToken() {
        var metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    }

    return {
        showStatusMessage: showStatusMessage,
        createXHR: createXHR,
        getCsrfToken: getCsrfToken
    };
})();
