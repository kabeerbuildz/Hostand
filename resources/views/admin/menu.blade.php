@php
    $admin_logo = getSettingsValByName('company_logo');
    $ids = parentId();
    $authUser = \App\Models\User::find($ids);
    $subscription = \App\Models\Subscription::find($authUser->subscription);
    $routeName = \Request::route()->getName();
    $pricing_feature_settings = getSettingsValByIdName(1, 'pricing_feature');
@endphp
<nav class="pc-sidebar">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="#" class="b-brand text-primary">
                <img src="{{ asset(Storage::url('upload/logo/')) . '/' . (isset($admin_logo) && !empty($admin_logo) ? $admin_logo : 'logo.png') }}"
                    alt="" class="logo logo-lg" />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="pc-navbar">
                <li class="pc-item pc-caption">
                    <label>{{ __('Home') }}</label>
                    <i class="ti ti-dashboard"></i>
                </li>
                <li class="pc-item {{ in_array($routeName, ['dashboard', 'home', '']) ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="pc-link">
                        <span class="pc-micon"><i class="ti ti-dashboard"></i></span>
                        <span class="pc-mtext">{{ __('Dashboard') }}</span>
                    </a>
                </li>
                @if (\Auth::user()->type == 'super admin')
                    @if (Gate::check('manage user'))
                        <li class="pc-item {{ in_array($routeName, ['users.index']) ? 'active' : '' }}">
                            <a href="{{ route('users.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-plus"></i></span>
                                <span class="pc-mtext">{{ __('Customers') }}</span>
                            </a>
                        </li>
                    @endif

                    <!-- Admin Property Management -->
                    <li
                        class="pc-item pc-hasmenu {{ in_array($routeName, ['admin.properties.index', 'admin.properties.show', 'admin.properties.services', 'admin.properties.analytics', 'admin.properties.maintenance-calendar']) ? 'pc-trigger active' : '' }}">
                        <a href="#!" class="pc-link">
                            <span class="pc-micon">
                                <i class="ti ti-building"></i>
                            </span>
                            <span class="pc-mtext">{{ __('Properties Overview') }}</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu"
                            style="display: {{ in_array($routeName, ['admin.properties.index', 'admin.properties.show', 'admin.properties.services', 'admin.properties.analytics', 'admin.properties.maintenance-calendar']) ? 'block' : 'none' }}">
                            <li
                                class="pc-item {{ in_array($routeName, ['admin.properties.index', 'admin.properties.show']) ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.properties.index') }}">
                                    <i class="ti ti-building"></i> {{ __('All Properties') }}
                                </a>
                            </li>
                            <li
                                class="pc-item {{ in_array($routeName, ['admin.properties.services']) ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.properties.services') }}">
                                    <i class="ti ti-tools"></i> {{ __('All Services') }}
                                </a>
                            </li>
                            <li
                                class="pc-item {{ in_array($routeName, ['admin.properties.analytics']) ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.properties.analytics') }}">
                                    <i class="ti ti-chart-bar"></i> {{ __('Analytics') }}
                                </a>
                            </li>
                            <li
                                class="pc-item {{ in_array($routeName, ['admin.properties.maintenance-calendar']) ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.properties.maintenance-calendar') }}">
                                    <i class="ti ti-calendar"></i> {{ __('Maintenance Calendar') }}
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Service Assignment Management -->
                    <li
                        class="pc-item pc-hasmenu {{ in_array($routeName, ['admin.service-assignment.index', 'admin.service-assignment.maintainer-schedule']) ? 'pc-trigger active' : '' }}">
                        <a href="#!" class="pc-link">
                            <span class="pc-micon">
                                <i class="ti ti-users"></i>
                            </span>
                            <span class="pc-mtext">{{ __('Service Assignment') }}</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu"
                            style="display: {{ in_array($routeName, ['admin.service-assignment.index', 'admin.service-assignment.maintainer-schedule']) ? 'block' : 'none' }}">
                            <li
                                class="pc-item {{ in_array($routeName, ['admin.service-assignment.index']) ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.service-assignment.index') }}">
                                    <i class="ti ti-user-plus"></i> {{ __('Assign Services') }}
                                </a>
                            </li>
                            <li
                                class="pc-item {{ in_array($routeName, ['admin.service-assignment.maintainer-schedule']) ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('admin.service-assignment.maintainer-schedule') }}">
                                    <i class="ti ti-calendar-week"></i> {{ __('Operator Schedules') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                @else
                    @if (\Auth::user()->type != 'owner')
                        @if (Gate::check('manage user') || Gate::check('manage role') || Gate::check('manage logged history'))
                            <li
                                class="pc-item pc-hasmenu {{ in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'pc-trigger active' : '' }}">
                                <a href="#!" class="pc-link">
                                    <span class="pc-micon">
                                        <i class="ti ti-users"></i>
                                    </span>
                                    <span class="pc-mtext">{{ __('Staff Management') }}</span>
                                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="pc-submenu"
                                    style="display: {{ in_array($routeName, ['users.index', 'logged.history', 'role.index', 'role.create', 'role.edit']) ? 'block' : 'none' }}">
                                    @if (Gate::check('manage user'))
                                        <li
                                            class="pc-item {{ in_array($routeName, ['users.index']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('users.index') }}">{{ __('Users') }}</a>
                                        </li>
                                    @endif
                                    @if (Gate::check('manage role'))
                                        <li
                                            class="pc-item  {{ in_array($routeName, ['role.index', 'role.create', 'role.edit']) ? 'active' : '' }}">
                                            <a class="pc-link" href="{{ route('role.index') }}">{{ __('Roles') }}
                                            </a>
                                        </li>
                                    @endif
                                    @if ($pricing_feature_settings == 'off' || $subscription->enabled_logged_history == 1)
                                        @if (Gate::check('manage logged history'))
                                            <li
                                                class="pc-item  {{ in_array($routeName, ['logged.history']) ? 'active' : '' }}">
                                                <a class="pc-link"
                                                    href="{{ route('logged.history') }}">{{ __('Logged History') }}</a>
                                            </li>
                                        @endif
                                    @endif
                                </ul>
                            </li>
                        @endif
                    @endif
                @endif

                @if (\Auth::user()->type == 'maintainer')
                    <!-- Operator/Maintainer Management -->
                    <li class="pc-item pc-caption">
                        <label>{{ __('Operator Dashboard') }}</label>
                        <i class="ti ti-tools"></i>
                    </li>

                    <li
                        class="pc-item pc-hasmenu {{ in_array($routeName, ['operator.dashboard', 'operator.daily-plan', 'operator.weekly-plan', 'operator.reports']) ? 'pc-trigger active' : '' }}">
                        <a href="#!" class="pc-link">
                            <span class="pc-micon">
                                <i class="ti ti-calendar-time"></i>
                            </span>
                            <span class="pc-mtext">{{ __('Work Management') }}</span>
                            <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                        </a>
                        <ul class="pc-submenu"
                            style="display: {{ in_array($routeName, ['operator.dashboard', 'operator.daily-plan', 'operator.weekly-plan', 'operator.reports']) ? 'block' : 'none' }}">
                            {{-- <li class="pc-item {{ in_array($routeName, ['operator.dashboard']) ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('operator.dashboard') }}">
                                    <i class="ti ti-dashboard"></i> {{ __('Dashboard') }}
                                </a>
                            </li> --}}
                            <li class="pc-item {{ in_array($routeName, ['operator.daily-plan']) ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('operator.daily-plan') }}">
                                    <i class="ti ti-calendar"></i> {{ __('Daily Plan') }}
                                </a>
                            </li>
                            <li class="pc-item {{ in_array($routeName, ['operator.weekly-plan']) ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('operator.weekly-plan') }}">
                                    <i class="ti ti-calendar-week"></i> {{ __('Weekly Plan') }}
                                </a>
                            </li>
                            <li class="pc-item {{ in_array($routeName, ['operator.reports']) ? 'active' : '' }}">
                                <a class="pc-link" href="{{ route('operator.reports') }}">
                                    <i class="ti ti-report"></i> {{ __('Reports') }}
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif

                @if (
                    \Auth::user()->type == 'super admin' ||
                        Gate::check('manage property') ||
                        Gate::check('manage unit') ||
                        Gate::check('manage tenant') ||
                        Gate::check('manage invoice') ||
                        Gate::check('manage expense') ||
                        Gate::check('manage maintainer') ||
                        Gate::check('manage maintenance request') ||
                        Gate::check('manage contact') ||
                        Gate::check('manage support') ||
                        Gate::check('manage note'))
                    <li class="pc-item pc-caption">
                        <label>{{ __('Business Management') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>

                    {{-- @if (Gate::check('manage tenant'))
                        <li
                            class="pc-item {{ in_array($routeName, ['tenant.index', 'tenant.create', 'tenant.edit', 'tenant.show']) ? 'active' : '' }}">
                            <a href="{{ route('tenant.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user"></i></span>
                                <span class="pc-mtext">{{ __('Tenants') }}</span>
                            </a>
                        </li>
                    @endif --}}
                    {{-- @if (Gate::check('manage maintainer'))
                        <li class="pc-item {{ in_array($routeName, ['maintainer.index']) ? 'active' : '' }}">
                            <a href="{{ route('maintainer.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-user-check"></i></span>
                                <span class="pc-mtext">{{ __('Maintainers') }}</span>
                            </a>
                        </li>
                    @endif --}}
                    @if(Auth::user()->type != "super admin")
                    @if (Gate::check('manage tenant') || Gate::check('manage property') || Gate::check('manage unit'))
                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show', 'unit.index']) ? 'pc-trigger active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-home"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Real Estate') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show', 'unit.index']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage property'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['property.index', 'property.create', 'property.edit', 'property.show']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('property.index') }}">{{ __('Properties') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage unit'))
                                    <li class="pc-item {{ in_array($routeName, ['unit.index']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('unit.index') }}">{{ __('Units') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                    @endif
                    @endif

                    @if ((Gate::check('manage maintainer') || Gate::check('manage maintenance request')) && Auth::user()->type !== 'super admin')
                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['maintenance-request.index', 'maintenance-request.pending', 'maintenance-request.inprogress']) ? 'pc-trigger active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-tool"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Maintenance') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['maintenance-request.index', 'maintenance-request.pending', 'maintenance-request.inprogress']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage maintenance request'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['maintenance-request.index']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('maintenance-request.index') }}">{{ __('All Requests') }}</a>
                                    </li>
                                    <li
                                        class="pc-item {{ in_array($routeName, ['maintenance-request.pending']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('maintenance-request.pending') }}">{{ __('Pending') }}</a>
                                    </li>
                                    <li
                                        class="pc-item {{ in_array($routeName, ['maintenance-request.inprogress']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('maintenance-request.inprogress') }}">{{ __('In Progress') }}</a>
                                    </li>
                                    <li class="pc-item {{ in_array($routeName, ['maintenance-request.completed']) ? 'active' : '' }}">
                                        <a class="pc-link" href="{{ route('maintenance-request.completed') }}">{{ __('Completed') }}</a>
                                    </li>

                                @endif
                            </ul>
                        </li>

                    @endif

                    @if (\Auth::user()->type == 'super admin' || Gate::check('manage invoice') || Gate::check('manage expense'))
                        <li
                            class="pc-item pc-hasmenu  {{ in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show', 'expense.index']) ? 'pc-trigger  active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-file-invoice"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Finance') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show', 'expense.index']) ? 'block' : 'none' }}">
                                @if (\Auth::user()->type == 'super admin' || Gate::check('manage invoice'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['invoice.index', 'invoice.create', 'invoice.edit', 'invoice.show']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('invoice.index') }}">{{ __('Invoices') }}</a>
                                    </li>
                                @endif
                                @if (\Auth::user()->type == 'super admin' || Gate::check('manage expense'))
                                    <li class="pc-item {{ in_array($routeName, ['expense.index']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('expense.index') }}">{{ __('Expense') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    @if (Gate::check('manage contact'))
                        <li class="pc-item {{ in_array($routeName, ['contact.index']) ? 'active' : '' }}">
                            <a href="{{ route('contact.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-phone-call"></i></span>
                                {{-- <span class="pc-mtext">{{ __('Contact Diary') }}</span> --}}
                                <span class="pc-mtext">{{ __('Live Chat') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (auth()->user()->type != 'owner')
         @if (Gate::check('manage maintainer'))
                    <li class="pc-item {{ in_array($routeName, ['maintainer.index', 'maintainer.create', 'maintainer.edit', 'maintainer.show']) ? 'active' : '' }}">
                        <a href="{{ route('maintainer.index') }}" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-user-check"></i></span>
                            <span class="pc-mtext">{{ __('Maintainers') }}</span>
                        </a>
                    </li>
                @endif
                @endif
                

                    @if (Gate::check('manage note'))
                        <li class="pc-item {{ in_array($routeName, ['note.index']) ? 'active' : '' }} ">
                            <a href="{{ route('note.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-notebook"></i></span>
                                <span class="pc-mtext">{{ __('Notice Board') }}</span>
                            </a>
                        </li>
                    @endif
               @if(\Auth::user()->type == 'owner')
    <li class="pc-item pc-caption">
        <label>{{ __('Account') }}</label>
        <i class="ti ti-user"></i>
    </li>
    <li class="pc-item {{ in_array($routeName, ['setting.index']) ? 'active' : '' }}">
        <a href="{{ route('setting.index') }}" class="pc-link">
            <span class="pc-micon"><i class="ti ti-user"></i></span>
            <span class="pc-mtext">{{ __('My Profile') }}</span>
        </a>
    </li>
@endif

                @endif

@if(auth()->user()->type != 'owner')
                @if (Gate::check('manage notification') || (Auth::user()->type == 'super admin' && Auth::user()->id == 339))
                    <li class="pc-item pc-caption">
                        <label>{{ __('System Configuration') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>
                    @if (Gate::check('manage types') && Auth::user()->type == 'super admin')
                        <li class="pc-item {{ in_array($routeName, ['type.index']) ? 'active' : '' }}">
                            <a href="{{ route('type.index') }}" class="pc-link">
                                <span class="pc-micon"><i data-feather="file-text"></i></span>
                                <span class="pc-mtext">{{ __('Types') }}</span>
                            </a>
                        </li>
                    @endif
                    @if (Gate::check('manage notification'))
                        <li class="pc-item {{ in_array($routeName, ['notification.index']) ? 'active' : '' }} ">
                            <a href="{{ route('notification.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-bell"></i></span>
                                <span class="pc-mtext">{{ __('Email Notification') }}</span>
                            </a>
                        </li>
                    @endif

                @endif


                @if (
                    (Gate::check('manage pricing packages') ||
                        Gate::check('manage pricing transation') ||
                        Gate::check('manage account settings') ||
                        Gate::check('manage password settings') ||
                        Gate::check('manage general settings') ||
                        Gate::check('manage email settings') ||
                        Gate::check('manage payment settings') ||
                        Gate::check('manage company settings') ||
                        Gate::check('manage seo settings') ||
                        Gate::check('manage google recaptcha settings')) &&
                        Auth::user()->id != 339)
                    <li class="pc-item pc-caption">
                        <label>{{ __('System Settings') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>

                    @if (Gate::check('manage FAQ') || Gate::check('manage Page'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-layout-rows"></i>
                                </span>
                                <span class="pc-mtext">{{ __('CMS') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['homepage.index', 'FAQ.index', 'pages.index', 'footerSetting']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage home page'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['homepage.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('homepage.index') }}"
                                            class="pc-link">{{ __('Home Page') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage Page'))
                                    <li class="pc-item {{ in_array($routeName, ['pages.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('pages.index') }}"
                                            class="pc-link">{{ __('Custom Page') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage FAQ'))
                                    <li class="pc-item {{ in_array($routeName, ['FAQ.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('FAQ.index') }}" class="pc-link">{{ __('FAQ') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage footer'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['footerSetting']) ? 'active' : '' }} ">
                                        <a href="{{ route('footerSetting') }}"
                                            class="pc-link">{{ __('Footer') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage auth page'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['authPage.index']) ? 'active' : '' }} ">
                                        <a href="{{ route('authPage.index') }}"
                                            class="pc-link">{{ __('Auth Page') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif
                    @if ($pricing_feature_settings == 'on')
                        {{-- @if (Gate::check('manage pricing packages') || Gate::check('manage pricing transation')) --}}
                        @if (Auth::user()->type == 'super admin')
                            <li
                                class="pc-item pc-hasmenu {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'active' : '' }}">
                                <a href="#!" class="pc-link">
                                    <span class="pc-micon">
                                        <i class="ti ti-package"></i>
                                    </span>
                                    <span class="pc-mtext">{{ __('Pricing') }}</span>
                                    <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                                </a>
                                <ul class="pc-submenu"
                                    style="display: {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show', 'subscription.transaction']) ? 'block' : 'none' }}">
                                    @if (Gate::check('manage pricing packages'))
                                        <li
                                            class="pc-item {{ in_array($routeName, ['subscriptions.index', 'subscriptions.show']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('subscriptions.index') }}">{{ __('Packages') }}</a>
                                        </li>
                                    @endif
                                    @if (Gate::check('manage pricing transation'))
                                        <li
                                            class="pc-item {{ in_array($routeName, ['subscription.transaction']) ? 'active' : '' }}">
                                            <a class="pc-link"
                                                href="{{ route('subscription.transaction') }}">{{ __('Transactions') }}</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                    @endif
                    @if (Gate::check('manage coupon') || Gate::check('manage coupon history'))
                        <li
                            class="pc-item pc-hasmenu {{ in_array($routeName, ['coupons.index', 'coupons.history']) ? 'active' : '' }}">
                            <a href="#!" class="pc-link">
                                <span class="pc-micon">
                                    <i class="ti ti-shopping-cart-discount"></i>
                                </span>
                                <span class="pc-mtext">{{ __('Coupons') }}</span>
                                <span class="pc-arrow"><i data-feather="chevron-right"></i></span>
                            </a>
                            <ul class="pc-submenu"
                                style="display: {{ in_array($routeName, ['coupons.index', 'coupons.history']) ? 'block' : 'none' }}">
                                @if (Gate::check('manage coupon'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['coupons.index']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('coupons.index') }}">{{ __('All Coupon') }}</a>
                                    </li>
                                @endif
                                @if (Gate::check('manage coupon history'))
                                    <li
                                        class="pc-item {{ in_array($routeName, ['coupons.history']) ? 'active' : '' }}">
                                        <a class="pc-link"
                                            href="{{ route('coupons.history') }}">{{ __('Coupon History') }}</a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if (Gate::check('manage account settings') ||
                            Gate::check('manage password settings') ||
                            Gate::check('manage general settings') ||
                            Gate::check('manage email settings') ||
                            Gate::check('manage payment settings') ||
                            Gate::check('manage company settings') ||
                            Gate::check('manage seo settings') ||
                            Gate::check('manage google recaptcha settings'))
                        <li class="pc-item {{ in_array($routeName, ['setting.index']) ? 'active' : '' }} ">
                            <a href="{{ route('setting.index') }}" class="pc-link">
                                <span class="pc-micon"><i class="ti ti-settings"></i></span>
                                <span class="pc-mtext">{{ __('Settings') }}</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- @if (
                    \Auth::user()->type == 'owner' ||
                        \Auth::user()->type == 'tenant' ||
                        \Auth::user()->type == 'super admin' ||
                        \Auth::user()->type == 'maintainer')
                    <li class="pc-item pc-caption">
                        <label>{{ __('Support') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>
                    <li
                        class="pc-item {{ in_array($routeName, ['support.index', 'support.create', 'support.show', 'support.edit']) ? 'active' : '' }}">
                        <a href="{{ route('support.index') }}" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-ticket"></i></span>
                            <span class="pc-mtext">{{ __('Tickets') }}</span>
                        </a>
                    </li>
                @endif --}}
                @endif
                @if (
                    \Auth::user()->type == 'owner' ||
                        \Auth::user()->type == 'tenant' ||
                        \Auth::user()->type == 'super admin' ||
                        \Auth::user()->type == 'maintainer')
                    <li class="pc-item pc-caption">
                        <label>{{ __('Support') }}</label>
                        <i class="ti ti-chart-arcs"></i>
                    </li>
                    <li
                        class="pc-item {{ in_array($routeName, ['support.index', 'support.create', 'support.show', 'support.edit']) ? 'active' : '' }}">
                        <a href="{{ route('support.index') }}" class="pc-link">
                            <span class="pc-micon"><i class="ti ti-ticket"></i></span>
                            <span class="pc-mtext">{{ __('Tickets') }}</span>
                        </a>
                    </li>
                @endif
            </ul>
            <div class="w-100 text-center">
                <div class="badge theme-version badge rounded-pill bg-light text-dark f-12"></div>
            </div>
        </div>
    </div>
</nav>
