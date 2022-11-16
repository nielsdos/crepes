<header>
	<nav class="navbar navbar-expand-md navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand me-5" href="{{ url('/') }}">{{ config('app.name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav navbar-f me-auto">
                    <li class="nav-item">
                        <a class="nav-link{{ Route::is('course.index', 'course.show', 'course.edit') ? ' active' : '' }}" href="{{ url('/') }}">@svg('solid/book') {{ __('acts.courses') }}</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link{{ Route::is('subscriptions') ? ' active' : '' }}" href="{{ route('subscriptions') }}">@svg('solid/pen-to-square') {{ __('acts.my_subscriptions') }}</a>
                        </li>
                        @can('create', App\Models\Course::class)
                        <li class="nav-item">
                            <a class="nav-link{{ Route::is('course.create') ? ' active' : '' }}" href="{{ route('course.create') }}">@svg('solid/plus') {{ __('acts.create_course') }}</a>
                        </li>
                        @endcan
                        @can('index', App\Models\User::class)
                        <li class="nav-item">
                            <a class="nav-link{{ Request::segment(1) === 'account' || Route::is('subscriptions.show') ? ' active' : '' }}" href="{{ route('account.index') }}">@svg('solid/users') {{ __('acts.user_management') }}</a>
                        </li>
                        @endcan
                        @can('create', App\Models\Setting::class)
                        <li class="nav-item">
                            <a class="nav-link{{ Route::is('settings.edit') ? ' active' : '' }}" href="{{ route('settings.edit') }}">@svg('solid/gear') {{ __('acts.settings_management') }}</a>
                        </li>
                        @endcan
                    @endif
                </ul>

                <ul class="navbar-nav ms-auto">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link{{ Request::segment(1) === 'login' || Request::segment(1) === 'password' ? ' active' : '' }}" href="{{ route('login') }}">@svg('solid/arrow-right-to-bracket') {{ __('acts.login') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link{{ Request::segment(1) === 'register' || Request::segment(1) === 'email' ? ' active' : '' }}" href="{{ route('register') }}">@svg('solid/user-plus') {{ __('acts.register') }}</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ Auth::user()->fullname() }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('account.edit', 'me') }}">
                                    @svg('solid/user') {{ __('acts.account') }}
                                </a>
                                <button class="dropdown-item" onclick="document.getElementById('logout-form').submit()">@svg('solid/arrow-right-from-bracket') {{ __('acts.logout') }}</button>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
	</nav>
</header>
