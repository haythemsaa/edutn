@extends('layouts.app')
@section('page-title', 'Gestion des notes')
@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-journal-text"></i> Gestion des notes</h5>
            <a href="{{ route('grades.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Ajouter une note
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Élève</th>
                            <th>Matière</th>
                            <th>Note</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Fonctionnalité en cours de développement - Liste des notes
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
