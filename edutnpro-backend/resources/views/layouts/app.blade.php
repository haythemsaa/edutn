<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'EDUTN PRO') - Gestion Scolaire</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: linear-gradient(180deg, #1e3a8a 0%, #3b82f6 100%); }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 0.75rem 1rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background-color: rgba(255,255,255,0.1); }
        .navbar { background: white !important; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .stat-card { transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>

    @stack('styles')
</head>
<body>
    @auth
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar px-0">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white"><i class="bi bi-mortarboard-fill"></i> EDUTN PRO</h4>
                        <p class="text-white-50 small">{{ auth()->user()->name }}</p>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('students*') ? 'active' : '' }}" href="{{ route('students.index') }}">
                                <i class="bi bi-people"></i> Élèves
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('teachers*') ? 'active' : '' }}" href="{{ route('teachers.index') }}">
                                <i class="bi bi-person-badge"></i> Enseignants
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('grades*') ? 'active' : '' }}" href="{{ route('grades.index') }}">
                                <i class="bi bi-journal-text"></i> Notes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('parents*') ? 'active' : '' }}" href="{{ route('parents.index') }}">
                                <i class="bi bi-person-hearts"></i> Parents
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('attendances*') ? 'active' : '' }}" href="{{ route('attendances.index') }}">
                                <i class="bi bi-calendar-check"></i> Présences
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('invoices*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                                <i class="bi bi-receipt"></i> Factures
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('payments*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                                <i class="bi bi-cash-coin"></i> Paiements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('announcements*') ? 'active' : '' }}" href="{{ route('announcements.index') }}">
                                <i class="bi bi-megaphone"></i> Annonces
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('messages*') ? 'active' : '' }}" href="{{ route('messages.index') }}">
                                <i class="bi bi-envelope"></i> Messages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('library*') ? 'active' : '' }}" href="{{ route('library.index') }}">
                                <i class="bi bi-book"></i> Bibliothèque
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('buses*') || request()->is('transport*') ? 'active' : '' }}" href="{{ route('buses.index') }}">
                                <i class="bi bi-bus-front"></i> Transport
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('canteen*') ? 'active' : '' }}" href="{{ route('canteen.index') }}">
                                <i class="bi bi-cup-straw"></i> Cantine
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('leaderboard') || request()->is('badges*') ? 'active' : '' }}" href="{{ route('leaderboard') }}">
                                <i class="bi bi-trophy"></i> Classement
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('analytics*') ? 'active' : '' }}" href="{{ route('analytics.index') }}">
                                <i class="bi bi-graph-up"></i> Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('subjects*') ? 'active' : '' }}" href="{{ route('subjects.index') }}">
                                <i class="bi bi-journals"></i> Matières
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('classrooms*') ? 'active' : '' }}" href="{{ route('classrooms.index') }}">
                                <i class="bi bi-door-open"></i> Salles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('class-sections*') ? 'active' : '' }}" href="{{ route('class-sections.index') }}">
                                <i class="bi bi-diagram-3"></i> Classes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('class-diary*') ? 'active' : '' }}" href="{{ route('class-diary.index') }}">
                                <i class="bi bi-journal-bookmark"></i> Cahier Texte
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('timetables*') ? 'active' : '' }}" href="{{ route('timetables.index') }}">
                                <i class="bi bi-calendar3"></i> Emploi du temps
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('report-cards*') ? 'active' : '' }}" href="{{ route('report-cards.index') }}">
                                <i class="bi bi-file-earmark-text"></i> Bulletins
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('assignments*') ? 'active' : '' }}" href="{{ route('assignments.index') }}">
                                <i class="bi bi-list-check"></i> Devoirs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('discipline*') ? 'active' : '' }}" href="{{ route('discipline.index') }}">
                                <i class="bi bi-shield-exclamation"></i> Discipline
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('events*') ? 'active' : '' }}" href="{{ route('events.index') }}">
                                <i class="bi bi-calendar-event"></i> Événements
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('online-classes*') ? 'active' : '' }}" href="{{ route('online-classes.index') }}">
                                <i class="bi bi-camera-video"></i> Classes en ligne
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('appointments*') ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                                <i class="bi bi-calendar2-check"></i> Rendez-vous
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('conversations*') ? 'active' : '' }}" href="{{ route('conversations.index') }}">
                                <i class="bi bi-chat-dots"></i> Messagerie
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('certificates*') ? 'active' : '' }}" href="{{ route('certificates.index') }}">
                                <i class="bi bi-award"></i> Certificats
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('documents*') ? 'active' : '' }}" href="{{ route('documents.index') }}">
                                <i class="bi bi-file-earmark-pdf"></i> Documents
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('hr/*') ? 'active' : '' }}" href="{{ route('hr.contracts.index') }}">
                                <i class="bi bi-person-workspace"></i> RH
                            </a>
                        </li>
                        @can('view-schools')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('schools*') ? 'active' : '' }}" href="{{ route('schools.index') }}">
                                <i class="bi bi-building"></i> Établissements
                            </a>
                        </li>
                        @endcan
                    </ul>
                    <hr class="bg-white">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-start w-100">
                                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-md-4">
                <!-- Top navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white mt-3 mb-4 rounded">
                    <div class="container-fluid">
                        <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary me-2">{{ auth()->user()->roles->first()?->name ?? 'User' }}</span>
                            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=0D8ABC&color=fff"
                                 class="rounded-circle" width="32" height="32">
                        </div>
                    </div>
                </nav>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @else
        @yield('content')
    @endauth

    <!-- Bootstrap 5.3 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>