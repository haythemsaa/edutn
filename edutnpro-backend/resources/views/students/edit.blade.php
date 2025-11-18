@extends('layouts.app')

@section('page-title', 'Modifier élève')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-pencil"></i> Modifier l'élève: {{ $student->full_name }}</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('students.update', $student) }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $student->first_name) }}" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $student->last_name) }}" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom (Arabe)</label>
                        <input type="text" name="first_name_ar" class="form-control" value="{{ old('first_name_ar', $student->first_name_ar) }}" dir="rtl">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom (Arabe)</label>
                        <input type="text" name="last_name_ar" class="form-control" value="{{ old('last_name_ar', $student->last_name_ar) }}" dir="rtl">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date de naissance <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth', $student->date_of_birth->format('Y-m-d')) }}" required>
                        @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Genre <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="male" {{ old('gender', $student->gender) == 'male' ? 'selected' : '' }}>Masculin</option>
                            <option value="female" {{ old('gender', $student->gender) == 'female' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Statut <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $student->status) == 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="inactive" {{ old('status', $student->status) == 'inactive' ? 'selected' : '' }}>Inactif</option>
                            <option value="transferred" {{ old('status', $student->status) == 'transferred' ? 'selected' : '' }}>Transféré</option>
                            <option value="graduated" {{ old('status', $student->status) == 'graduated' ? 'selected' : '' }}>Diplômé</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">École <span class="text-danger">*</span></label>
                        <select name="school_id" class="form-select @error('school_id') is-invalid @enderror" required>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ old('school_id', $student->school_id) == $school->id ? 'selected' : '' }}>
                                    {{ $school->name_fr }}
                                </option>
                            @endforeach
                        </select>
                        @error('school_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Classe</label>
                        <select name="class_id" class="form-select">
                            <option value="">Aucune classe</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection