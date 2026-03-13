<?php
namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::with(['roles', 'branch', 'store'])
                ->when($request->search, fn($q, $s) =>
                    $q->where(fn($q2) =>
                        $q2->where('name', 'like', "%{$s}%")
                            ->orWhere('email', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%")
                    )
                )
                ->when($request->role, fn($q, $r) =>
                    $q->whereHas('roles', fn($q2) => $q2->where('name', $r))
                )
                ->when($request->filled('account_status'), fn($q) =>
                    $q->where('account_status', $request->account_status)
                )
                ->when($request->branch_id, fn($q, $b) =>
                    $q->where('branch_id', $b)
                )
                ->latest();

            return $this->paginated($query->paginate(15));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {

            $data = $request->validate([
                'name'           => ['required', 'string', 'max:150'],
                'email'          => ['required', 'email', 'unique:users,email'],
                'phone'          => ['nullable', 'string', 'max:20', 'unique:users,phone'],
                'password'       => ['required', 'string', 'min:8', 'confirmed'],
                'role'           => ['required', 'string', Rule::exists('roles', 'name')],
                'branch_id'      => ['nullable', 'exists:branches,id'],
                'store_id'       => ['nullable', 'exists:stores,id'],
                'account_status' => ['sometimes', 'in:active,inactive,suspended,pending'],
                'national_id'    => ['nullable', 'string', 'max:20', 'unique:users,national_id'],
                'gender'         => ['nullable', 'in:male,female,other'],
                'date_of_birth'  => ['nullable', 'date'],
                'address'        => ['nullable', 'string'],
            ]);

            $user = User::create([
                 ...$data,
                'password'            => Hash::make($data['password']),
                'password_changed_at' => now(),
            ]);

            $user->assignRole($data['role']);

            return $this->created(
                new UserResource($user->load(['roles', 'branch', 'store'])),
                'User created successfully'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = User::with(['roles', 'branch', 'store'])->findOrFail($id);

            return $this->success(new UserResource($user));
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {

            $user = User::findOrFail($id);

            $data = $request->validate([
                'name'           => ['sometimes', 'string', 'max:150'],
                'email'          => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
                'phone'          => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
                'role'           => ['sometimes', 'string', Rule::exists('roles', 'name')],
                'branch_id'      => ['nullable', 'exists:branches,id'],
                'store_id'       => ['nullable', 'exists:stores,id'],
                'account_status' => ['sometimes', 'in:active,inactive,suspended,pending'],
                'national_id'    => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
                'gender'         => ['nullable', 'in:male,female,other'],
                'date_of_birth'  => ['nullable', 'date'],
                'address'        => ['nullable', 'string'],
            ]);

            $user->update($data);

            if (! empty($data['role'])) {
                $user->syncRoles([$data['role']]);
            }

            return $this->success(
                new UserResource($user->fresh(['roles', 'branch', 'store'])),
                'User updated successfully'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function resetPassword(Request $request, int $id): JsonResponse
    {
        try {

            $data = $request->validate([
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = User::findOrFail($id);
            $user->update([
                'password'              => Hash::make($data['password']),
                'password_changed_at'   => now(),
                'failed_login_attempts' => 0,
                'locked_until'          => null,
            ]);

            return $this->success(null, 'Password reset successfully');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        try {

            $data = $request->validate([
                'account_status' => ['required', 'in:active,inactive,suspended'],
            ]);

            $user = User::findOrFail($id);
            $user->update(['account_status' => $data['account_status']]);

            return $this->success(
                new UserResource($user->fresh(['roles', 'branch', 'store'])),
                'User status updated'
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {

            $user = User::findOrFail($id);

            if ($user->id === Auth::id()) {
                return $this->error('You cannot delete your own account.', 422);
            }

            $user->delete();

            return $this->success(null, 'User deleted successfully');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }

    public function roles(): JsonResponse
    {
        try {
            $roles = Role::orderBy('name')->get(['id', 'name']);

            return $this->success($roles);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
}
