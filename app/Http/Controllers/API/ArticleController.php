<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Article;
use Cloudinary\Cloudinary;

class ArticleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Article::with('user', 'comments', 'votes')->orderBy('created_at', 'desc');

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        $articles = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $articles->items(),
            'total' => $articles->total(),
            'current_page' => $articles->currentPage(),
            'last_page' => $articles->lastPage(),
            'is_bottom' => $articles->currentPage() === $articles->lastPage()
        ], 200);
    }


    public function userArticles($id)
    {
        $articles = Article::with('user', 'comments.user', 'votes')->where('user_id', $id)->orderBy('created_at', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $articles], 200);
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'user_id' => 'required|exists:users,id'
        ]);

        // $photoPath = null;
        $photoUrl = null;
        if ($request->hasFile('photo')) {

            // $photoPath = $request->file('photo')->store('posts', 'public');
            $photo = $request->file('photo');
            $cloudinary = new Cloudinary();
            $uploadedImage = $cloudinary->uploadApi()->upload($photo->getRealPath(), [
                'folder' => 'posts',
            ]);

            $photoUrl = $uploadedImage['secure_url'];
        }

        $article = Article::create([
            'title' => $request->title,
            'content' => $request->content,
            'photo' => $photoUrl,
            'user_id' => $request->user_id
        ]);


        $article->photo = $article->photo ? url('storage/' . $article->photo) : null;

        return response()->json(['status' => 'success', 'articles' => $article], 201);
    }


    public function show($id)
    {
        $article = Article::with('user', 'comments.user', 'votes')->where('id', $id)->first();
        if (!$article) {
            return response()->json(['status' => 'error', 'message' => 'Article not found'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $article], 201);
    }




    public function update(Request $request, $id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['status' => 'success', 'message' => 'Article not found'], 404);
        }

        $article->update([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Article Updated successfully.', 'articles' => $article], 200);
    }


    public function destroy($id)
    {
        $article = Article::find($id);

        if (!$article) {
            return response()->json(['status' => 'error', 'message' => 'Article not found'], 404);
        }

        $article->delete();

        return response()->json(['status' => 'success', 'message' => 'Article deleted successfully.', 'articles' => $article], 201);
    }
}
