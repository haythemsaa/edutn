@extends('layouts.app')

@section('page-title', 'Détails élève')

@section('content')
<div class="container-fluid">
    <div class="row g-4">
        <!-- Student Profile Card -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="{{ $student->photo ?? 'https://ui-avatars.com/api/?name='.urlencode($student->full_name).'&size=150' }}"
                         class="rounded-circle mb-3" width="150" height="150">
                    <h4>{{ $student->full_name }}</h4>
                    @if($student->full_name_ar)
                        <h5 class="text-muted" dir="rtl">{{ $student->full_name_ar }}</h5>
                    @endif
                    <p class="text-muted">{{ $student->registration_number }}</p>

                    @if($student->status === 'active')
                        <span class="badge bg-success">Actif</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($student->status) }}</span>
                    @endif

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="{{ route('students.edit', $student) }}" class="btn btn-primary">
                            <i class="bi bi-pencil"></i> Modifier
                        </a>
                        <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Date de naissance:</div>
                        <div class="col-md-8"><strong>{{ $student->date_of_birth->format('d/m/Y') }} ({{ $student->date_of_birth->age }} ans)</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Genre:</div>
                        <div class="col-md-8"><strong>{{ $student->gender == 'male' ? 'Masculin' : 'Féminin' }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Lieu de naissance:</div>
                        <div class="col-md-8"><strong>{{ $student->place_of_birth ?? 'Non renseigné' }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Nationalité:</div>
                        <div class="col-md-8"><strong>{{ $student->nationality }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Email:</div>
                        <div class="col-md-8"><strong>{{ $student->email ?? 'Non renseigné' }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Téléphone:</div>
                        <div class="col-md-8"><strong>{{ $student->phone ?? 'Non renseigné' }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Adresse:</div>
                        <div class="col-md-8"><strong>{{ $student->address ?? 'Non renseignée' }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Ville:</div>
                        <div class="col-md-8"><strong>{{ $student->city ?? 'Non renseignée' }}</strong></div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Informations scolaires</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">École:</div>
                        <div class="col-md-8"><strong>{{ $student->school->name_fr }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Classe:</div>
                        <div class="col-md-8"><strong>{{ $student->class?->name ?? 'Non assigné' }}</strong></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Date d'inscription:</div>
                        <div class="col-md-8"><strong>{{ $student->enrollment_date->format('d/m/Y') }}</strong></div>
                    </div>
                    @if($student->medical_notes)
                    <div class="row mb-2">
                        <div class="col-md-4 text-muted">Notes médicales:</div>
                        <div class="col-md-8"><span class="badge bg-warning">{{ $student->medical_notes }}</span></div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-journal-text"></i> Dernières notes</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Matière</th>
                                    <th>Note</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($student->grades->take(10) as $grade)
                                <tr>
                                    <td>{{ $grade->subject->name_fr }}</td>
                                    <td><span class="badge bg-primary">{{ $grade->score }}/{{ $grade->max_score }}</span></td>
                                    <td>{{ ucfirst($grade->grade_type) }}</td>
                                    <td>{{ $grade->date->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Aucune note</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection