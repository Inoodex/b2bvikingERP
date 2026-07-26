<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\GeneralSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class SettingController extends Controller
{
    public function index()
    {
        return view('backend.settings.index');
    }

    public function general()
    {
        $setting = GeneralSetting::first();

        return view('backend.settings.general', compact('setting'));
    }

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'site_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'site_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,avif', 'max:4096'],
        ]);

        $setting = GeneralSetting::first();
        $logoPath = $setting?->site_logo;

        if ($request->hasFile('site_logo')) {
            $file = $request->file('site_logo');
            $directory = public_path('uploads/settings');

            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }

            $filename = 'site-logo-' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($directory, $filename);
            $logoPath = 'uploads/settings/' . $filename;
        }

        GeneralSetting::updateOrCreate(
            ['id' => 1],
            [
                'site_name' => $request->site_name,
                'contact_email' => $request->contact_email,
                'address' => $request->address,
                'site_logo' => $logoPath,
            ]
        );

        Toastr::success('General settings updated successfully!');
        return redirect()->route('admin.settings.general');
    }

    public function email()
    {
        $setting = GeneralSetting::first();

        return view('backend.settings.email', compact('setting'));
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'mail_mailer' => ['required', 'string', 'max:50'],
            'mail_host' => ['required', 'string', 'max:255'],
            'mail_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'string', 'max:20'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ]);

        $setting = GeneralSetting::first();

        GeneralSetting::updateOrCreate(
            ['id' => 1],
            [
                'mail_mailer' => $request->mail_mailer,
                'mail_host' => $request->mail_host,
                'mail_port' => $request->mail_port,
                'mail_username' => $request->mail_username,
                // Keep existing password when field is left empty.
                'mail_password' => $request->filled('mail_password') ? $request->mail_password : optional($setting)->mail_password,
                'mail_encryption' => $request->mail_encryption,
                'mail_from_address' => $request->mail_from_address,
                'mail_from_name' => $request->mail_from_name,
            ]
        );

        Toastr::success('Email configuration updated successfully!');
        return redirect()->route('admin.settings.email');
    }

    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $toEmail = $request->test_email;
            $siteName = optional(GeneralSetting::first())->site_name ?: config('app.name');

            Mail::raw('This is a test email from your admin email configuration.', function ($message) use ($toEmail, $siteName) {
                $message->to($toEmail)
                    ->subject($siteName . ' - SMTP Test Email');
            });

            Toastr::success('Test email sent successfully to ' . $toEmail . '!');
            return redirect()
                ->route('admin.settings.email')
                ->with('email_test_status', 'success')
                ->with('email_test_message', 'Test email sent successfully to ' . $toEmail . '.');
        } catch (\Throwable $exception) {
            Toastr::error('Test email failed: ' . $exception->getMessage());
            return redirect()
                ->route('admin.settings.email')
                ->withInput()
                ->with('email_test_status', 'error')
                ->with('email_test_message', 'Test email failed: ' . $exception->getMessage());
        }
    }
}
