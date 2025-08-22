<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function show($slug)
    {
        // buatkan detail artikel menggunakan slug
        $article = Article::where('slug', $slug)->firstOrFail();
        return $this->getSuccessResponse($article);
    }
}
