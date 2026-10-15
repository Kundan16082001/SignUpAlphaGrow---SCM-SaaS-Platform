@extends('layouts.master')
@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-1">All Notifications</h5>
            <p class="text-muted small mb-0">{{ $unreadCount }} unread notification{{ $unreadCount !== 1 ? 's' : '' }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.markAllRead') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-check2-all me-1"></i> Mark All Read
                    </button>
                </form>
            @endif
            <form method="POST" action="{{ route('notifications.clearAll') }}" onsubmit="return confirm('Clear all notifications?');">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Clear All
                </button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                @forelse($notifications as $notif)
                    @php
                        $typeColors = [
                            'success' => 'success', 'warning' => 'warning', 'danger' => 'danger',
                            'info' => 'info', 'order' => 'primary', 'stock' => 'warning', 'payment' => 'success'
                        ];
                        $color = $typeColors[$notif->data['type'] ?? 'info'] ?? 'secondary';
                    @endphp
                    <div class="list-group-item p-4 {{ $notif->read_at ? '' : 'bg-primary bg-opacity-10 border-start border-4 border-primary' }}">
                        <div class="d-flex gap-3 align-items-start">
                            <div class="bg-{{ $color }} bg-opacity-10 text-{{ $color }} rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width: 48px; height: 48px; font-size: 1.3rem;">
                                <i class="bi {{ $notif->data['icon'] ?? 'bi-bell-fill' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold mb-0">{{ $notif->data['title'] ?? 'Notification' }}</h6>
                                    <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="text-muted mb-2 small">{{ $notif->data['message'] ?? '' }}</p>
                                <div class="d-flex gap-2">
                                    @if(!empty($notif->data['link']))
                                        <a href="{{ route('notifications.read', $notif->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-arrow-right me-1"></i> View Details
                                        </a>
                                    @endif
                                    @if($notif->read_at === null)
                                        <form method="POST" action="{{ route('notifications.read', $notif->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-check2 me-1"></i> Mark Read
                                            </button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('notifications.delete', $notif->id) }}" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?');">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                        <h5 class="mt-3 text-muted">No notifications</h5>
                        <p class="text-muted small">You're all caught up!</p>
                    </div>
                @endforelse
            </div>
        </div>
        @if($notifications->hasPages())
            <div class="card-footer bg-white border-top-0 pt-3">{{ $notifications->links() }}</div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    // Auto-refresh notification count every 30 seconds
    setInterval(() => {
        fetch('{{ route("notifications.count") }}')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('notificationBadge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            });
    }, 30000);
</script>
@endpush