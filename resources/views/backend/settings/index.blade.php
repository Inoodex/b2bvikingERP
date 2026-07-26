@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Settings</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item active">Settings</div>
            </div>
        </div>

        <style>
            @media (max-width: 575.98px) {
                .settings-icon { font-size: 32px !important; }
                .row.no-gutters > [class*="col-"]:first-child { min-height: 80px; }
            }
        </style>

        <div class="section-body">
            <div class="card shadow-sm mb-4">
                <div class="card-body py-4">
                    <h4 class="mb-2">Overview</h4>
                    <p class="text-muted mb-0">Organize and adjust all settings from one place.</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="row no-gutters h-100">
                            <div class="col-12 col-sm-4 d-flex align-items-center justify-content-center text-white p-3 p-sm-0" style="background: #5b6ee1;">
                                <i class="fas fa-cog settings-icon" style="font-size: 54px;"></i>
                            </div>
                            <div class="col-12 col-sm-8">
                                <div class="card-body d-flex flex-column h-100">
                                    <h4 class="mb-2">General</h4>
                                    <p class="text-muted flex-grow-1 mb-3">Site title, contact email and address information.</p>
                                    <a href="{{ route('admin.settings.general') }}" class="font-weight-bold">Change Setting <i class="fas fa-angle-right ml-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="col-12 col-lg-6 mb-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="row no-gutters h-100">
                            <div class="col-12 col-sm-4 d-flex align-items-center justify-content-center text-white p-3 p-sm-0" style="background: #5b6ee1;">
                                <i class="fas fa-envelope settings-icon" style="font-size: 54px;"></i>
                            </div>
                            <div class="col-12 col-sm-8">
                                <div class="card-body d-flex flex-column h-100">
                                    <h4 class="mb-2">Email Configuration</h4>
                                    <p class="text-muted flex-grow-1 mb-3">Configure SMTP credentials and test outgoing email.</p>
                                    <a href="{{ route('admin.settings.email') }}" class="font-weight-bold">Change Setting <i class="fas fa-angle-right ml-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

