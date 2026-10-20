<?php

namespace App\Services;

use App\Exceptions\AlreadyFriendException;
use App\Models\Contact;
use Exception;

class FriendsService
{
    // Amis confirmés uniquement : les demandes en attente n'apparaissent pas dans la liste d'amis
    public function myFriends(int $userId)
    {
        $contacts = Contact::with(['user', 'friend'])->myFriend($userId)->accepted()->get();

        return $contacts->map(fn ($contact) => $contact->user_id == $userId ? $contact->friend : $contact->user)->values()->all();
    }

    public function itExist($userId, $friendId)
    {
        foreach ($this->myFriends($userId) as $user) {
            if ($user->id == $friendId)
                return true;
        }
        return false;
    }

    /**
     * Envoie une demande d'ami. Si l'autre personne avait déjà envoyé une demande, elle est acceptée.
     */
    public function addFriend(int $userId, int $friendId): Contact
    {
        $existing = Contact::betweenUsers($userId, $friendId)->first();

        if ($existing) {
            if ($existing->accepted_at)
                throw new AlreadyFriendException();

            // Demande reçue de l'autre personne : on l'accepte au lieu d'en créer une seconde
            if ($existing->user_id != $userId) {
                $existing->accepted_at = now();
                $existing->save();
            }
            return $existing;
        }

        $contact = new Contact([
            'user_id' => $userId,
            'friend_id' => $friendId
        ]);
        $contact->save();

        return $contact;
    }

    // Accepte une demande reçue d'un utilisateur donné
    public function acceptRequest(int $userId, int $requesterId): ?Contact
    {
        $contact = Contact::where('user_id', $requesterId)
            ->where('friend_id', $userId)
            ->whereNull('accepted_at')
            ->first();

        if (!$contact)
            return null;

        $contact->accepted_at = now();
        $contact->save();

        return $contact;
    }

    // Demandes reçues et pas encore acceptées : renvoie les utilisateurs qui les ont envoyées
    public function incomingRequests(int $userId)
    {
        return Contact::with('user')
            ->where('friend_id', $userId)
            ->whereNull('accepted_at')
            ->get()
            ->pluck('user')
            ->values()
            ->all();
    }

    // Statut de l'amitié vu depuis $userId : none, outgoing, incoming ou friends
    public function friendshipStatus(int $userId, int $otherId): string
    {
        $contact = Contact::betweenUsers($userId, $otherId)->first();

        if (!$contact)
            return 'none';
        if ($contact->accepted_at)
            return 'friends';

        return $contact->user_id == $userId ? 'outgoing' : 'incoming';
    }

    public function removeFriend(int $userId, int $friendId)
    {
        $contact = Contact::betweenUsers($userId, $friendId)->first();

        if ($contact)
            return $contact->delete();

        return false;
    }
}
