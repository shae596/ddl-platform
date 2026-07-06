@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<section class="panel">
    <p class="text-muted">Activez ou désactivez les notifications in-app envoyées lors des changements de statut et des événements du workflow.</p>

    <form method="POST" action="{{ route('admin.parametres.notifications') }}">
        @csrf
        @method('PUT')

        <div class="notif-settings">
            @foreach($notifications as $notif)
                <label class="checkbox-row notif-settings__row">
                    <input type="checkbox" name="notifications[]" value="{{ $notif->cle }}" @checked($notif->actif)>
                    <span>{{ $notif->label }}</span>
                </label>
            @endforeach
        </div>

        <div class="form-actions" style="justify-content:flex-start; margin-top:1.5rem;">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
</section>
@endsection
