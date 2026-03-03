<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class WorkspaceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // If no active workspace is set in session, try to set the first available one
            if (!Session::has('active_workspace_id')) {
                $workspace = $user->workspaces()->first();

                if ($workspace) {
                    Session::put('active_workspace_id', $workspace->id);
                }
            }
        }

        return $next($request);
    }
}
