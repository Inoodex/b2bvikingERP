@extends('backend.layouts.master')

@section('title', 'Create Approval Workflow')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Approval Workflow</h1>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Create Approval Workflow</h4>
                        <div class="card-header-action">
                            <a href="{{ route('admin.master.approval-workflows.index') }}" class="btn btn-primary">Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.master.approval-workflows.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Workflow Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Standard Requisition Approval Chain" required value="{{ old('name') }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Target Module <span class="text-danger">*</span></label>
                                    <select name="model_type" class="form-control" required>
                                        @foreach($models as $class => $label)
                                            <option value="{{ $class }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Min Amount Threshold (Optional)</label>
                                    <input type="number" step="0.01" name="min_amount" class="form-control" placeholder="0.00" value="{{ old('min_amount') }}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Max Amount Threshold (Optional)</label>
                                    <input type="number" step="0.01" name="max_amount" class="form-control" placeholder="Leave empty for unlimited" value="{{ old('max_amount') }}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="inputState">Status</label>
                                    <select id="inputState" name="status" class="form-control">
                                        <option value="1" selected>Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <hr>
                            <h5 class="mb-3">Approval Chain Steps</h5>

                            <div id="steps-container">
                                <div class="step-row row align-items-center mb-3 bg-light p-3 rounded">
                                    <div class="col-md-4">
                                        <label>Step 1 Name <span class="text-danger">*</span></label>
                                        <input type="text" name="steps[0][step_name]" class="form-control" placeholder="e.g. Department Head Approval" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Approver Role</label>
                                        <select name="steps[0][approver_role_id]" class="form-control approver-role-select">
                                            <option value="">Select Role (All Roles)</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Specific User (Optional)</label>
                                        <select name="steps[0][approver_user_id]" class="form-control approver-user-select">
                                            <option value="">-- Any user in role --</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}"
                                                        data-role-id="{{ $user->role_id }}"
                                                        data-roles="{{ $user->roles->pluck('id')->implode(',') }}">
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm mb-4" id="add-step-btn"><i class="fas fa-plus mr-1"></i> Add Another Level/Step</button>

                            <div class="text-right">
                                <button type="submit" class="btn btn-primary px-4">Create</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function filterUsersByRole(stepRow) {
        const roleSelect = stepRow.querySelector('.approver-role-select');
        const userSelect = stepRow.querySelector('.approver-user-select');
        if (!roleSelect || !userSelect) return;

        const selectedRoleId = roleSelect.value;
        const currentUserId = userSelect.value;
        let isCurrentAllowed = false;

        const options = userSelect.querySelectorAll('option');
        options.forEach(opt => {
            if (!opt.value) {
                opt.style.display = '';
                opt.disabled = false;
                return;
            }

            const primaryRoleId = opt.getAttribute('data-role-id') || '';
            const spatieRoles = (opt.getAttribute('data-roles') || '').split(',').filter(Boolean);
            const userRoleIds = [primaryRoleId, ...spatieRoles];

            if (!selectedRoleId || userRoleIds.includes(selectedRoleId.toString())) {
                opt.style.display = '';
                opt.disabled = false;
                if (opt.value === currentUserId) {
                    isCurrentAllowed = true;
                }
            } else {
                opt.style.display = 'none';
                opt.disabled = true;
            }
        });

        if (currentUserId && !isCurrentAllowed) {
            userSelect.value = '';
        }
    }

    let stepCount = 1;
    document.getElementById('add-step-btn').addEventListener('click', function() {
        stepCount++;
        const container = document.getElementById('steps-container');
        const row = document.createElement('div');
        row.className = 'step-row row align-items-center mb-3 bg-light p-3 rounded';
        row.innerHTML = `
            <div class="col-md-4">
                <label>Step ${stepCount} Name <span class="text-danger">*</span></label>
                <input type="text" name="steps[${stepCount - 1}][step_name]" class="form-control" placeholder="e.g. Director / MD Approval" required>
            </div>
            <div class="col-md-4">
                <label>Approver Role</label>
                <select name="steps[${stepCount - 1}][approver_role_id]" class="form-control approver-role-select">
                    <option value="">Select Role (All Roles)</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Specific User (Optional)</label>
                <select name="steps[${stepCount - 1}][approver_user_id]" class="form-control approver-user-select">
                    <option value="">-- Any user in role --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                                data-role-id="{{ $user->role_id }}"
                                data-roles="{{ $user->roles->pluck('id')->implode(',') }}">
                            {{ $user->name }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 text-right pt-4">
                <button type="button" class="btn btn-danger btn-sm remove-step"><i class="fas fa-times"></i></button>
            </div>
        `;
        container.appendChild(row);
        filterUsersByRole(row);

        row.querySelector('.remove-step').addEventListener('click', function() {
            row.remove();
        });
    });

    $(document).on('change', '.approver-role-select', function() {
        const stepRow = this.closest('.step-row');
        filterUsersByRole(stepRow);
    });

    $(document).ready(function() {
        document.querySelectorAll('.step-row').forEach(row => filterUsersByRole(row));
    });
</script>
@endpush
