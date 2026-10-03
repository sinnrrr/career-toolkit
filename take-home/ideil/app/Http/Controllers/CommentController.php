<?php

namespace App\Http\Controllers;

use App\Comment;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Comment as CommentRequest;
use App\Http\Resources\Comment as CommentResource;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(Comment::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CommentRequest $request
     * @return mixed
     */
    public function store(CommentRequest $request)
    {
        $validatedData = $request->validated();
        $comment = new Comment();

        $comment->owner_id = $validatedData['owner_id'];
        $comment->marker_id = $validatedData['marker_id'];
        $comment->body = $validatedData['body'];

        if ($comment->save()) {
            $comment->message = 'Comment successfully created';

            return response()->json(new CommentResource($comment));
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
            new CommentResource(Comment::findOrFail($id))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CommentRequest $request
     * @param $id
     * @return mixed
     */
    public function update(CommentRequest $request, $id)
    {
        $comment = Comment::findOrFail($id);

        $validatedData = $request->validated();
        $comment->body = $validatedData['body'];

        if ($comment->save()) {
            $comment->message = "Comment ID {$id} successfully updated";

            return response()->json(new CommentResource($comment));
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
        Comment::findOrFail($id)->delete();

        return response()->json((object)['message' => "Comment ID {$id} successfully destroyed"]);
    }
}
