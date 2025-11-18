@extends('layouts.app')

@section('page-title', 'Nouvel élève')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-person-plus"></i> Ajouter un nouvel élève</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('students.store') }}">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Prénom (Arabe)</label>
                        <input type="text" name="first_name_ar" class="form-control" value="{{ old('first_name_ar') }}" dir="rtl">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nom (Arabe)</label>
                        <input type="text" name="last_name_ar" class="form-control" value="{{ old('last_name_ar') }}" dir="rtl">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Date de naissance <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_birth" class="form-control @error('date_of_birth') is-invalid @enderror" value="{{ old('date_of_birth') }}" required>
                        @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Genre <span class="text-danger">*</span></label>
                        <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                            <option value="">Sélectionner...</option>
                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Masculin</option>
                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('gender')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">N° d'inscription <span class="text-danger">*</span></label>
                        <input type="text" name="registration_number" class="form-control @error('registration_number') is-invalid @enderror" value="{{ old('registration_number') }}" required>
                        @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">École <span class="text-danger">*</span></label>
                        <select name="school_id" class="form-select @error('school_id') is-invalid @enderror" required>
                            <option value="">Sélectionner...</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                    {{ $school->name_fr }}
                                </option>
                            @endforeach
                        </select>
                        @error('school_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Classe</label>
                        <select name="class_id" class="form-select">
                            <option value="">Sélectionner...</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Adresse</label>
                    <textarea name="address" class="form-control" rows="2">{{ old('address') }}</textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ville</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lieu de naissance</label>
                        <input type="text" name="place_of_birth" class="form-control" value="{{ old('place_of_birth') }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes médicales</label>
                    <textarea name="medical_notes" class="form-control" rows="2">{{ old('medical_notes') }}</textarea>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Annuler
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection