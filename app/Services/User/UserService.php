<?php

namespace App\Services\User;

use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserService
{
    public function createUser(array $data, $imageFile = null)
    {
        $imagePath = null;
        if ($imageFile) {
            $imageName = rand() . '_' . $imageFile->getClientOriginalName();
            $imageFile->move(public_path('uploads'), $imageName);
            $imagePath = 'uploads/' . $imageName;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'image' => $imagePath,
            'status' => $data['status'],
            'role_id' => $data['user_role'],
            'discount_type' => $data['discount_type'] ?? null,
            'discount_value' => $data['discount_value'] ?? null,
            'min_order_amount' => $data['min_order_amount'] ?? null,
        ]);

        $role = Role::findById($data['user_role']);
        $user->assignRole($role->name);

        return $user;
    }

    public function updateUser(User $user, array $data, $imageFile = null)
    {
        if ($imageFile) {
            if ($user->image && File::exists(public_path($user->image))) {
                File::delete(public_path($user->image));
            }
            $imageName = rand() . '_' . $imageFile->getClientOriginalName();
            $imageFile->move(public_path('uploads'), $imageName);
            $user->image = 'uploads/' . $imageName;
        }

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'] ?? null;
        $user->status = $data['status'];
        $user->role_id = $data['user_role'];
        $user->discount_type = $data['discount_type'] ?? null;
        $user->discount_value = $data['discount_value'] ?? null;
        $user->min_order_amount = $data['min_order_amount'] ?? null;
        $user->save();

        $role = Role::findById($data['user_role']);
        $user->syncRoles([$role->name]);

        return $user;
    }

    public function deleteUser(User $user)
    {
        if ($user->id === 1) {
            throw new \Exception('Main Admin Cannot be Deleted!');
        }

        if ($user->image && File::exists(public_path($user->image))) {
            File::delete(public_path($user->image));
        }

        return $user->delete();
    }
}
