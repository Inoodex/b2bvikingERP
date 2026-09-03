@php
    $modelType = get_class($model);
    $approvals = $model->approvals()->with(['step.approverRole', 'step.approverUser', 'user'])->get();
    
    // Determine transaction amount for workflow threshold evaluation
    $amount = (float)($model->grand_total ?? $model->total_amount ?? $model->amount ?? $model->total_claim_amount ?? 0);
    if ($amount == 0 && method_exists($model, 'items')) {
        $amount = (float)$model->items->sum(function($i) { 
            return ($i->qty ?? $i->quantity ?? 0) * ($i->unit_cost ?? $i->unit_price ?? 0); 
        });
    }
    
    $workflow = \App\Models\ApprovalWorkflow::active()
        ->where('model_type', $modelType)
        ->where(function ($q) use ($amount) {
            $q->where('min_amount', '<=', $amount)->orWhereNull('min_amount');
        })
        ->where(function ($q) use ($amount) {
            $q->where('max_amount', '>=', $amount)->orWhereNull('max_amount');
        })
        ->with(['steps' => function($q) { 
            $q->orderBy('step_order', 'asc')->with('approverRole', 'approverUser'); 
        }])
        ->latest('id')
        ->first();

    $allSteps = $workflow ? $workflow->steps : collect();
    $activeApproval = $approvals->where('status', 'pending')->first();
    $approvalService = app(\App\Services\ApprovalService::class);
    $canApprove = $activeApproval ? $approvalService->canUserApproveCurrentStep($model, \Illuminate\Support\Facades\Auth::user()) : false;
    $isFullyApproved = ($model->approval_status === 'approved');
    $isRejected = ($model->approval_status === 'rejected');
    $totalStepsCount = $allSteps->count() ?: $approvals->count();
@endphp

