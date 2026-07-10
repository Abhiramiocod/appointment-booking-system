<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Http\Resources\StaffSearchResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Display a listing of staff.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $staff = User::query()
            ->where('role', UserRole::STAFF)

            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->search;

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%");
                    });
                }
            )

            ->orderBy(
                $request->input('sort_by', 'name'),
                $request->input('sort_dir', 'asc')
            )

            ->paginate(
                $request->input('per_page', 15)
            );

        return UserResource::collection($staff);
    }

    /**
     * Store a newly created staff member.
     */
    public function store(StoreStaffRequest $request)
    {
        Gate::authorize('create', User::class);

        $staff = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => UserRole::STAFF,
            'image' => $request->image,
        ]);

        return new UserResource($staff);
    }

    /**
     * Display the specified staff member.
     */
    public function show(User $staff)
    {
        Gate::authorize('view', $staff);

        abort_if($staff->role !== UserRole::STAFF, 404);

        return new UserResource($staff);
    }

    /**
     * Update the specified staff member.
     */
    public function update(UpdateStaffRequest $request, User $staff)
    {
        Gate::authorize('update', $staff);

        abort_if($staff->role !== UserRole::STAFF, 404);

        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $staff->update($data);

        return new UserResource($staff->fresh());
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy(User $staff)
    {
        Gate::authorize('delete', $staff);

        abort_if($staff->role !== UserRole::STAFF, 404);

        $staff->delete();

        return response()->json([
            'message' => 'Staff deleted successfully.',
        ]);
    }

    public function search(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $staff = User::query()
            ->where('role', UserRole::STAFF)
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = trim($request->search);

                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'ILIKE', "%{$search}%")
                            ->orWhere('email', 'ILIKE', "%{$search}%");
                    });
                }
            )
            ->orderBy('name')
            ->limit(5)
            ->get([
                'id',
                'name',
            ]);

        return StaffSearchResource::collection($staff);
    }
}
