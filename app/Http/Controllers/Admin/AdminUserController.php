<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function __construct(private readonly ActivityLogger $activityLogger) {}

    public function index(Request $request): Response
    {
        abort_unless($request->user()?->hasAdminPermission('manage_admins'), 403);

        $admins = User::query()
            ->whereIn('role', User::adminRoles())
            ->latest()
            ->paginate(15)
            ->through(fn (User $admin) => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
                'role_label' => User::adminRoleLabels()[$admin->role] ?? $admin->role,
                'status' => $admin->status,
                'created_at' => $admin->created_at?->toIso8601String(),
            ]);

        return Inertia::render('admin/admin-users/Index', [
            'admins' => $admins,
            'roles' => User::adminRoleLabels(),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()?->hasAdminPermission('manage_admins'), 403);

        return Inertia::render('admin/admin-users/Create', [
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(StoreAdminUserRequest $request): RedirectResponse
    {
        abort_unless($request->user()?->hasAdminPermission('manage_admins'), 403);

        $admin = User::create([
            'name' => $request->string('name')->value(),
            'email' => $request->string('email')->value(),
            'password' => Hash::make($request->string('password')->value()),
            'role' => $request->string('role')->value(),
            'status' => $request->string('status')->value(),
            'email_verified_at' => now(),
        ]);

        $this->activityLogger->log(
            ActivityLog::ACTION_ADMIN_USER_CREATED,
            "{$request->user()->name} created admin user {$admin->name} ({$admin->role}).",
            $request->user(),
            $admin,
            ['role' => $admin->role],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Admin user created.']);

        return to_route('admin.admin-users.index');
    }

    public function edit(Request $request, User $adminUser): Response
    {
        abort_unless($request->user()?->hasAdminPermission('manage_admins'), 403);
        abort_unless(in_array($adminUser->role, User::adminRoles(), true), 404);
        abort_unless($request->user()->canManageAdminUser($adminUser), 403);

        return Inertia::render('admin/admin-users/Edit', [
            'admin' => [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'email' => $adminUser->email,
                'role' => $adminUser->role,
                'status' => $adminUser->status,
            ],
            'roles' => User::adminRoleLabels(),
            'canChangeRole' => $adminUser->role !== User::ROLE_OWNER || $adminUser->is($request->user()),
        ]);
    }

    public function update(UpdateAdminUserRequest $request, User $adminUser): RedirectResponse
    {
        abort_unless($request->user()?->hasAdminPermission('manage_admins'), 403);
        abort_unless(in_array($adminUser->role, User::adminRoles(), true), 404);
        abort_unless($request->user()->canManageAdminUser($adminUser), 403);

        $adminUser->fill($request->only(['name', 'email', 'status']));

        if ($request->filled('password')) {
            $adminUser->password = Hash::make($request->string('password')->value());
        }

        if ($request->filled('role') && ($adminUser->role !== User::ROLE_OWNER || $adminUser->is($request->user()))) {
            $adminUser->role = $request->string('role')->value();
        }

        $adminUser->save();

        $this->activityLogger->log(
            ActivityLog::ACTION_ADMIN_USER_UPDATED,
            "{$request->user()->name} updated admin user {$adminUser->name}.",
            $request->user(),
            $adminUser,
            ['role' => $adminUser->role, 'status' => $adminUser->status],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Admin user updated.']);

        return to_route('admin.admin-users.index');
    }

    /**
     * @return array<string, string>
     */
    private function assignableRoles(): array
    {
        return array_intersect_key(
            User::adminRoleLabels(),
            array_flip([User::ROLE_ADMIN, User::ROLE_STAFF]),
        );
    }
}
