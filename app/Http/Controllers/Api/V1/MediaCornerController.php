<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\MediaCorner;
use Illuminate\Http\Request;

class MediaCornerController extends Controller
{
    public function index(Request $request)
    {
        $latestArticle = Article::latest()->first();
        if ($request->type == 'VIDEO') {
            $data = MediaCorner::whereIsActive(true)
                ->when($request->search, function ($query, $search) {
                    return $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->whereNotNull('published_at')
                ->orderByDesc('published_at')->paginate($request->per_page ?? 12);
        } else {
            $data = Article::whereIsActive(true)
                ->when($request->search, function ($query, $search) {
                    return $query->where('title', 'like', "%{$search}%")
                        ->orWhere('source', 'like', "%{$search}%");
                })
                ->orderBy('published_at')->paginate($request->per_page ?? 12);
        }

        // Example response (replace with actual data retrieval logic)
        return $this->getSuccessResponse([
            'latest_article' => $latestArticle,
            'data' => $data
        ]);
    }
}
