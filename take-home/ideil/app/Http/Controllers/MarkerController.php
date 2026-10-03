<?php

namespace App\Http\Controllers;

use App\Marker;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Marker as MarkerRequest;
use App\Http\Resources\Marker as MarkerResource;

class MarkerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
//        $markers = Marker::paginate(10);
//        $markers->withPath('/');

        return response()->json(Marker::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param MarkerRequest $request
     * @return mixed
     */
    public function store(MarkerRequest $request)
    {
        $validatedData = $request->validated();
        $marker = new Marker();

        $marker->owner_id = $validatedData['owner_id'];
        $marker->lat = $validatedData['lat'];
        $marker->lng = $validatedData['lng'];
        $marker->title = $validatedData['title'];
        $marker->description = $validatedData['description'];

        if ($marker->save()) {
            $marker->message = 'Marker successfully created';

            return response()->json(new MarkerResource($marker));
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
            new MarkerResource(Marker::findOrFail($id))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param MarkerRequest $request
     * @param $id
     * @return mixed
     */
    public function update(MarkerRequest $request, $id)
    {
        $marker = Marker::findOrFail($id);

        $validatedData = $request->validated();

        $marker->title = $validatedData['title'];
        $marker->description = $validatedData['description'];

        if ($marker->save()) {
            $marker->message = "Marker ID {$id} successfully updated";

            return response()->json(new MarkerResource($marker));
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
        Marker::findOrFail($id)->delete();

        return response()->json((object)['message' => "Marker ID {$id} successfully deleted"]);
    }
}
