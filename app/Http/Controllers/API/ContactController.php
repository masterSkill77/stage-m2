<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\PublicUserResource;
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
        $friends = collect($users)->filter()->each->load('configuration');
        return response()->json(PublicUserResource::collection($friends)->resolve());
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
        User::findOrFail($friendId);
        $contact = $this->friendsService->addFriend($userId, $friendId);

        return response()->json(['status' => $contact->accepted_at ? 'accepted' : 'pending'], Response::HTTP_CREATED);
    }

    // Demandes d'ami reçues et pas encore acceptées
    public function requests()
    {
        $users = $this->friendsService->incomingRequests(auth()->user()->id);
        return response()->json(PublicUserResource::collection(collect($users)->each->load('configuration'))->resolve());
    }

    // Accepte la demande envoyée par $user
    public function accept(User $user)
    {
        $contact = $this->friendsService->acceptRequest(auth()->user()->id, $user->id);

        if (!$contact)
            return response()->json(['error' => 'NO_REQUEST'], Response::HTTP_NOT_FOUND);

        return response()->json(['status' => 'accepted']);
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
