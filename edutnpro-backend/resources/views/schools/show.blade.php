@extends('layouts.app')
@section('page-title', 'Détails établissement')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-building"></i> Détails de l'établissement</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Fonctionnalité en cours de développement</p>
            <a href="{{ route('schools.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
</div>
@endsection
