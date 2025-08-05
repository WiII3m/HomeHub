<?php

namespace App\Widgets\HelloWorld;

use App\Widgets\Core\WidgetInterface;
use App\Widgets\HelloWorld\App\Services\HelloWorldService;

/**
 * Widget HelloWorld - Exemple pédagogique
 * Montre la structure de base d'un widget avec devices simulés
 */
class HelloWorldWidget implements WidgetInterface
{
    private HelloWorldService $helloworldService;
    private array $config;

    public function __construct(HelloWorldService $helloworldService)
    {
        // Injection du service pour respecter la séparation des responsabilités
        $this->helloworldService = $helloworldService;
        // Chargement de la configuration depuis le fichier JSON
        $this->config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
    }

    /**
     * Nom technique du widget (utilisé en interne)
     */
    public function getName(): string
    {
        return $this->config['name'];
    }

    /**
     * Auteur du widget
     */
    public function getAuthor(): string
    {
        return $this->config['author'];
    }

    /**
     * Titre affiché dans l'interface utilisateur
     */
    public function getTitle(): string
    {
        return $this->config['title'];
    }

    /**
     * Icône du widget (emoji ou classe CSS)
     */
    public function getIcon(): string
    {
        return $this->config['icon'];
    }

    /**
     * Données du widget - récupérées depuis le service
     * Logique métier déléguée au service pour respecter la séparation des responsabilités
     */
    public function getData(): array
    {
        return [
            'devices' => $this->helloworldService->getDevices(),
            'config' => $this->getConfig()
        ];
    }

    /**
     * Rendu du widget - génère le HTML complet
     * Combine les données du service avec le template Blade
     */
    public function render(): string
    {
        $data = $this->getData();

        return view('widgets::HelloWorld.resources.views.hello-world-widget', $data)->render();
    }

    /**
     * Assets CSS/JS spécifiques au widget
     * Définis dans le fichier config.json
     */
    public function getAssets(): array
    {
        return $this->config['assets'];
    }

    /**
     * Configuration complète du widget
     * Toutes les métadonnées depuis le fichier JSON
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
