

window.PinManager = (function() {
    'use strict';

    function setupPinForm() {
        var pinForm = document.getElementById('changePinForm');
        if (pinForm) {
            pinForm.addEventListener('submit', function(e) {
                e.preventDefault();

                var currentPin = document.getElementById('currentPin').value;
                var newPin = document.getElementById('newPin').value;
                var confirmPin = document.getElementById('confirmPin').value;
                var resultDiv = document.getElementById('pinChangeResult');

                if (!currentPin || !newPin || !confirmPin) {
                    resultDiv.innerHTML = '<div class="alert alert-danger">Tous les champs sont requis</div>';
                    return;
                }

                if (newPin !== confirmPin) {
                    resultDiv.innerHTML = '<div class="alert alert-danger">Les nouveaux PINs ne correspondent pas</div>';
                    return;
                }

                if (newPin.length !== 6) {
                    resultDiv.innerHTML = '<div class="alert alert-danger">Le PIN doit contenir exactement 6 chiffres</div>';
                    return;
                }

                if (!/^\d+$/.test(newPin)) {
                    resultDiv.innerHTML = '<div class="alert alert-danger">Le PIN ne doit contenir que des chiffres</div>';
                    return;
                }

                var xhr = window.DashboardUtils.createXHR();
                xhr.open('POST', '/pin/change', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-CSRF-TOKEN', window.DashboardUtils.getCsrfToken());

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    resultDiv.innerHTML = '<div class="alert alert-success">' + response.message + '</div>';
                                    pinForm.reset();
                                } else {
                                    resultDiv.innerHTML = '<div class="alert alert-danger">' + response.error + '</div>';
                                }
                            } catch(e) {
                                resultDiv.innerHTML = '<div class="alert alert-danger">Erreur lors du changement de PIN</div>';
                            }
                        } else {
                            var error = 'Erreur lors du changement de PIN';
                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.errors) {
                                    for (var key in response.errors) {
                                        if (response.errors.hasOwnProperty(key) && response.errors[key][0]) {
                                            error = response.errors[key][0];
                                            break;
                                        }
                                    }
                                }
                            } catch(e) {}
                            resultDiv.innerHTML = '<div class="alert alert-danger">' + error + '</div>';
                        }
                    }
                };

                xhr.send(JSON.stringify({
                    current_pin: currentPin,
                    new_pin: newPin,
                    confirm_pin: confirmPin
                }));
            });
        }
    }

    function init() {
        setupPinForm();
    }

    return {
        init: init,
        setupPinForm: setupPinForm
    };
})();

document.addEventListener('DOMContentLoaded', function() {
    if (window.PinManager) {
        window.PinManager.init();
    }
});

if (document.readyState !== 'loading') {
    if (window.PinManager) {
        window.PinManager.init();
    }
}