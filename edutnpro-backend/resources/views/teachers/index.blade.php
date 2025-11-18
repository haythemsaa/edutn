@extends('layouts.app')
@section('page-title', 'Liste des enseignants')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Liste des enseignants</h5>
            <a href="{{ route('teachers.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouvel enseignant
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>N° Employé</th>
                            <th>Nom complet</th>
                            <th>Spécialisation</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Fonctionnalité en cours de développement - Liste des enseignants
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
