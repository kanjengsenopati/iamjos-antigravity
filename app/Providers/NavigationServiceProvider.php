<?php

namespace App\Providers;

use App\Models\NavigationMenu;
use App\Models\SidebarBlock;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class NavigationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Compose navigation data for public layouts
        View::composer(['components.journal-public-layout', 'components.public-layout'], function ($view) {
            $journal = current_journal();
            $journalId = $journal?->id;

            // Get Primary Navigation Menu
            $primaryMenu = NavigationMenu::getMenu(NavigationMenu::LOCATION_PRIMARY, $journalId);
            $primaryNavItems = $primaryMenu ? $primaryMenu->tree : collect();

            // Get User Top Navigation Menu
            $userMenu = NavigationMenu::getMenu(NavigationMenu::LOCATION_USER_TOP, $journalId);
            $userNavItems = $userMenu ? $userMenu->tree : collect();

            // Get Footer Navigation Menu
            $footerMenu = NavigationMenu::getMenu(NavigationMenu::LOCATION_FOOTER, $journalId);
            $footerNavItems = $footerMenu ? $footerMenu->tree : collect();

            // Get Sidebar Blocks
            $sidebarBlocks = $journalId
                ? SidebarBlock::getActiveBlocks($journalId, 'right')
                : collect();

            $leftSidebarBlocks = $journalId
                ? SidebarBlock::getActiveBlocks($journalId, 'left')
                : collect();

            // Share with view
            $view->with([
                'primaryNavItems' => $primaryNavItems,
                'userNavItems' => $userNavItems,
                'footerNavItems' => $footerNavItems,
                'sidebarBlocks' => $sidebarBlocks,
                'leftSidebarBlocks' => $leftSidebarBlocks,
            ]);
        });

        // Compose navigation data for admin settings page
        View::composer(['admin.journals.settings-navigation', 'journal.admin.settings.navigation'], function ($view) {
            $journal = current_journal();

            if (!$journal) {
                return;
            }

            // Get all menus for this journal
            $menus = NavigationMenu::where('journal_id', $journal->id)
                ->with(['items' => function ($q) {
                    $q->orderBy('order');
                }])
                ->get();

            // Get all sidebar blocks
            $sidebarBlocks = SidebarBlock::forJournal($journal->id)
                ->ordered()
                ->get();

            // Available system blocks
            $availableSystemBlocks = SidebarBlock::getSystemBlocks();

            $view->with([
                'navigationMenus' => $menus,
                'sidebarBlocks' => $sidebarBlocks,
                'availableSystemBlocks' => $availableSystemBlocks,
            ]);
        });
    }
}
