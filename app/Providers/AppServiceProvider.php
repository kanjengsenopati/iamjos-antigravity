<?php

namespace App\Providers;

use App\Models\Issue;
use App\Models\Submission;
use App\Models\ReviewAssignment;
use App\Policies\IssuePolicy;
use App\Policies\SubmissionPolicy;
use App\Policies\ReviewAssignmentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\View\Composers\PublicLayoutComposer;
use App\View\Composers\SiteLayoutComposer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.tailwind');
        // Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        \Carbon\Carbon::setLocale('id');

        // Register Policies
        Gate::policy(Submission::class, SubmissionPolicy::class);
        Gate::policy(ReviewAssignment::class, ReviewAssignmentPolicy::class);
        Gate::policy(Issue::class, IssuePolicy::class);

        // Super Admin bypass all policies
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Super Admin')) {
                return true;
            }
        });

        // Register View Composer for Public Layout (both regular and component versions)
        View::composer([
            'layouts.public',
            'components.layouts.public',
        ], PublicLayoutComposer::class);

        // Also register PublicSidebarComposer to the layouts because they check $sidebarBlocks->isNotEmpty()
        View::composer([
            'layouts.public',
            'components.layouts.public',
        ], \App\View\Composers\PublicSidebarComposer::class);

        // Register View Composer for Public Sidebar
        View::composer([
            'components.public.sidebar',
        ], \App\View\Composers\PublicSidebarComposer::class);

        // Register View Composer for Portal/Site Layout
        View::composer([
            'layouts.portal',
            'site.*',
            'components.site.navbar',
            'components.site.footer',
        ], SiteLayoutComposer::class);
    }
}
