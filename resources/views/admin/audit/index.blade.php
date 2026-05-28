@extends('layouts.app')

@section('content')
<div class="container mt-5">

    <h1 class="title">Journal d'actions</h1>

    <div class="notification is-warning">
        TODO : brancher sur un système de logs (ex. spatie/laravel-activitylog).
    </div>

    @if($logs->isEmpty())
        <p class="has-text-grey">Aucune entrée de journal disponible.</p>
    @else
        <table class="table is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Cible</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->created_at }}</td>
                    <td>{{ $log->causer?->nom }}</td>
                    <td>{{ $log->description }}</td>
                    <td>{{ $log->subject_type }} #{{ $log->subject_id }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>
@endsection
