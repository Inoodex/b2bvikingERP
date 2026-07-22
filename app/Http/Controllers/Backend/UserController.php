<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\DataTables\UsersDataTable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
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
        $roles = Role::all();
        return view('backend.authorization.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => ['nullable', 'image', 'max:2048'],
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'max:255'],
            'password' => ['required', 'min:8'],
            'status' => ['required', 'boolean'],
            'user_role' => ['required', 'exists:roles,id'],
            'discount_type' => ['nullable', 'in:flat,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0']
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = rand() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads'), $imageName);
            $imagePath = 'uploads/' . $imageName;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'image' => $imagePath,
            'status' => $request->status,
            'role_id' => $request->user_role,
            'discount_type' => $request->discount_type,
            'discount_value' => $request->discount_value,
            'min_order_amount' => $request->min_order_amount,
        ]);

        $role = Role::findById($request->user_role);
        $user->assignRole($role->name);

        return redirect()->route('admin.users.index')->with('success', 'User Created Successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        // role_id is now a physical column

        return view('backend.authorization.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'image' => ['nullable', 'image', 'max:2048'],
            'name' => ['required', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $id],
            'phone' => ['nullable', 'max:255'],
            'status' => ['required', 'boolean'],
            'user_role' => ['required', 'exists:roles,id'],
            'discount_type' => ['nullable', 'in:flat,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0']
        ]);

        $user = User::findOrFail($id);

        if ($request->hasFile('image')) {
            if ($user->image && File::exists(public_path($user->image))) {
                File::delete(public_path($user->image));
            }
            $image = $request->file('image');
            $imageName = rand() . '_' . $image->getClientOriginalName();
            $image->move(public_path('uploads'), $imageName);
            $user->image = 'uploads/' . $imageName;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->status = $request->status;
        $user->role_id = $request->user_role;
        $user->discount_type = $request->discount_type;
        $user->discount_value = $request->discount_value;
        $user->min_order_amount = $request->min_order_amount;
        $user->save();

        $role = Role::findById($request->user_role);
        $user->syncRoles([$role->name]);

        return redirect()->route('admin.users.index')->with('success', 'User Updated Successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if ($user->id === 1) {
            return response(['status' => 'error', 'message' => 'Main Admin Cannot be Deleted!']);
        }

        if ($user->image && File::exists(public_path($user->image))) {
            File::delete(public_path($user->image));
        }

        $user->delete();

        return response(['status' => 'success', 'message' => 'Deleted Successfully!']);
    }

    /**
     * Change user status.
     */
    public function changeStatus(Request $request)
    {
        $user = User::findOrFail($request->id);
        $user->status = $request->status == 'true' ? 1 : 0;
        $user->save();

        return response(['status' => 'success', 'message' => 'Status Updated Successfully!']);
    }
}
