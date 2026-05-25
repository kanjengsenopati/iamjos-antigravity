<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContentBlockRequest;
use App\Http\Resources\ContentBlockResource;
use App\Models\SiteContentBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ContentBlockController
 * 
 * Manages content blocks for the Page Builder system.
 * Blocks are reusable content components that can be arranged
 * and configured to build custom portal pages.
 * Supports both traditional views and AJAX/API operations.
 */
class ContentBlockController extends Controller
{
    /**
     * Display a listing of content blocks.
     * Supports AJAX requests with search, filter, and pagination.
     */
    public function index(Request $request)
    {
        // Check if this is an AJAX/API request
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return $this->indexApi($request);
        }

        // Traditional view response
        $blocks = SiteContentBlock::ordered()->get();
        return view('admin.content-blocks.index', compact('blocks'));
    }

    /**
     * API endpoint for listing blocks with search, filter, and pagination
     */
    protected function indexApi(Request $request): JsonResponse
    {
        $query = SiteContentBlock::query()->with(['creator', 'updater']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('key', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
                  ->orWhere('content', 'ilike', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by active status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Order by sort_order
        $query->ordered();

        // Pagination
        $perPage = $request->input('per_page', 25);
        $blocks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ContentBlockResource::collection($blocks),
            'meta' => [
                'current_page' => $blocks->currentPage(),
                'last_page' => $blocks->lastPage(),
                'per_page' => $blocks->perPage(),
                'total' => $blocks->total(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new block.
     */
    public function create(): View
    {
        return view('admin.content-blocks.form', [
            'block' => null,
            'title' => 'Create New Content Block',
        ]);
    }

    /**
     * Store a newly created block.
     * Supports both traditional form submission and AJAX requests.
     */
    public function store(ContentBlockRequest $request)
    {
        $validated = $request->validated();

        // Set default sort_order if not provided
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = SiteContentBlock::max('sort_order') + 1;
        }

        $block = SiteContentBlock::create($validated);

        // AJAX/API response
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Content block created successfully',
                'data' => new ContentBlockResource($block->load(['creator', 'updater'])),
            ], 201);
        }

        // Traditional redirect response
        return redirect()->route('admin.content-blocks.index')
            ->with('success', 'Content block created successfully.');
    }

    /**
     * Display the specified block.
     * API endpoint for retrieving a single block.
     */
    public function show(SiteContentBlock $contentBlock): JsonResponse
    {
        $contentBlock->load(['creator', 'updater']);

        return response()->json([
            'success' => true,
            'data' => new ContentBlockResource($contentBlock),
        ]);
    }

    /**
     * Show the form for editing the specified block.
     */
    public function edit(SiteContentBlock $contentBlock): View
    {
        return view('admin.content-blocks.form', [
            'block' => $contentBlock,
            'title' => 'Edit Content Block: ' . $contentBlock->title,
        ]);
    }

    /**
     * Update the specified block.
     * Supports both traditional form submission and AJAX requests.
     */
    public function update(ContentBlockRequest $request, SiteContentBlock $contentBlock)
    {
        $validated = $request->validated();

        $contentBlock->update($validated);

        // AJAX/API response
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return response()->json([
                'success' => true,
                'message' => 'Content block updated successfully',
                'data' => new ContentBlockResource($contentBlock->fresh()->load(['creator', 'updater'])),
            ]);
        }

        // Traditional redirect response
        return redirect()->route('admin.content-blocks.index')
            ->with('success', 'Content block updated successfully.');
    }

    /**
     * Remove the specified block.
     * Supports both traditional form submission and AJAX requests.
     */
    public function destroy(Request $request, SiteContentBlock $contentBlock)
    {
        $this->authorize('manage-content-blocks');

        $blockTitle = $contentBlock->title;
        $contentBlock->delete();

        // AJAX/API response
        if ($request->wantsJson() || $request->is('admin/api/*')) {
            return response()->json([
                'success' => true,
                'message' => "Content block '{$blockTitle}' deleted successfully",
            ]);
        }

        // Traditional redirect response
        return redirect()->route('admin.content-blocks.index')
            ->with('success', 'Content block deleted successfully.');
    }

    /**
     * Duplicate an existing block.
     * Creates a copy with "(Copy)" suffix.
     */
    public function duplicate(SiteContentBlock $contentBlock): JsonResponse
    {
        $this->authorize('manage-content-blocks');

        // Create a copy of the block
        $copy = $contentBlock->replicate();
        $copy->title = $contentBlock->title . ' (Copy)';
        $copy->key = $contentBlock->key . '_copy';
        $copy->sort_order = SiteContentBlock::max('sort_order') + 1;
        $copy->save();

        return response()->json([
            'success' => true,
            'message' => 'Content block duplicated successfully',
            'data' => new ContentBlockResource($copy->load(['creator', 'updater'])),
        ], 201);
    }

    /**
     * Toggle block active state via AJAX.
     */
    public function toggle(SiteContentBlock $contentBlock): JsonResponse
    {
        $this->authorize('manage-content-blocks');

        $contentBlock->is_active = !$contentBlock->is_active;
        $contentBlock->save();

        return response()->json([
            'success' => true,
            'is_active' => $contentBlock->is_active,
            'message' => $contentBlock->is_active ? 'Content block activated' : 'Content block deactivated',
        ]);
    }

    /**
     * Reorder blocks via AJAX (Drag & Drop).
     */
    public function reorder(Request $request): JsonResponse
    {
        $this->authorize('manage-content-blocks');

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:site_content_blocks,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            SiteContentBlock::where('id', $item['id'])
                ->update(['sort_order' => $item['order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Content block order updated successfully',
        ]);
    }

    /**
     * Bulk delete multiple blocks.
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $this->authorize('manage-content-blocks');

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:site_content_blocks,id',
        ]);

        $count = SiteContentBlock::whereIn('id', $validated['ids'])->count();
        SiteContentBlock::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} content block(s) deleted successfully",
            'count' => $count,
        ]);
    }
}
