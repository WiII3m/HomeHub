<?php

namespace App\Http\Controllers;

use App\Services\TuyaService;
use App\Widgets\Core\WidgetManager;

class DashboardController extends Controller
{
    public function __construct(
        private TuyaService $tuyaService,
        private WidgetManager $widgetManager
    ) {}

    public function index()
    {
        $widgetsData = $this->widgetManager->getAllWidgetsMetadata();
        
        $renderedWidgets = $this->widgetManager->renderAllWidgets();
        
        $widgetAssets = $this->widgetManager->getAllAssets();

        return view('dashboard.index', [
            'widgetsData' => $widgetsData,
            'renderedWidgets' => $renderedWidgets,
            'widgetAssets' => $widgetAssets
        ])->with('widgetsData', $widgetsData);
    }


}
