<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Vue d'un utilisateur destinée aux autres utilisateurs : pas d'e-mail, pas de quotas,
 * pas d'informations de paiement. Seule la photo de profil est gardée dans la configuration.
 */
class PublicUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'username' => $this->username,
            'etherum_adress' => $this->etherum_adress,
            'created_at' => $this->created_at,
            'configuration' => $this->configuration
                ? ['profile_image' => $this->configuration->profile_image]
                : null,
        ];
    }
}
