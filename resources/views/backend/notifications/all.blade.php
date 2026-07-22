@extends('backend.layouts.master')

@section('title')
    All Notifications
@endsection

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>All Notifications</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ route('admin.dashboard') }}">Dashboard</a></div>
                <div class="breadcrumb-item">Notifications</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header">
                    <h4>Active Alerts & Requests</h4>
                    <div class="card-header-action">
                        <a href="javascript:void(0)" onclick="markAllAsRead()" class="btn btn-primary">Mark All as Read</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="list-unstyled list-unstyled-border">
                        @php
                            $lastReadAt = session('notifications_read_at');
                        @endphp
                        
                        @forelse($notifications as $item)
                            <li class="media" style="padding: 20px; border-bottom: 1px solid #eee; {{ $item['is_unread'] ? 'background-color: #fcfcfc; border-left: 4px solid #6777ef;' : '' }}">
                                <div class="mr-3 {{ $item['class'] }} text-white d-flex align-items-center justify-content-center rounded" style="width: 50px; height: 50px; font-size: 20px;">
                                    <i class="{{ $item['icon'] }}"></i>
                                </div>
                                <div class="media-body">
                                    <div class="float-right text-primary">{{ $item['time'] }}</div>
                                    <h6 class="media-title"><a href="{{ $item['url'] }}">{{ $item['title'] }}</a></h6>
                                    <div class="text-small text-muted">{{ $item['desc'] }}</div>
                                </div>
                            </li>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-check-circle text-success mb-3" style="font-size: 40px;"></i>
                                <h5>No active notifications</h5>
                                <p class="text-muted">You're all caught up!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
