<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Vote;

class CommentVoteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function comment(Request $request)
    {
        $comment = Comment::create([
            'article_id' => $request->article_id,
            'text' => $request->text,
            'user_id' => $request->user_id
        ]);

        return response()->json(['status' => 'success', 'message' => 'Comment added', 'comment' => $comment], 201);
    }

    public function vote(Request $request)
    {
        Vote::create([
            'article_id' => $request->article_id, 'user_id' => $request->user_id
        ]);

        return response()->json(['status' => 'success', 'message' => 'Vote added'], 201);
    }

    public function getComments($id)
    {
        $comments = Comment::with('user')->where('article_id', $id)->orderBy('id', 'desc')->get();

        return response()->json(['status' => 'success', 'message' => 'Comments retrieved', 'data' => $comments], 200);
    }

    public function downVote(Request $request, $id)
    {

        $user_id = $request->input('user_id');

        Vote::where('article_id', $id)
            ->where('user_id', $user_id)
            ->delete();

        return response()->json(['status' => 'success', 'message' => 'Vote deleted'], 200);
    }
}
