@php
$configData = Helper::appClasses();

use Illuminate\Support\Str;

$currentUrl = url()->current();

function checkActiveClass($menuItem, $currentUrl) {
    // Verificar si el elemento del menú tiene una URL y coincide exactamente con la URL actual
    if (isset($menuItem->url) && $currentUrl === url($menuItem->url)) {
        return 'active';
    }

    // Si hay un submenú, comprobar cada elemento del submenú de forma recursiva
    if (isset($menuItem->submenu)) {
        foreach ($menuItem->submenu as $submenuItem) {
            if (checkActiveClass($submenuItem, $currentUrl) === 'active') {
                return 'active open';
            }
        }
    }

    return '';
}
@endphp

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
  @if(!isset($navbarFull))
  <div class="app-brand demo">
    <a href="{{ url('/') }}" class="app-brand-link">
      <div class="container">
        <img src="{{ asset($companySettings->logo_black) }}" alt="" class="" style="max-width: 150px;">
      </div>
    </a>
  </div>
  @endif

  <div class="menu-inner-shadow"></div>
  <ul class="menu-inner py-1">
    @foreach ($menuData[0]->menu as $menu)
      @cannot ('access_' . $menu->slug)
        @continue
      @endcan

      @php
      // Obtener la clase activa para el elemento actual usando la función recursiva
      $activeClass = checkActiveClass($menu, $currentUrl);
      @endphp

      @if (isset($menu->menuHeader))
      <li class="menu-header small text-uppercase">
        <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
      </li>
      @else
      <li class="menu-item {{ $activeClass }}" @isset($menu->id) id="{{ $menu->id }}" @endisset>
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
