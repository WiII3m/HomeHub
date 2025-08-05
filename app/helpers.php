<?php

if (!function_exists('vite_legacy')) {
    function vite_legacy($entry) {
        $manifestPath = public_path('build/manifest.json');

        if (!file_exists($manifestPath)) {
            return asset('build/' . $entry);
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        if (!isset($manifest[$entry])) {
            return asset('build/' . $entry);
        }

        return asset('build/' . $manifest[$entry]['file']);
    }
}