<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; border: 1px solid #e2e8f0;">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 font-weight-bold text-dark" style="font-size: 15px;">
            <i class="fas fa-sitemap mr-2 text-primary"></i> Approval Workflow Chain
        </h5>
        <div>
            @if($isFullyApproved)
                <span class="badge badge-success px-3 py-1 font-weight-bold" style="border-radius: 20px;">
                    <i class="fas fa-check-circle mr-1"></i> Fully Approved
                </span>
            @elseif($isRejected)
                <span class="badge badge-danger px-3 py-1 font-weight-bold" style="border-radius: 20px;">
                    <i class="fas fa-times-circle mr-1"></i> Workflow Rejected
                </span>
            @else
                <span class="badge badge-warning text-dark px-3 py-1 font-weight-bold" style="border-radius: 20px; background: #fef08a;">
                    <i class="fas fa-hourglass-half mr-1"></i> In Progress ({{ $activeApproval?->step?->step_name ?: 'Review' }})
                </span>
            @endif
        </div>
    </div>

    <div class="card-body p-3">
        @if($totalStepsCount > 0)
            <div class="approval-timeline position-relative pl-3" style="border-left: 2px solid #e2e8f0; margin-left: 14px;">
                @php
                    $stepsToIterate = $allSteps->isNotEmpty() ? $allSteps : $approvals->pluck('step')->filter();
                    $processedApprovalIds = [];
                @endphp

                @foreach($stepsToIterate as $index => $step)
                    @php
                        // Check if an approval record exists for this step
                        $stepApproval = $approvals->where('approval_step_id', $step->id)->first();
                        $isApproved = $stepApproval && $stepApproval->status === 'approved';
                        $isStepRejected = $stepApproval && $stepApproval->status === 'rejected';
                        $isPending = $stepApproval && $stepApproval->status === 'pending';
                        $isUpcoming = !$stepApproval || (!$isApproved && !$isStepRejected && !$isPending);
                        $roleName = $step->approverRole?->name ?? 'Designated Approver';
                    @endphp

                    <div class="timeline-step position-relative mb-4 {{ $loop->last ? 'mb-1' : '' }}">
                        {{-- Dot Indicator --}}
                        <div class="timeline-dot position-absolute d-flex align-items-center justify-content-center shadow-sm"
                             style="left: -27px; top: 0; width: 26px; height: 26px; border-radius: 50%; font-size: 11px; z-index: 2;
                             @if($isApproved) background: #10b981; color: #fff;
                             @elseif($isStepRejected) background: #ef4444; color: #fff;
                             @elseif($isPending) background: #f59e0b; color: #fff; box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.25) !important;
                             @else background: #cbd5e1; color: #64748b; @endif">
                            @if($isApproved)
                                <i class="fas fa-check"></i>
                            @elseif($isStepRejected)
                                <i class="fas fa-times"></i>
                            @elseif($isPending)
                                <i class="fas fa-hourglass-half fa-spin"></i>
                            @else
                                <i class="fas fa-lock"></i>
                            @endif
                        </div>

                        {{-- Step Content Box --}}
                        <div class="step-card p-3 rounded" style="background: @if($isPending) #fffbeb; border: 1px solid #fde68a; @elseif($isApproved) #f0fdf4; border: 1px solid #bbf7d0; @elseif($isStepRejected) #fef2f2; border: 1px solid #fecaca; @else #f8fafc; border: 1px solid #e2e8f0; @endif">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="font-weight-bold text-dark" style="font-size: 13px;">
                                        Step {{ $step->step_order ?? ($index + 1) }}: {{ $step->step_name ?: 'Review Step' }}
                                    </div>
                                    <div class="small text-muted">
                                        <i class="fas fa-user-shield mr-1"></i> Authority: <strong>{{ $roleName }}</strong>
                                    </div>
                                </div>
                                <div>
                                    @if($isApproved)
                                        <span class="badge badge-success small font-weight-bold">Passed</span>
                                    @elseif($isStepRejected)
                                        <span class="badge badge-danger small font-weight-bold">Rejected</span>
                                    @elseif($isPending)
                                        <span class="badge badge-warning text-dark small font-weight-bold" style="background: #fbbf24;">Waiting Authorization</span>
                                    @else
                                        <span class="badge badge-secondary small">Locked</span>
                                    @endif
                                </div>
                            </div>

                            @if($isApproved && $stepApproval)
                                <div class="mt-2 pt-2 border-top small text-success d-flex flex-wrap justify-content-between align-items-center" style="border-color: #bbf7d0 !important;">
                                    <span>
                                        <i class="fas fa-user-check mr-1"></i> Signed by: <strong>{{ $stepApproval->user?->name ?? 'Authorized Officer' }}</strong>
                                    </span>
                                    <span class="text-muted">
                                        <i class="far fa-clock mr-1"></i> {{ $stepApproval->updated_at->format('d M, Y h:i A') }}
                                    </span>
                                </div>
                                @if($stepApproval->comments)
                                    <div class="mt-1 small text-dark font-italic bg-white p-2 rounded border" style="border-color: #dcfce7 !important;">
                                        "{{ $stepApproval->comments }}"
                                    </div>
                                @endif
                            @elseif($isStepRejected && $stepApproval)
                                <div class="mt-2 pt-2 border-top small text-danger" style="border-color: #fecaca !important;">
                                    <div><i class="fas fa-user-times mr-1"></i> Rejected by: <strong>{{ $stepApproval->user?->name ?? 'Officer' }}</strong> on {{ $stepApproval->updated_at->format('d M, Y h:i A') }}</div>
                                    @if($stepApproval->comments)
                                        <div class="mt-1 p-2 bg-white rounded border border-danger text-danger font-weight-bold">
                                            Reason: {{ $stepApproval->comments }}
                                        </div>
                                    @endif
                                </div>
                            @elseif($isPending)
                                <div class="mt-2 small text-warning font-weight-bold d-flex align-items-center">
                                    <i class="fas fa-info-circle mr-1"></i> Currently under review by assigned {{ $roleName }}.
                                </div>

                                {{-- If current user has authority to approve this step --}}
                                @if($canApprove && !empty($approveRoute))
                                    <div class="mt-3 pt-2 border-top d-flex gap-2" style="border-color: #fde68a !important;">
                                        <form action="{{ route($approveRoute, $model->id) }}" method="POST" class="mr-2">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success font-weight-bold px-3 shadow-sm">
                                                <i class="fas fa-check mr-1"></i> Approve Step {{ $step->step_order ?? ($index + 1) }}
                                            </button>
                                        </form>

                                        @if(!empty($rejectModalId))
                                            <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold px-3" data-toggle="modal" data-target="#{{ $rejectModalId }}">
                                                <i class="fas fa-times mr-1"></i> Reject
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            @else
                                <div class="mt-1 small text-muted font-italic">
                                    <i class="fas fa-lock mr-1"></i> Will activate automatically once Step {{ ($step->step_order ?? ($index + 1)) - 1 }} is signed.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-3 text-center bg-light rounded text-muted">
                <i class="fas fa-shield-alt fa-2x text-success mb-2 d-block"></i>
                <strong class="text-dark">Auto-Approved (Direct Execution)</strong>
                <div class="small mt-1">This transaction did not exceed the governance threshold or no multi-step approval policy is assigned.</div>
            </div>
        @endif
    </div>
</div>
