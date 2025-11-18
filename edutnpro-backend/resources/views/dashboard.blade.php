@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card text-center p-4 bg-primary text-white">
                <i class="bi bi-people-fill" style="font-size: 2.5rem;"></i>
                <h2 class="mt-3 mb-0">{{ $stats['total_students'] }}</h2>
                <p class="mb-0">Élèves</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-center p-4 bg-success text-white">
                <i class="bi bi-person-badge-fill" style="font-size: 2.5rem;"></i>
                <h2 class="mt-3 mb-0">{{ $stats['total_teachers'] }}</h2>
                <p class="mb-0">Enseignants</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-center p-4 bg-warning text-white">
                <i class="bi bi-building" style="font-size: 2.5rem;"></i>
                <h2 class="mt-3 mb-0">{{ $stats['total_schools'] }}</h2>
                <p class="mb-0">Établissements</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card text-center p-4 bg-info text-white">
                <i class="bi bi-journal-text" style="font-size: 2.5rem;"></i>
                <h2 class="mt-3 mb-0">{{ $stats['total_grades'] }}</h2>
                <p class="mb-0">Notes</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Students -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Élèves récents</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Classe</th>
                                    <th>Date d'inscription</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_students as $student)
                                <tr>
                                    <td>{{ $student->full_name }}</td>
                                    <td>{{ $student->class?->name ?? 'Non assigné' }}</td>
                                    <td>{{ $student->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Aucun élève</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-center">
                    <a href="{{ route('students.index') }}" class="btn btn-sm btn-outline-primary">Voir tous les élèves</a>
                </div>
            </div>
        </div>

        <!-- Recent Grades -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-journal-text"></i> Notes récentes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Élève</th>
                                    <th>Matière</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recent_grades as $grade)
                                <tr>
                                    <td>{{ $grade->student->full_name }}</td>
                                    <td>{{ $grade->subject->name_fr }}</td>
                                    <td><span class="badge bg-primary">{{ $grade->score }}/{{ $grade->max_score }}</span></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Aucune note</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-center">
                    <a href="{{ route('grades.index') }}" class="btn btn-sm btn-outline-primary">Voir toutes les notes</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection