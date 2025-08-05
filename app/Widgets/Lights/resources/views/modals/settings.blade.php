<div class="modal fade" id="lightsSettingsModal" tabindex="-1" role="dialog" aria-labelledby="lightsSettingsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="lightsSettingsModalLabel">
                    <span class="margin-right-8">⚙️</span>Paramètres d'éclairage
                </h4>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#room-order-tab" aria-controls="room-order-tab" role="tab" data-toggle="tab">
                            🏠 Ordre des pièces
                        </a>
                    </li>
                    <li role="presentation">
                        <a href="#light-sort-tab" aria-controls="light-sort-tab" role="tab" data-toggle="tab">
                            💡 Tri des lumières
                        </a>
                    </li>
                </ul>

                <div class="tab-content margin-top-24">
                    <div role="tabpanel" class="tab-pane active" id="room-order-tab">
                        <div class="alert alert-info font-size-medium margin-bottom-16">
                            <strong>💡 Astuce :</strong> Glissez-déposez les pièces pour réorganiser leur ordre d'affichage.
                        </div>

                        <ul id="sortable-rooms" class="list-unstyled margin-top-0">
                            @foreach($lightsByRoom as $room)
                            <li data-room-id="{{ $room['room_id'] }}" class="sortable-room-item bg-gray-light border-1 border-gray border-radius-4 padding-12 margin-bottom-8 cursor-move">
                                <div class="row">
                                    <div class="col-xs-1 text-center">
                                        <span class="handle text-gray-light font-size-large line-height-none">⋮⋮</span>
                                    </div>
                                    <div class="col-xs-10">
                                        <span class="font-weight-500">{{ $room['room_name'] }}</span>
                                        <small class="text-gray margin-left-8">({{ count($room['lights']) }} lumière{{ count($room['lights']) > 1 ? 's' : '' }})</small>
                                    </div>
                                </div>
                            </li>
                            @endforeach
                        </ul>

                        <div class="margin-top-16 font-size-small text-gray">
                            <strong>💾 Sauvegarde :</strong> L'ordre est sauvegardé automatiquement.
                        </div>
                    </div>

                    <div role="tabpanel" class="tab-pane" id="light-sort-tab">
                        <div class="alert alert-info font-size-medium margin-bottom-16">
                            <strong>💡 Paramètre :</strong> Choisissez comment trier les lumières dans chaque pièce.
                        </div>

                        <div class="form-group margin-bottom-24">
                            <label class="font-weight-500 margin-bottom-12">📝 Ordre alphabétique :</label>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="alphabeticOrder" value="a-to-z" id="order-a-to-z">
                                    <span class="margin-left-8">A → Z (croissant)</span>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="alphabeticOrder" value="z-to-a" id="order-z-to-a">
                                    <span class="margin-left-8">Z → A (décroissant)</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-500 margin-bottom-12">⚡ Priorité d'état :</label>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="statusPriority" value="online-first" id="priority-online-first">
                                    <span class="margin-left-8 display-flex align-items-center"><span class="margin-right-8 status-point online display-block border-radius-50"></span> En ligne d'abord</span>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="statusPriority" value="offline-first" id="priority-offline-first">
                                    <span class="margin-left-8 display-flex align-items-center"><span class="margin-right-8 status-point offline display-block border-radius-50"></span> Hors ligne d'abord</span>
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                    <input type="radio" name="statusPriority" value="no-priority" id="priority-no-priority">
                                    <span class="margin-left-8 display-flex align-items-center"><span class="margin-right-8 status-point none display-block border-radius-50"></span> Ignorer l'état</span>
                                </label>
                            </div>
                        </div>

                        <div class="margin-top-16 font-size-small text-gray">
                            <strong>💾 Sauvegarde :</strong> Le tri s'applique à toutes les pièces et est sauvegardé automatiquement.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
