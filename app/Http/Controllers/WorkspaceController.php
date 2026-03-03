<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkspaceController extends Controller
{
    public function index()
    {
        $workspaces = auth()->user()->workspaces()->withPivot('role')->get();
        return view('workspaces.index', compact('workspaces'));
    }

    public function create()
    {
        return view('workspaces.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($request) {
            $workspace = Workspace::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name) . '-' . uniqid(),
                'owner_id' => auth()->id(),
            ]);

            WorkspaceUser::create([
                'workspace_id' => $workspace->id,
                'user_id' => auth()->id(),
                'role' => 'owner',
            ]);
        });

        return redirect()->route('workspaces.index')->with('success', 'Workspace created successfully.');
    }

    public function edit(Workspace $workspace)
    {
        // Check access
        $this->authorizeAccess($workspace);

        $workspace->load(['users' => function ($query) {
            $query->orderBy('workspace_users.created_at');
        }]);

        return view('workspaces.edit', compact('workspace'));
    }

    public function update(Request $request, Workspace $workspace)
    {
        $this->authorizeAccess($workspace, 'owner');

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $workspace->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . substr($workspace->id, 0, 5),
        ]);

        return redirect()->route('workspaces.index')->with('success', 'Workspace updated successfully.');
    }

    public function destroy(Workspace $workspace)
    {
        $this->authorizeAccess($workspace, 'owner');

        // Prevent deleting the last workspace
        if (auth()->user()->workspaces()->count() <= 1) {
            return back()->with('error', 'You cannot delete your only workspace.');
        }

        $workspace->delete();

        // If the deleted workspace was the active one, clear session
        if (session('active_workspace_id') === $workspace->id) {
            session()->forget('active_workspace_id');
        }

        return redirect()->route('workspaces.index')->with('success', 'Workspace deleted successfully.');
    }

    // --- Member Management ---

    public function addMember(Request $request, Workspace $workspace)
    {
        $this->authorizeAccess($workspace, ['owner', 'editor']);

        $request->validate([
            'email' => 'required|email|exists:users,email',
            'role' => 'required|in:editor,viewer',
        ], [
            'email.exists' => 'User with this email not found.',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if user is already a member
        if ($workspace->users()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User is already a member of this workspace.');
        }

        WorkspaceUser::create([
            'workspace_id' => $workspace->id,
            'user_id' => $user->id,
            'role' => $request->role,
        ]);

        return back()->with('success', 'Member added successfully.');
    }

    public function updateRole(Request $request, Workspace $workspace, User $user)
    {
        $this->authorizeAccess($workspace, 'owner');

        $request->validate([
            'role' => 'required|in:editor,viewer',
        ]);

        // Cannot change owner role from here
        $pivot = WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)->firstOrFail();

        if ($pivot->role === 'owner') {
            return back()->with('error', 'Cannot change the role of the workspace owner.');
        }

        $pivot->update(['role' => $request->role]);

        return back()->with('success', 'Member role updated.');
    }

    public function removeMember(Workspace $workspace, User $user)
    {
        $this->authorizeAccess($workspace, 'owner');

        $pivot = WorkspaceUser::where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)->firstOrFail();

        if ($pivot->role === 'owner') {
            return back()->with('error', 'Cannot remove the workspace owner.');
        }

        $pivot->delete();

        return back()->with('success', 'Member removed.');
    }

    // --- Switch Active Workspace ---
    public function switch(Workspace $workspace)
    {
        $this->authorizeAccess($workspace);
        session(['active_workspace_id' => $workspace->id]);
        return redirect()->back()->with('success', 'Switched to workspace: ' . $workspace->name);
    }

    // --- Helper function for checking access ---
    private function authorizeAccess(Workspace $workspace, $requiredRole = null)
    {
        $pivot = $workspace->workspaceUsers()->where('user_id', auth()->id())->first();

        if (!$pivot) {
            abort(403, 'Unauthorized actions on this workspace.');
        }

        if ($requiredRole) {
            $roles = is_array($requiredRole) ? $requiredRole : [$requiredRole];
            if (!in_array($pivot->role, $roles) && $pivot->role !== 'owner') {
                abort(403, 'You do not have permission to perform this action.');
            }
        }
    }
}
