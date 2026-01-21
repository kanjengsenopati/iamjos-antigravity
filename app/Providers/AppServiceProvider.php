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
use App\View\Composers\PublicLayoutComposer;

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
    }
}
