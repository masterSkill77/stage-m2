<?php

namespace App\Services;

use App\Exceptions\AlreadyFriendException;
use App\Models\Contact;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class FriendsService
{
    public function myFriends(int $userId)
    {
        $myFriend = [...Contact::with(['user', 'friend'])->myFriend($userId)->get()];

        $result = array_map(function ($element) use ($userId) {
            if ($userId == $element->user_id) {
                return ($element->friend);
            } else if ($userId == $element->friend_id) {
                return ($element->user);
            }
        }, $myFriend);
        return $result;
    }

    public function itExist($userId, $friendId)
    {
        $users = $this->myFriends($userId);
        $exist = false;
        foreach ($users as $user) {
            if ($user->id == $friendId)
                return true;
        }
        return $exist;
    }
    public function addFriend(int $userId, int $friend_id)
    {
        if ($this->itExist($userId, $friend_id))
            throw new AlreadyFriendException();

        $friend = new Contact([
            'user_id' => $userId,
            'friend_id' => $friend_id
        ]);
        $friend->save();

        return $friend;
    }

    public function removeFriend(int $userId, int $friendId)
    {
        $resultat = Contact::where('user_id', $userId)->where('friend_id', $friendId)->first();
        if ($resultat)
            return  $resultat->delete();
        $resultat = Contact::where('user_id', $friendId)->where('friend_id', $userId)->first();

        if ($resultat)
            return  $resultat->delete();
        else
            return false;
    }
}
