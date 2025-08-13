@php 
    $authUser = auth()->user();
@endphp

<section class="menu-section-area @auth isLoginMenu @endauth">
    <!-- Navigation -->
    <nav class="navbar sticky-header navbar-expand-lg" id="mainNav">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ getImageFile(get_option('app_logo')) }}" alt="Logo">
            </a>

            <div class="header-nav-right-side d-flex ms-auto">
                <ul class="navbar-nav">

                    {{-- Menú para visitantes --}}
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Sign In') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('sign-up') }}">{{ __('Sign Up') }}</a>
                        </li>
                    @endguest

                    {{-- Menú para usuarios autenticados --}}
                    @auth
                        <li class="nav-item dropdown profile-dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <img src="{{ $authUser->image ? asset($authUser->image) : asset('uploads/default/user.png') }}" alt="user" class="rounded-circle" width="30">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="dropdown-header text-center">
                                    <strong>{{ $authUser->name }}</strong><br>
                                    <small>{{ $authUser->email }}</small>
                                </li>

                                {{-- Acceso según el rol --}}
                                @if($authUser->role == USER_ROLE_ADMIN)
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}"><i class="fa fa-cogs"></i> Home</a></li>
                                @elseif($authUser->role == USER_ROLE_ORGANIZATION)
                                    <li><a class="dropdown-item" href="{{ route('organization.dashboard') }}"><i class="fa fa-building"></i> Organization Panel</a></li>
                                @elseif($authUser->role == USER_ROLE_INSTRUCTOR)
                                    <li><a class="dropdown-item" href="{{ route('instructor.dashboard') }}"><i class="fa fa-chalkboard-teacher"></i> Instructor Panel</a></li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('student.dashboard') }}"><i class="fa fa-user-graduate"></i> Student Panel</a></li>
                                @endif

                                {{-- Opciones comunes para todos los roles --}}
                                <li><a class="dropdown-item" href="{{ route('student.my-learning') }}"><i class="fa fa-book"></i> My courses</a></li>
                                <li><a class="dropdown-item" href="{{ route('student.my-consultation') }}"><i class="fa fa-headset"></i> Support</a></li>
                                <li><a class="dropdown-item" href="{{ route('student.profile') }}"><i class="fa fa-user-cog"></i> Perfil</a></li>
                                <li> <a class="dropdown-item" href="https://tuenlace.com/activar-licencias" target="_blank"> <i class="fa fa-key"></i> Activate Licenses</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fa fa-sign-out-alt"></i> Cerrar sesión
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</section>
