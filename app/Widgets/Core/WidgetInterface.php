<?php

namespace App\Widgets\Core;

interface WidgetInterface
{
    public function getName(): string;

    public function getTitle(): string;

    public function getIcon(): string;

    public function getData(): array;

    public function render(): string;

    public function getAssets(): array;

    public function getConfig(): array;
}
