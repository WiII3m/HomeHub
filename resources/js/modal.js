

window.DashboardModal = (function() {
    'use strict';

    function openModal(modalId, onShownCallback) {
        var modalElement = document.getElementById(modalId);
        if (!modalElement) {

            return;
        }

        convertToCustomModal(modalElement);

        createBackdropBeforeModal(modalId, modalElement);

        document.body.classList.add('dashboard-modal-open');

        modalElement.classList.add('dashboard-modal', 'show');

        setupCustomModalEvents(modalId);

        if (onShownCallback) {
            setTimeout(function() {
                onShownCallback();
            }, 100);
        }

    }

    function closeModal(modalId) {
        var modalElement = document.getElementById(modalId);
        if (!modalElement) {
            return;
        }

        modalElement.classList.remove('show');

        removeBackdrop();

        document.body.classList.remove('dashboard-modal-open');

        cleanupModalEvents(modalId);

    }

    function convertToCustomModal(modalElement) {

        modalElement.classList.remove('modal', 'fade');
        modalElement.classList.add('dashboard-modal');

        var dialog = modalElement.querySelector('.modal-dialog');
        if (dialog) {
            dialog.classList.remove('modal-dialog');
            dialog.classList.add('dashboard-modal-dialog');
        }

        var content = modalElement.querySelector('.modal-content');
        if (content) {
            content.classList.remove('modal-content');
            content.classList.add('dashboard-modal-content');
        }

        var header = modalElement.querySelector('.modal-header');
        if (header) {
            header.classList.remove('modal-header');
            header.classList.add('dashboard-modal-header');
        }

        var body = modalElement.querySelector('.modal-body');
        if (body) {
            body.classList.remove('modal-body');
            body.classList.add('dashboard-modal-body');
        }

        var footer = modalElement.querySelector('.modal-footer');
        if (footer) {
            footer.classList.remove('modal-footer');
            footer.classList.add('dashboard-modal-footer');
        }

        var title = modalElement.querySelector('.modal-title');
        if (title) {
            title.classList.remove('modal-title');
            title.classList.add('dashboard-modal-title');
        }
    }

    function createBackdropBeforeModal(modalId, modalElement) {

        removeBackdrop();

        var backdrop = document.createElement('div');
        backdrop.className = 'dashboard-modal-backdrop';
        backdrop.id = 'dashboard-backdrop-' + modalId;

        backdrop.addEventListener('click', function() {
            closeModal(modalId);
        });

        modalElement.parentNode.insertBefore(backdrop, modalElement);
        backdrop.classList.add('show');
    }

    function removeBackdrop() {
        var backdrops = document.querySelectorAll('.dashboard-modal-backdrop');
        for (var i = 0; i < backdrops.length; i++) {
            backdrops[i].parentNode.removeChild(backdrops[i]);
        }
    }

    function setupCustomModalEvents(modalId) {
        var modalElement = document.getElementById(modalId);
        if (!modalElement || modalElement.hasAttribute('data-custom-events-setup')) {
            return;
        }

        var closeButtons = modalElement.querySelectorAll('[data-dismiss="modal"], .close');
        for (var i = 0; i < closeButtons.length; i++) {
            closeButtons[i].addEventListener('click', function(e) {
                e.preventDefault();
                closeModal(modalId);
            });
        }

        modalElement.setAttribute('data-custom-events-setup', 'true');
    }

    function cleanupModalEvents(modalId) {
        var modalElement = document.getElementById(modalId);
        if (modalElement) {
            modalElement.removeAttribute('data-custom-events-setup');
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.keyCode === 27) {

            var openModals = document.querySelectorAll('.dashboard-modal.show');
            for (var i = 0; i < openModals.length; i++) {
                var modalId = openModals[i].id;
                if (modalId) {
                    closeModal(modalId);
                    break;
                }
            }
        }
    });

    return {
        open: openModal,
        close: closeModal
    };
})();
