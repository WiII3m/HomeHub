<!-- Modal des paramètres thermomètres -->
<div class="modal fade" id="thermometersSettingsModal" tabindex="-1" role="dialog" aria-labelledby="thermometersSettingsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="thermometersSettingsModalLabel">
                    <span class="margin-right-8">⚙️</span>Paramètres thermomètres
                </h4>
            </div>
            <div class="modal-body">
                <!-- Contenu principal -->
                <div class="margin-top-16">
                        <div class="alert alert-info font-size-medium margin-bottom-16">
                            <strong>💡 Astuce :</strong> Glissez-déposez les thermomètres pour réorganiser leur ordre d'affichage.
                        </div>

                        <ul id="sortable-thermometers" class="list-unstyled margin-top-0">
                            @foreach($thermometers as $thermometerId => $thermometer)
                            <li data-thermometer-id="{{ $thermometerId }}" class="sortable-thermometer-item bg-gray-light border-1 border-gray border-radius-4 padding-12 margin-bottom-8 cursor-move">
                                <div class="row">
                                    <div class="col-xs-1 text-center">
                                        <span class="handle text-gray-light font-size-large line-height-none">⋮⋮</span>
                                    </div>
                                    <div class="col-xs-10">
                                        <span class="font-weight-500">{{ $thermometer['name'] ?? 'Thermomètre' }}</span>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>

                        <div class="margin-top-16 font-size-small text-gray">
                            <strong>💾 Sauvegarde :</strong> L'ordre est sauvegardé automatiquement.
                        </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
