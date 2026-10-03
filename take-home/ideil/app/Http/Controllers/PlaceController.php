<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Marker;
use App\Photo;
use App\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlaceController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @param $id
     * @return Renderable
     */
    public function index($id)
    {
        $markerData = Marker::findOrFail($id);
        $userData = User::findOrFail($markerData->owner_id);
        $photoData = Photo::where('marker_id', $id)->get();
        $commentData = Comment::where('marker_id', $id)->orderBy('created_at', 'desc')->paginate(5);
        $commentData->withPath("/place/{$id}/");

        $commentUserIDs = [];
        $commentUserData = [];
        $commentUserNameData = [];

        // process of getting all IDs related to comments
        // in order to make only one query, where we are getting user names
        // by the range of IDs
        if (!empty($commentData)) {
            foreach ($commentData as $comment) {
                array_push($commentUserIDs, $comment->owner_id);
            }
        }

        // checking if array has more than one ID
        if (!empty($commentUserIDs)) {
            $commentUserNameData = DB::table('users')
                ->select('id', 'name')
                ->whereIn('id', $commentUserIDs)
                ->get();

            // refactoring array
            foreach ($commentUserNameData as $data) {
                $commentUserData[$data->id] = $data->name;
            }
        }

        return view('place', [
            'markerData' => $markerData,
            'userData' => $userData,
            'photoData' => $photoData,
            'commentData' => $commentData,
            'commentUserData' => $commentUserData,
            'checkAuth' => !Auth::check(),
            'authID' => Auth::id(),
            'authName' => Auth::user()->name ?? 'Unknown'
        ]);
    }
}
