<?php

namespace App\Http\Controllers;

use App\Photo;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Photo as PhotoRequest;
use App\Http\Resources\Photo as PhotoResource;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(Photo::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PhotoRequest $request
     * @return mixed
     */
    public function store(PhotoRequest $request)
    {
        $validatedData = $request->validated();
        $photo = new Photo();

        $photo->owner_id = $validatedData['owner_id'];
        $photo->marker_id = $validatedData['marker_id'];
        $photo->content = $request->file('content')->store('uploads', 'public');

        if ($photo->save()) {
            $photo->message = 'Photo successfully uploaded';

            return response()->json(new PhotoResource($photo));
        } else {
            abort(500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return response()->json(
            new PhotoResource(Photo::findOrFail($id))
        );
    }


    /**
     * Update the specified resource in storage.
     * Not sure if this should be in app.
     *
     * @param PhotoRequest $request
     * @param $id
     * @return mixed
     */
    public function update(PhotoRequest $request, $id)
    {
        $photo = Photo::findOrFail($id);

        $photo->content = $request->file('content')->store('uploads', 'public');

        if ($photo->save()) {
            $photo->message = "Photo ID {$id} successfully updated";

            return response()->json(new PhotoResource($photo));
        } else {
            abort(500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        Photo::findOrFail($id)->delete();

        return response()->json((Object) ['message' => "Photo ID {$id} successfully deleted"]);
    }
}
