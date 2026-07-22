@extends('backend.layouts.master')
@section('title', 'Profile')
@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Profile</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Profile</div>
            </div>
        </div>
        <div class="section-body">
            <div class="row mt-sm-4">
                <div class="col-12 col-md-12 col-lg-6">
                    <div class="card">
                        <form action="{{ route('admin.profile.update') }}" method="post" class="needs-validation"
                            novalidate="" enctype="multipart/form-data">
                            @csrf
                            <div class="card-header">
                                <h4>Update Profile</h4>
                            </div>
                            <div class="col-12 col-md-12 col-lg-5">

                                <img alt="image" width="100px"
                                    src="{{ asset(Auth::user()->image ?? '/uploads/default.jpg') }}">
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-6 col-12">
                                        <label>Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ Auth::user()->name }}">
                                        <div class="invalid-feedback">
                                            Please fill in the name
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6 col-12">
                                        <label>Email</label>
                                        <input type="text" class="form-control" name="email"
                                            value="{{ Auth::user()->email }}">
                                        <div class="invalid-feedback">
                                            Please fill in the email
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12 col-12">
                                        <label>Phone</label>
                                        <input type="text" class="form-control" name="phone"
                                            value="{{ Auth::user()->phone }}">
                                    </div>
                                    @role('Outlet User')
                                    <div class="form-group col-md-12 col-12">
                                        <label>Outlet Name</label>
                                        <input type="text" class="form-control" name="outlet_name"
                                            value="{{ Auth::user()->outlet_name }}">
                                    </div>
                                    <div class="form-group col-12">
                                        <label>Address</label>
                                        <textarea class="form-control" name="address" style="min-height: 80px;">{{ Auth::user()->address }}</textarea>
                                    </div>
                                    @endrole

                                    @role('User')
                                    <div class="form-group col-md-12 col-12">
                                        <label>Shop Name</label>
                                        <input type="text" class="form-control" name="outlet_name"
                                            value="{{ Auth::user()->outlet_name }}">
                                    </div>
                                    <div class="form-group col-12">
                                        <label>Address</label>
                                        <textarea class="form-control" name="address" style="min-height: 80px;">{{ Auth::user()->address }}</textarea>
                                    </div>
                                    @endrole
                                    <div class="form-group  col-12">
                                        <label>Image</label>
                                        <input type="file" name="image" id="" class="form-control">
                                    </div>

                                </div>

                            </div>
                            <div class="card-footer text-right">
                                <button class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-12 col-md-12 col-lg-6">
                    <div class="card">

                        <form action="{{ route('admin.password.update') }}" method="post" class="needs-validation"
                            novalidate="">
                            @csrf
                            <div class="card-header">
                                <h4>Update Password</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label>Current Password</label>
                                        <input type="password" class="form-control" name="current_password" value="">
                                        <div class="invalid-feedback">
                                            Please fill in the Currentt password
                                        </div>
                                    </div>
                                    <div class="form-group col-12">
                                        <label>New Password</label>
                                        <input type="password" class="form-control" name="password" value="">
                                        <div class="invalid-feedback">
                                            Please fill in the name
                                        </div>
                                    </div>
                                    <div class="form-group col-12">
                                        <label>Confirme Password</label>
                                        <input type="password" class="form-control" name="password_confirmation"
                                            value="">
                                        <div class="invalid-feedback">
                                            Please fill in the name
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="card-footer text-right">
                                <button class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
