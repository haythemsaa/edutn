@extends('layouts.app')
@section('page-title', 'Modifier enseignant')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-pencil"></i> Modifier l'enseignant</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Fonctionnalité en cours de développement</p>
            <a href="{{ route('teachers.index') }}" class="btn btn-secondary">Retour</a>
        </div>
    </div>
</div>
@endsection
