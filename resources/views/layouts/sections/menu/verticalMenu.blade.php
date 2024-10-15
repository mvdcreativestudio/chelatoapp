@php
$configData = Helper::appClasses();
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  @if(!isset($navbarFull))
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <div class="container">
        <img src="{{ asset($companySettings->logo_black) }}" alt="" class="" style="max-width: 150px;">
      </div>
    </a>
    {{-- Colapsa el menú --}}

    {{-- <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a> --}}
  </div>
  @endif

  <div class="menu-inner-shadow"></div>
  <ul class="menu-inner py-1">
    @foreach ($menuData[0]->menu as $menu)
      @cannot ('access_' . $menu->slug)
        @continue
      @endcan

      @php
      $activeClass = null;
      $currentRouteName = Route::currentRouteName();
      if ($currentRouteName === $menu->slug) {
        $activeClass = 'active';
      } elseif (isset($menu->submenu)) {
        foreach ($menu->submenu as $submenu) {
          if (str_contains($currentRouteName, $submenu->slug) && strpos($currentRouteName, $submenu->slug) === 0) {
            $activeClass = 'active open';
          }
        }
      }
      @endphp

      @if (isset($menu->menuHeader))
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
      </li>
      @else
      <li class="menu-item {{$activeClass}}" @isset($menu->id) id="{{ $menu->id }}" @endisset>
        <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}" class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}" @if(isset($menu->target) && !empty($menu->target)) target="_blank" @endif>
          @isset($menu->icon)
          <i class="{{ $menu->icon }}"></i>
          @endisset
          <div class="text-truncate">{{ isset($menu->name) ? __($menu->name) : '' }}</div>
          @isset($menu->badge)
          <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
          @endisset
        </a>
        @if (isset($menu->submenu))
        @include('layouts.sections.menu.submenu', ['menu' => $menu->submenu])
        @endif
      </li>
      @endif
    @endforeach
  </ul>
</aside>
