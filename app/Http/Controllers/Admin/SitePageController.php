<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SitePageRequest;
use App\Http\Resources\SitePageResource;
use App\Models\SitePage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * SitePageController
 * 
 * Manages custom static pages for the portal (CMS).
 * Pages can be created with rich text content using TinyMCE.
 * Supports both traditional views and AJAX/API operations.
 */
class SitePageController extends Controller
{
    /**
     * Display a listing of site pages.
     * Supports AJAX requests with search, filter, and pagination.
     */
    public function index(Request $request)
    {
        // Check if this is an AJAX/API request
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return $this->indexApi($request);
        }

        // Traditional view response
        $pages = SitePage::ordered()->get();
        return view('admin.site-pages.index', compact('pages'));
    }

    /**
     * API endpoint for listing pages with search, filter, and pagination
     */
    protected function indexApi(Request $request): JsonResponse
    {
        $query = SitePage::query()->with(['creator', 'updater']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('slug', 'ilike', "%{$search}%")
                  ->orWhere('content', 'ilike', "%{$search}%");
            });
        }

        // Filter by publication status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            }
        }

        // Order by sort_order
        $query->ordered();

        // Pagination
        $perPage = $request->input('per_page', 25);
        $pages = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => SitePageResource::collection($pages),
            'meta' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new page.
     */
    public function create(): View
    {
        return view('admin.site-pages.form', [
            'page' => null,
            'title' => 'Create New Page',
        ]);
    }

    /**
     * Store a newly created page.
     * Supports both traditional form submission and AJAX requests.
     */
    public function store(SitePageRequest $request)
    {
        $validated = $request->validated();

        $page = SitePage::create($validated);

        // AJAX/API response
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Page created successfully',
                'data' => new SitePageResource($page->load(['creator', 'updater'])),
            ], 201);
        }

        // Traditional redirect response
        return redirect()->route('admin.site-pages.index')
            ->with('success', 'Page created successfully.');
    }

    /**
     * Display the specified page.
     * API endpoint for retrieving a single page.
     */
    public function show(SitePage $sitePage): JsonResponse
    {
        $sitePage->load(['creator', 'updater']);

        return response()->json([
            'success' => true,
            'data' => new SitePageResource($sitePage),
        ]);
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(SitePage $sitePage): View
    {
        return view('admin.site-pages.form', [
            'page' => $sitePage,
            'title' => 'Edit Page: ' . $sitePage->title,
        ]);
    }

    /**
     * Update the specified page.
     * Supports both traditional form submission and AJAX requests.
     */
    public function update(SitePageRequest $request, SitePage $sitePage)
    {
        $validated = $request->validated();

        $sitePage->update($validated);

        // AJAX/API response
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Page updated successfully',
                'data' => new SitePageResource($sitePage->fresh()->load(['creator', 'updater'])),
            ]);
        }

        // Traditional redirect response
        return redirect()->route('admin.site-pages.index')
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified page.
     * Supports both traditional form submission and AJAX requests.
     */
    public function destroy(Request $request, SitePage $sitePage)
    {
        $this->authorize('manage-site-pages');

        $pageTitle = $sitePage->title;
        $sitePage->delete();

        // AJAX/API response
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Page '{$pageTitle}' deleted successfully",
            ]);
        }

        // Traditional redirect response
        return redirect()->route('admin.site-pages.index')
            ->with('success', 'Page deleted successfully.');
    }

    /**
     * Duplicate an existing page.
     * Creates a copy with "(Copy)" suffix and draft status.
     */
    public function duplicate(SitePage $sitePage): JsonResponse
    {
        $this->authorize('manage-site-pages');

        // Create a copy of the page
        $copy = $sitePage->replicate();
        $copy->title = $sitePage->title . ' (Copy)';
        $copy->slug = $sitePage->slug . '-copy';
        $copy->is_published = false; // Set to draft
        $copy->sort_order = SitePage::max('sort_order') + 1;
        $copy->save();

        return response()->json([
            'success' => true,
            'message' => 'Page duplicated successfully',
            'data' => new SitePageResource($copy->load(['creator', 'updater'])),
        ], 201);
    }

    /**
     * Toggle page published state via AJAX.
     */
    public function toggle(SitePage $sitePage): JsonResponse
    {
        $this->authorize('manage-site-pages');

        $sitePage->is_published = !$sitePage->is_published;
        $sitePage->save();

        return response()->json([
            'success' => true,
            'is_published' => $sitePage->is_published,
            'message' => $sitePage->is_published ? 'Page published' : 'Page unpublished',
        ]);
    }

    /**
     * Reorder pages via AJAX (Drag & Drop).
     */
    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('manage-site-pages');

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|uuid|exists:site_pages,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            SitePage::where('id', $item['id'])
                ->update(['sort_order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Page order updated successfully',
        ]);
    }

    /**
     * Bulk delete multiple pages.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $this->authorize('manage-site-pages');

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|uuid|exists:site_pages,id',
        ]);

        $count = SitePage::whereIn('id', $validated['ids'])->count();
        SitePage::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} page(s) deleted successfully",
            'count' => $count,
        ]);
    }
}
