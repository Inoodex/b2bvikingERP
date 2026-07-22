@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Email Configuration</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></div>
                <div class="breadcrumb-item active">Email</div>
            </div>
        </div>

        <div class="section-body">
            {{-- @if (session('email_test_message'))
                <div class="alert {{ session('email_test_status') === 'success' ? 'alert-success' : 'alert-danger' }} alert-dismissible fade show" role="alert">
                    {{ session('email_test_message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif --}}

            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h4>SMTP Settings</h4>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.settings.email.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Mail Driver</label>
                                            <select name="mail_mailer" class="form-control">
                                                @php $currentMailer = old('mail_mailer', $setting->mail_mailer ?? config('mail.default', 'smtp')); @endphp
                                                @foreach (['smtp', 'sendmail', 'log', 'array'] as $mailer)
                                                    <option value="{{ $mailer }}" {{ $currentMailer === $mailer ? 'selected' : '' }}>{{ strtoupper($mailer) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Mail Host</label>
                                            <input type="text" name="mail_host" class="form-control" value="{{ old('mail_host', $setting->mail_host ?? config('mail.mailers.smtp.host')) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Mail Port</label>
                                            <input type="number" name="mail_port" class="form-control" value="{{ old('mail_port', $setting->mail_port ?? config('mail.mailers.smtp.port')) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Mail Encryption</label>
                                            <select name="mail_encryption" class="form-control">
                                                @php $currentEncryption = old('mail_encryption', $setting->mail_encryption ?? config('mail.mailers.smtp.encryption')); @endphp
                                                <option value="">None</option>
                                                <option value="tls" {{ $currentEncryption === 'tls' ? 'selected' : '' }}>TLS</option>
                                                <option value="ssl" {{ $currentEncryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Mail Username</label>
                                            <input type="text" name="mail_username" class="form-control" value="{{ old('mail_username', $setting->mail_username ?? config('mail.mailers.smtp.username')) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Mail Password</label>
                                            <input type="password" name="mail_password" class="form-control" placeholder="Leave empty to keep existing password">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>From Email</label>
                                            <input type="email" name="mail_from_address" class="form-control" value="{{ old('mail_from_address', $setting->mail_from_address ?? config('mail.from.address')) }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>From Name</label>
                                    <input type="text" name="mail_from_name" class="form-control" value="{{ old('mail_from_name', $setting->mail_from_name ?? config('mail.from.name')) }}">
                                </div>

                                <div class="text-right">
                                    <a href="{{ route('admin.settings.index') }}" class="btn btn-light border mr-2">Back</a>
                                    <button type="submit" class="btn btn-primary">Save Email Configuration</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h4>Send Test Email</h4>
                        </div>
                        <div class="card-body">
                            <p class="text-muted">
                                Use this to verify your SMTP setup is working.
                            </p>
                            <form action="{{ route('admin.settings.email.test') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Recipient Email</label>
                                    <input type="email" name="test_email" class="form-control" value="{{ old('test_email', $setting->contact_email ?? auth()->user()->email) }}" required>
                                </div>
                                <button type="submit" class="btn btn-info btn-block">Send Test Email</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
