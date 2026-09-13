<?php

declare(strict_types=1);

namespace Suricate\Helper;

class ViteAsset
{
    private static ?array $manifest = null;

    /**
     * Retourne les chemins du JS et du CSS associés à une entrée Vite
     */
    public static function get(string $entry, string $distPath = '/dist/'): array
    {
        $manifestPath = $_SERVER['DOCUMENT_ROOT'] . $distPath . '.vite/manifest.json';

        // Charge et met en cache le manifest une seule fois par requête
        if (self::$manifest === null) {
            if (file_exists($manifestPath)) {
                self::$manifest = json_decode(file_get_contents($manifestPath), true) ?? [];

            } else {
                self::$manifest = [];
            }
        }

        if (!isset(self::$manifest[$entry])) {
            return ['js' => [], 'preload' => [], 'css' => []];
        }

        $item = self::$manifest[$entry];

        // Fichier JS principal
        $js = isset($item['file']) ? $distPath . $item['file'] : null;

        $preload = [];
        // Preload des chunks JS importés statiquement
        if (isset($item['imports']) && is_array($item['imports'])) {
            foreach ($item['imports'] as $importKey) {
                if (isset(self::$manifest[$importKey])) {
                    $preload[] = $distPath .self::$manifest[$importKey]['file'];
                }
            }
        }

        // Fichiers CSS extraits/générés par Vite
        $css = [];
        if (!empty($item['css'])) {
            foreach ($item['css'] as $cssFile) {
                $css[] = $distPath . $cssFile;
            }
        }

        return [
            'js'  => $js,
            'preload' => $preload,
            'css' => $css,
        ];
    }
}
