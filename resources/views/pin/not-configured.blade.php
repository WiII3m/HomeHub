@extends('layouts.app')

@section('content')
<div class="container-fluid text-center padding-vertical-32">
    <div class="row">
        <div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3">

            <div class="panel panel-warning border-radius-12">
                <div class="panel-body padding-24">
                    <h3 class="margin-bottom-24 text-gray-dark">
                        <span class="glyphicon glyphicon-exclamation-sign margin-right-8"></span>
                        Configuration requise
                    </h3>

                    <p class="text-gray-light margin-bottom-24">
                        Aucun code PIN n'est configuré pour sécuriser l'accès au dashboard.
                    </p>

                    <div class="alert alert-warning">
                        <strong>Action requise :</strong><br>
                        Veuillez configurer la variable <code>APP_PIN</code> dans votre fichier <code>.env</code>
                    </div>

                    <div class="margin-top-24">
                        <h4 class="text-left">Instructions :</h4>
                        <ol class="text-left margin-top-16">
                            <li>Ouvrez le fichier <code>.env</code> à la racine du projet</li>
                            <li>Ajoutez la ligne : <code>APP_PIN=123456</code> (remplacez 123456 par votre PIN)</li>
                            <li>Utilisez un PIN à 6 chiffres</li>
                            <li>Rechargez cette page</li>
                        </ol>
                    </div>

                    <div class="margin-top-24">
                        <a href="/pin" class="btn btn-primary">
                            <span class="glyphicon glyphicon-refresh margin-right-4"></span>
                            Recharger
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.panel {
    border-radius: 12px;
}

.panel-body {
    border-radius: 12px;
}

code {
    background-color: #f5f5f5;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 90%;
}

ol {
    padding-left: 20px;
}

ol li {
    margin-bottom: 8px;
}
</style>
@endsection
