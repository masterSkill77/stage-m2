<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Stockage des images envoyées par les utilisateurs (photos de profil, images de NFT).
 * Les fichiers sont sur le disque public ; l'URL est construite à partir de l'hôte de la requête.
 */
class ImageUploadService
{
    public function store(UploadedFile $file, string $folder): string
    {
        $path = Storage::disk('public')->putFile($folder, $file);

        return url('storage/' . $path);
    }

    // Supprime une image précédemment stockée (URL renvoyée par store), si elle est sur notre disque
    public function delete(?string $url): void
    {
        if (!$url) {
            return;
        }
        $marker = '/storage/';
        $position = strpos($url, $marker);
        if ($position === false) {
            return;
        }
        Storage::disk('public')->delete(substr($url, $position + strlen($marker)));
    }
}
