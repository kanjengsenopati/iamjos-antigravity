<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function show($slug)
    {
        // Ambil artikel berdasarkan slug
        $article = Article::where('slug', $slug)->firstOrFail();

        // Ambil 6 artikel lain secara acak, kecuali artikel yang sedang ditampilkan
        $articles = Article::where('id', '!=', $article->id)
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return $this->getSuccessResponse([
            'article' => $article,
            'articles' => $articles,
        ]);
    }
}
