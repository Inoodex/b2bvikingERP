@extends('backend.layouts.master')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>Pricing Rules</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Pricing Rules</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>All Pricing Rules</h4>
                            <div class="card-header-action">
                                <a href="{{ route('admin.pricing-rules.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create New
                                </a>
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="text-center">Sale Multiplier</th>
                                        <th class="text-center">Outlet Multiplier</th>
                                        <th class="text-center">Default</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center" style="width: 150px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pricingRules as $rule)
                                        <tr>
                                            <td>{{ $rule->name }}</td>
                                            <td class="text-center">{{ $rule->sale_multiplier }}</td>
                                            <td class="text-center">{{ $rule->outlet_multiplier }}</td>
                                            <td class="text-center">
                                                @if($rule->is_default)
                                                    <span class="badge badge-success">Default</span>
                                                @else
                                                    <span class="badge badge-light">No</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($rule->status)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.pricing-rules.edit', $rule->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="{{ route('admin.pricing-rules.destroy', $rule->id) }}" class="btn btn-sm btn-danger delete-item">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No pricing rules found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

