<?php

namespace App\Http\Controllers\Admin;

use App\Models\SitePage;
use App\Models\SiteContentBlock;
use App\Models\NavigationMenu;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class PublicPageController extends Controller
{
    /**
     * Display the unified public page management interface
     */
    public function index(Request $request): View
    {
        // Determine active tab
        $activeTab = $this->determineActiveTab($request);
        
        // Fetch data for all tabs
        $sitePagesData = $this->getSitePagesData();
        $pageBuilderData = $this->getPageBuilderData();
        $siteNavData = $this->getSiteNavigationData();
        
        return view('admin.public-page.index', [
            'activeTab' => $activeTab,
            'sitePagesData' => $sitePagesData,
            'pageBuilderData' => $pageBuilderData,
            'siteNavData' => $siteNavData,
        ]);
    }
    
    /**
     * Determine which tab should be active
     */
    protected function determineActiveTab(Request $request): string
    {
        // Priority 1: Query parameter
        if ($request->has('tab')) {
            $tab = $request->query('tab');
            if (in_array($tab, ['pages', 'builder', 'nav'])) {
                return $tab;
            }
        }
        
        // Priority 2: Route context (for backward compatibility)
        if ($request->routeIs('admin.site-pages.*')) {
            return 'pages';
        }
        if ($request->routeIs('admin.site.appearance.*')) {
            return 'builder';
        }
        if ($request->routeIs('admin.site-navigation.*')) {
            return 'nav';
        }
        
        // Default: Site Pages
        return 'pages';
    }
    
    /**
     * Get data for Site Pages tab
     */
    protected function getSitePagesData(): array
    {
        try {
            return [
                'pages' => SitePage::orderBy('order')->get(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch site pages data: ' . $e->getMessage());
            return ['pages' => collect()];
        }
    }
    
    /**
     * Get data for Page Builder tab
     */
    protected function getPageBuilderData(): array
    {
        try {
            $blocks = SiteContentBlock::orderBy('order')->get();
            $blocksByCategory = $blocks->groupBy('category');
            $journals = Journal::select('id', 'name', 'abbreviation', 'slug', 'logo_path')
                ->where('enabled', true)
                ->orderBy('name')
                ->get();
                
            return [
                'blocks' => $blocks,
                'blocksByCategory' => $blocksByCategory,
                'journals' => $journals,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch page builder data: ' . $e->getMessage());
            return [
                'blocks' => collect(),
                'blocksByCategory' => collect(),
                'journals' => collect(),
            ];
        }
    }
    
    /**
     * Get data for Site Navigation tab
     */
    protected function getSiteNavigationData(): array
    {
        try {
            $menus = collect();
            
            // Get all navigation menu locations
            $locations = [
                'main' => 'Main Navigation',
                'footer' => 'Footer Navigation',
                'secondary' => 'Secondary Navigation',
            ];
            
            foreach ($locations as $location => $name) {
                $menu = NavigationMenu::firstOrCreate(
                    ['journal_id' => null, 'area_name' => $location],
                    ['title' => $name, 'is_active' => true]
                );
                $menu->load(['items' => fn($q) => $q->orderBy('order')]);
                $menus->push($menu);
            }
            
            $availableRoutes = $this->getAvailableRoutes();
            $sitePages = SitePage::where('published', true)->orderBy('order')->get(['id', 'title', 'slug']);
            
            return [
                'menus' => $menus,
                'availableRoutes' => $availableRoutes,
                'sitePages' => $sitePages,
            ];
        } catch (\Exception $e) {
            Log::error('Failed to fetch site navigation data: ' . $e->getMessage());
            return [
                'menus' => collect(),
                'availableRoutes' => [],
                'sitePages' => collect(),
            ];
        }
    }
    
    /**
     * Get available routes for site menu items
     */
    protected function getAvailableRoutes(): array
    {
        return [
            ['name' => 'portal.home', 'label' => 'Homepage', 'params' => []],
            ['name' => 'portal.journals', 'label' => 'Journals', 'params' => []],
            ['name' => 'portal.about', 'label' => 'About', 'params' => []],
            ['name' => 'portal.contact', 'label' => 'Contact', 'params' => []],
            ['name' => 'login', 'label' => 'Login', 'params' => []],
            ['name' => 'register', 'label' => 'Register', 'params' => []],
        ];
    }
}
