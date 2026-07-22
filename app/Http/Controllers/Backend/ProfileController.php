<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ProfileController extends Controller
{
    public function index()
    {
        return view('backend.profile.index');
    }
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,' . Auth::user()->id],
            'image' => ['nullable', 'mimetypes:image/jpeg,image/png,image/gif,image/webp', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:20'],
            'outlet_name' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($request->hasFile('image')) {
            if ($user->image && File::exists(public_path($user->image))) {
                File::delete(public_path($user->image));
            }
            $image = $request->file('image');
            $imageName = rand() . '_' . $image->getClientOriginalName();
            // Store in storage/app/public/uploads
            $image->storeAs('uploads', $imageName, 'public');
            
            // Path accessible via web (requires php artisan storage:link)
            $path = "/storage/uploads/" . $imageName;
            $user->image = $path;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        
        $user->outlet_name = $request->outlet_name;
        $user->address = $request->address;

        $user->save();
        return redirect()->back()->with('success', 'Profile updated successfully');
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8']
        ]);
        $request->user()->update([
            'password' => bcrypt($request->password)
        ]);
        Toastr::success('Password updated successfully');
        return redirect()->back();
    }
}
