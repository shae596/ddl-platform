@props(['notif', 'href' => null])

@if($href)
    <a href="{{ $href }}" class="notif-item notif-item--link {{ $notif->lue ? '' : 'notif-item--unread' }}">
        <strong>{{ $notif->titre }}</strong>
        <p>{{ $notif->message }}</p>
        <small>{{ $notif->created_at?->diffForHumans() }}</small>
    </a>
@else
    <div class="notif-item {{ $notif->lue ? '' : 'notif-item--unread' }}">
        <strong>{{ $notif->titre }}</strong>
        <p>{{ $notif->message }}</p>
        <small>{{ $notif->created_at?->diffForHumans() }}</small>
    </div>
@endif
