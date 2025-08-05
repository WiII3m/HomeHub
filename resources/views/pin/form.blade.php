@extends('layouts.app')

@section('content')
<div class="container-fluid text-center padding-vertical-32">
    <div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">

            <div class="panel panel-default border-radius-12">
                <div class="panel-body padding-24">
                    <h3 class="margin-bottom-24 text-gray-dark">
                        <span class="glyphicon glyphicon-lock margin-right-8"></span>
                        Accès sécurisé
                    </h3>

                    <p class="text-gray-light margin-bottom-24">
                        Veuillez saisir votre code PIN pour accéder au dashboard
                    </p>

                    @if($errors->has('pin'))
                        <div class="alert alert-danger margin-bottom-16">
                            {{ $errors->first('pin') }}
                        </div>
                    @endif

                    <form method="POST" action="/pin/verify" id="pinForm">
                        @csrf

                        <!-- Affichage visuel du PIN (cercles) -->
                        <div class="pin-display margin-bottom-24">
                            <div class="pin-circles text-center">
                                <span class="pin-circle" data-index="0"></span>
                                <span class="pin-circle" data-index="1"></span>
                                <span class="pin-circle" data-index="2"></span>
                                <span class="pin-circle" data-index="3"></span>
                                <span class="pin-circle" data-index="4"></span>
                                <span class="pin-circle" data-index="5"></span>
                            </div>
                        </div>

                        <!-- Input caché pour le PIN -->
                        <input type="hidden" name="pin" id="pinInput" value="">

                        <!-- Clavier tactile -->
                        <div class="pin-keypad">
                            <div class="row margin-bottom-8">
                                <div class="col-xs-4"><button type="button" class="btn btn-default btn-block height-52 pin-key" data-key="1">1</button></div>
                                <div class="col-xs-4"><button type="button" class="btn btn-default btn-block height-52 pin-key" data-key="2">2</button></div>
                                <div class="col-xs-4"><button type="button" class="btn btn-default btn-block height-52 pin-key" data-key="3">3</button></div>
                            </div>
                            <div class="row margin-bottom-8">
                                <div class="col-xs-4"><button type="button" class="btn btn-default btn-block height-52 pin-key" data-key="4">4</button></div>
                                <div class="col-xs-4"><button type="button" class="btn btn-default btn-block height-52 pin-key" data-key="5">5</button></div>
                                <div class="col-xs-4"><button type="button" class="btn btn-default btn-block height-52 pin-key" data-key="6">6</button></div>
                            </div>
                            <div class="row margin-bottom-8">
                                <div class="col-xs-4"><button type="button" class="btn btn-default btn-block height-52 pin-key" data-key="7">7</button></div>
                                <div class="col-xs-4"><button type="button" class="btn btn-default btn-block height-52 pin-key" data-key="8">8</button></div>
                                <div class="col-xs-4"><button type="button" class="btn btn-default btn-block height-52 pin-key" data-key="9">9</button></div>
                            </div>
                            <div class="row">
                                <div class="col-xs-4">
                                    <button type="button" class="btn btn-danger btn-block height-52" id="pinClear">
                                        <span class="glyphicon glyphicon-remove"></span>
                                    </button>
                                </div>
                                <div class="col-xs-4"><button type="button" class="btn btn-default btn-block height-52 pin-key" data-key="0">0</button></div>
                                <div class="col-xs-4">
                                    <button type="button" class="btn btn-success btn-block height-52" id="pinSubmit">
                                        <span class="glyphicon glyphicon-ok"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pin-display {
    margin: 20px 0;
}

.pin-circles {
    display: inline-block;
}

.pin-circle {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ccc;
    border-radius: 50%;
    margin: 0 4px;
    background-color: transparent;
    transition: background-color 0.2s ease;
}

.pin-circle.filled {
    background-color: #007cba;
    border-color: #007cba;
}

.pin-keypad {
    max-width: 280px;
    margin: 0 auto;
}

.pin-key {
    font-size: 18px;
    font-weight: 500;
}

button {
    -webkit-tap-highlight-color: transparent;
    -webkit-touch-callout: none;
    -webkit-user-select: none;
    user-select: none;
}

.panel {
    border-radius: 12px;
    border: 1px solid #ddd;
}

.panel-body {
    border-radius: 12px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var pinInput = document.getElementById('pinInput');
    var pinForm = document.getElementById('pinForm');
    var pinCircles = document.querySelectorAll('.pin-circle');
    var pinKeys = document.querySelectorAll('.pin-key');
    var pinClear = document.getElementById('pinClear');
    var pinSubmit = document.getElementById('pinSubmit');
    var currentPin = '';

    function updateDisplay() {
        for (var i = 0; i < pinCircles.length; i++) {
            if (i < currentPin.length) {
                pinCircles[i].classList.add('filled');
            } else {
                pinCircles[i].classList.remove('filled');
            }
        }
        pinInput.value = currentPin;
    }

    function addDigit(digit) {
        if (currentPin.length < 6) {
            currentPin += digit;
            updateDisplay();
        }
    }

    function clearPin() {
        currentPin = '';
        updateDisplay();
    }

    function submitPin() {
        if (currentPin.length === 6) {
            // Envoi AJAX pour récupérer le token JWT
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '/pin/verify', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                // Rediriger vers le dashboard (CSRF token sera dans meta tag)
                                window.location.href = response.redirect;
                            }
                        } catch(e) {
                            showError('Erreur de connexion');
                        }
                    } else if (xhr.status === 401) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            var message = response.error || 'PIN incorrect';
                            if (typeof response.attempts_remaining === 'number') {
                                if (response.attempts_remaining > 0) {
                                    message += ' (' + response.attempts_remaining + ' tentative' + (response.attempts_remaining > 1 ? 's' : '') + ' restante' + (response.attempts_remaining > 1 ? 's' : '') + ')';
                                } else {
                                    message = 'Trop de tentatives. Réessayez dans 5 minutes.';
                                }
                            }
                            showError(message);
                        } catch(e) {
                            showError('PIN incorrect');
                        }
                        clearPin();
                    } else if (xhr.status === 429) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            showError(response.error || 'Trop de tentatives');
                        } catch(e) {
                            showError('Trop de tentatives');
                        }
                        clearPin();
                    } else {
                        showError('Erreur de connexion');
                        clearPin();
                    }
                }
            };

            xhr.send('pin=' + encodeURIComponent(currentPin) + '&_token=' + encodeURIComponent(document.querySelector('meta[name="csrf-token"]').getAttribute('content')));
        }
    }

    function showError(message) {
        var existingAlert = document.querySelector('.alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        var alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger margin-bottom-16';
        alertDiv.textContent = message;

        var form = document.getElementById('pinForm');
        form.parentNode.insertBefore(alertDiv, form);
    }

    // Événements clavier tactile
    for (var i = 0; i < pinKeys.length; i++) {
        pinKeys[i].addEventListener('click', function(e) {
            e.preventDefault();
            addDigit(this.getAttribute('data-key'));
        });
    }

    pinClear.addEventListener('click', function(e) {
        e.preventDefault();
        clearPin();
    });

    pinSubmit.addEventListener('click', function(e) {
        e.preventDefault();
        submitPin();
    });

    // Support clavier physique (optionnel)
    document.addEventListener('keydown', function(e) {
        if (e.key >= '0' && e.key <= '9') {
            addDigit(e.key);
            e.preventDefault();
        } else if (e.key === 'Backspace') {
            clearPin();
            e.preventDefault();
        } else if (e.key === 'Enter') {
            submitPin();
            e.preventDefault();
        }
    });
});
</script>
@endsection
