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
                    @auth
                        @php
                            $role = $authUser->role;
                        @endphp

                        @if($role == USER_ROLE_ADMIN)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">{{ __('Admin Panel') }}</a>
                            </li>
                        @elseif($role == USER_ROLE_ORGANIZATION)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('organization.dashboard') }}">{{ __('Organization Panel') }}</a>
                            </li>
                        @elseif($role == USER_ROLE_INSTRUCTOR)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('instructor.dashboard') }}">{{ __('Instructor Panel') }}</a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('student.dashboard') }}">{{ __('Student Panel') }}</a>
                            </li>
                        @endif

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('logout') }}">{{ __('Logout') }}</a>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Sign In') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('sign-up') }}">{{ __('Sign Up') }}</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
    <!-- Navigation -->
</section>
