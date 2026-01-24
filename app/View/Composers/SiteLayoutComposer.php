<?php

namespace App\View\Composers;

use App\Models\NavigationMenu;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * SiteLayoutComposer
 * 
 * Provides navigation menus and settings to portal (site-level) views.
 * Site menus have journal_id = NULL.
 */
class SiteLayoutComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        // Cache site menus for 5 minutes
        $menus = Cache::remember('site_navigation_menus', 300, function () {
            return $this->loadSiteMenus();
        });

        // Share data with the view
        $view->with([
            'primaryMenu' => $menus['primary'] ?? null,
            'userMenu' => $menus['user_top'] ?? null,
            'footerMenu' => $menus['footer'] ?? null,
        ]);
    }

    /**
     * Load all site-level navigation menus.
     */
    protected function loadSiteMenus(): array
    {
        $menus = [];

        foreach (NavigationMenu::getLocations() as $location => $name) {
            $menu = NavigationMenu::where('journal_id', null)
                ->where('location', $location)
                ->where('is_active', true)
                ->with(['items' => function ($query) {
                    $query->where('is_active', true)
                        ->whereNull('parent_id')
                        ->orderBy('order')
                        ->with(['activeChildren']);
                }])
                ->first();

            $menus[$location] = $menu;
        }

        return $menus;
    }

    /**
     * Clear the cached site menus.
     */
    public static function clearCache(): void
    {
        Cache::forget('site_navigation_menus');
    }
}
