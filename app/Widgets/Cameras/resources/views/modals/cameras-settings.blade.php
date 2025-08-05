<div class="modal fade" id="cameraSettingsModal" tabindex="-1" role="dialog" aria-labelledby="cameraSettingsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="cameraSettingsModalLabel">
                    <span class="margin-right-8">⚙️</span>Paramètres caméras
                </h4>
            </div>
            <div class="modal-body">
                <div class="margin-top-16">
                        <div class="alert alert-info font-size-medium margin-bottom-16">
                            <strong>💡 Astuce :</strong> Glissez-déposez les caméras pour réorganiser leur ordre d'affichage.
                        </div>

                        <ul id="sortable-cameras" class="list-unstyled margin-top-0">
                            @foreach($cameras as $cameraId => $camera)
                            <li data-camera-id="{{ $camera['id'] }}" class="sortable-camera-item bg-gray-light border-1 border-gray border-radius-4 padding-12 margin-bottom-8 cursor-move">
                                <div class="row">
                                    <div class="col-xs-1 text-center">
                                        <span class="handle text-gray-light font-size-large line-height-none">⋮⋮</span>
                                    </div>
                                    <div class="col-xs-10">
                                        <span class="font-weight-500">{{ $camera['name'] ?? 'Caméra' }}</span>
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
