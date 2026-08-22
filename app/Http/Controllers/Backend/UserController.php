<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\DataTables\UsersDataTable;
use App\Models\Company;
use App\Models\Department;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Backend\User\StoreUserRequest;
use App\Http\Requests\Backend\User\UpdateUserRequest;
use App\Services\User\UserService;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(UsersDataTable $dataTable)
    {
        return $dataTable->render('backend.authorization.users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::withCount('permissions')->get();
        $companies = Company::where('status', 1)->get();
        $departments = Department::where('status', 1)->get();
        $outlets = Outlet::where('status', 1)->get();

        return view('backend.authorization.users.create', compact('roles', 'companies', 'departments', 'outlets'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $this->userService->createUser($request->validated(), $request->file('image'));
            return redirect()->route('admin.users.index')->with('success', 'User Created Successfully!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::withCount('permissions')->get();
        $companies = Company::where('status', 1)->get();
        $departments = Department::where('status', 1)->get();
        $outlets = Outlet::where('status', 1)->get();

        return view('backend.authorization.users.edit', compact('user', 'roles', 'companies', 'departments', 'outlets'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id)
    {
        try {
            $user = User::findOrFail($id);
            $this->userService->updateUser($user, $request->validated(), $request->file('image'));
            return redirect()->route('admin.users.index')->with('success', 'User Updated Successfully!');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $user = User::findOrFail($id);
            if ($user->id === 1) {
                return response(['status' => 'error', 'message' => 'Super Admin cannot be deleted!']);
            }
            $this->userService->deleteUser($user);
            return response(['status' => 'success', 'message' => 'User deleted successfully!']);
        } catch (\Throwable $e) {
            return response(['status' => 'error', 'message' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            $user = User::findOrFail($request->id);
            $user->status = $request->status == 'true' ? 1 : 0;
            $user->save();
            return response(['message' => 'Status has been updated!']);
        } catch (\Throwable $e) {
            return response(['message' => 'Failed to update status'], 500);
        }
    }
}
