<?php

namespace App\Http\Controllers\API;

use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FriendsService;
use Symfony\Component\HttpFoundation\Response;

class ContactController extends Controller
{
    public function __construct(public FriendsService $friendsService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = $this->friendsService->myFriends(auth()->user()->id);
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $friendId = $request->input("friendId");
        $userId = auth()->user()->id;
        if ($friendId == $userId)
            return response()->json(["error" => "CANNOT_AUTO_FRIEND"], Response::HTTP_BAD_REQUEST);
        $friend = $this->friendsService->addFriend($userId, $friendId);

        return response()->json($friend, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $userId)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Contact $contactModel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $friend)
    {
        if (!$friend->id)
            return response()->json(['error' => 'NOT_FOUND'], Response::HTTP_NOT_FOUND);
        $userId = auth()->user()->id;
        if ($friend->id == $userId)
            return response()->json(["error" => "CANNOT_AUTO_FRIEND"], Response::HTTP_BAD_REQUEST);
        $removed = $this->friendsService->removeFriend($userId, $friend->id);

        return response()->json($removed);
    }
}
