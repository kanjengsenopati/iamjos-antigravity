<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Submission;

class EnsureEditorIsAssigned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Lewati request pembacaan (GET/HEAD)
        if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
            return $next($request);
        }

        // 2. Lewati route-route yang dikecualikan (misalnya penugasan editor itu sendiri)
        $routeName = $request->route() ? $request->route()->getName() : null;
        $exemptRoutes = [
            'journal.workflow.assign-editor',
            'journal.workflow.remove-editor',
        ];

        if (in_array($routeName, $exemptRoutes)) {
            return $next($request);
        }

        // 3. Resolusi model Submission dari parameter route
        $submission = $request->route('submission');
        if ($submission) {
            if (!$submission instanceof Submission) {
                $submission = Submission::where('id', $submission)
                    ->orWhere('slug', $submission)
                    ->orWhere('seq_id', $submission)
                    ->first();
            }

            if ($submission) {
                $user = auth()->user();
                if ($user) {
                    // Check if current user has Super Admin or Journal Manager roles (which bypass restrictions)
                    // But if it's a standard Editor/Section Editor, enforce assignment.
                    // OJS 3.3 rules: Level 0 (Super Admin) & Level 1 (Manager/Admin) have full control.
                    // Level 2 (Editor/Section Editor) are constrained to their assignments.
                    
                    // Let's check if the user has a role of Super Admin or Admin/Manager in the journal
                    $journal = current_journal();
                    $isManagerOrAdmin = false;
                    if ($journal) {
                        $isManagerOrAdmin = $user->hasRole(\App\Models\Role::ROLE_SUPERADMIN) || 
                            \DB::table('journal_user_roles')
                                ->join('roles', 'journal_user_roles.role_id', '=', 'roles.id')
                                ->where('journal_user_roles.user_id', $user->id)
                                ->where('journal_user_roles.journal_id', $journal->id)
                                ->whereIn('roles.name', ['Super Admin', 'Admin', 'Journal Manager'])
                                ->exists();
                    }

                    if (!$isManagerOrAdmin) {
                        // Check if the submission has any active editor assignment
                        $hasAssignedEditors = $submission->editorialAssignments()
                            ->where('is_active', true)
                            ->exists();

                        if ($hasAssignedEditors) {
                            // Check if current user is one of the assigned editors
                            $isAssigned = $submission->editorialAssignments()
                                ->where('is_active', true)
                                ->where('user_id', $user->id)
                                ->exists();

                            if (!$isAssigned) {
                                $message = 'Anda tidak ditugaskan sebagai editor untuk artikel ini.';
                                if ($request->wantsJson()) {
                                    return response()->json([
                                        'success' => false,
                                        'message' => $message
                                    ], 403);
                                }
                                return back()->with('error', $message);
                            }
                        } else {
                            // Jika belum ada editor yang diassign, tolak keputusan editorial
                            $message = 'Silakan tugaskan editor terlebih dahulu untuk memproses artikel ini.';
                            if ($request->wantsJson()) {
                                return response()->json([
                                    'success' => false,
                                    'message' => $message
                                ], 403);
                            }
                            return back()->with('error', $message);
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
