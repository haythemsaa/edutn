@extends('layouts.app')
@section('page-title', 'Liste des établissements')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-building"></i> Liste des établissements</h5>
            <a href="{{ route('schools.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Nouvel établissement
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Ville</th>
                            <th>Capacité</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Fonctionnalité en cours de développement - Liste des établissements
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
