@php
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');

@endphp

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Scripts para notificaciones --}}
<script>
  window.notificationUrl = '{{ route('notifications.index') }}';
  window.notificationReadUrl = '{{ route('notifications.markAsRead') }}';
  window.orderUrl = '{{ route('orders.index') }}';
</script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
      let popupActive = false;

      function fetchNotifications() {
          if (!popupActive) {
              fetch(window.notificationUrl)
                  .then(response => response.json())
                  .then(notifications => {
                      const notificationContainer = document.getElementById('notification-list');
                      const notificationIcon = document.getElementById('notification-icon');
                      notificationContainer.innerHTML = ''; // Limpiar la lista de notificaciones

                      if (notifications.length > 0) {
                          console.log('Notificaciones recibidas:', notifications);

                          let unreadNotifications = notifications.filter(notification => !notification.read_at);
                          console.log('Notificaciones no leídas:', unreadNotifications);

                          if (unreadNotifications.length > 0) {
                              let lastNotification = unreadNotifications[unreadNotifications.length - 1]; // Obtener la última notificación no leída
                              let notificationIds = unreadNotifications.map(notification => notification.id); // Obtener los IDs de todas las notificaciones no leídas

                              unreadNotifications.forEach(notification => {
                                  const notificationData = notification.data;
                                  const notificationElement = document.createElement('li');
                                  notificationElement.classList.add('notification-item');
                                  notificationElement.setAttribute('data-id', notification.id);

                                  const customerName = notificationData.customer_name || 'Nombre no disponible';
                                  const customerLastname = notificationData.customer_lastname || 'Apellido no disponible';
                                  const address = notificationData.address || 'Dirección no disponible';
                                  const paymentMethod = notificationData.payment_method || 'Método de pago no disponible';
                                  const status = notificationData.status ? notificationData.status.toLowerCase().replace(/ /g, '_') : 'estado_desconocido';
                                  const amount = notificationData.amount || 'Monto no disponible';
                                  const createdAt = notificationData.created_at ? new Date(notificationData.created_at).toLocaleTimeString() : 'Fecha no disponible';

                                  notificationElement.innerHTML = `
                                      <div class="notification-time">${createdAt}</div>
                                      <div class="notification-content">
                                          <h6>Nueva orden</h6>
                                          <p>${customerName} ${customerLastname}</p>
                                          <p>${address}</p>
                                          <p>${paymentMethod}</p>
                                          <div class="notification-footer">
                                              <span class="notification-status ${status}">${notificationData.status || 'Estado no disponible'}</span>
                                              <span class="notification-amount">UYU ${amount}</span>
                                          </div>
                                          <button class="notification-action" onclick="markAsRead('${notification.id}', '${notificationData.order_uuid}')">VER ORDEN</button>
                                      </div>
                                  `;
                                  notificationContainer.appendChild(notificationElement);
                              });

                              popupActive = true;
                              if (unreadNotifications.length > 1) {
                                  Swal.fire({
                                      title: 'Tienes ' + unreadNotifications.length + ' ventas nuevas',
                                      text: 'Haz clic en "Ver ventas" para ver todas los ventas nuevas.',
                                      icon: 'info',
                                      confirmButtonText: 'Ver ventas'
                                  }).then((result) => {
                                      popupActive = false;
                                      if (result.isConfirmed) {
                                          markAllAsRead(notificationIds);
                                      }
                                  });
                              } else if (lastNotification) {
                                  Swal.fire({
                                      title: '¡Hay una nueva venta!',
                                      text: `Venta #${lastNotification.data.order_id} de ${lastNotification.data.customer_name} ${lastNotification.data.customer_lastname}`,
                                      icon: 'info',
                                      confirmButtonText: 'Ver Venta'
                                  }).then((result) => {
                                      popupActive = false;
                                      if (result.isConfirmed) {
                                          markAsRead(lastNotification.id, lastNotification.data.order_uuid);
                                      }
                                  });
                              }

                              notificationIcon.textContent = unreadNotifications.length;
                              notificationIcon.classList.add('bg-danger');
                          }
                      } else {
                          notificationIcon.textContent = '';
                          notificationIcon.classList.remove('bg-danger');
                      }
                  }).catch(error => console.error('Error al obtener notificaciones:', error));
          }
      }

      setInterval(fetchNotifications, 5000); // Poll cada 5 segundos

      window.markAsRead = function(notificationId, orderUuid) {
        console.log('Marcando como leída la notificación:', notificationId);
        fetch(window.notificationReadUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ notification_ids: [notificationId] })
        }).then(response => response.json())
          .then(data => {
              console.log('Respuesta del servidor al marcar como leída:', data);
              if (data.affected > 0) { // Verifica que las notificaciones fueron realmente marcadas como leídas
                  const notificationElement = document.querySelector(`li[data-id="${notificationId}"]`);
                  if (notificationElement) {
                      notificationElement.remove();
                  }
                  const notificationIcon = document.getElementById('notification-icon');
                  const currentCount = parseInt(notificationIcon.textContent, 10);
                  if (currentCount > 1) {
                      notificationIcon.textContent = currentCount - 1;
                  } else {
                      notificationIcon.textContent = '';
                      notificationIcon.classList.remove('bg-danger');
                  }
              } else {
                  console.error('No se marcó ninguna notificación como leída.');
              }
              // Redirigir a la orden
              window.location.href = `${window.orderUrl}/${orderUuid}/show`;
          }).catch(error => {
              console.error('Error al marcar como leída:', error);
              // Redirigir a la orden en caso de error
              window.location.href = `${window.orderUrl}/${orderUuid}/show`;
          });
      };


      window.markAllAsRead = function(notificationIds) {
          console.log('Marcando todas como leídas:', notificationIds);
          fetch(window.notificationReadUrl, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({ notification_ids: notificationIds })
          }).then(response => response.json())
            .then(data => {
              console.log('Respuesta del servidor al marcar todas como leídas:', data);
              document.querySelectorAll('.notification-item').forEach(item => item.remove());
              const notificationIcon = document.getElementById('notification-icon');
              notificationIcon.textContent = '';
              notificationIcon.classList.remove('bg-danger');
              window.location.href = `${window.orderUrl}`;
          }).catch(error => console.error('Error al marcar todas como leídas:', error));
      };
  });
  </script>
{{-- Fin scripts para notificaciones --}}





<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
  <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{$containerNav}}">
      @endif

      <!--  Brand demo (display only for navbar-full and hide on below xl) -->
      @if(isset($navbarFull))
      <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{url('/')}}" class="app-brand-link gap-2">
          <span class="app-brand-logo demo">@include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])</span>
          <span class="app-brand-text demo menu-text fw-bold">{{config('variables.templateName')}}</span>
        </a>

        @if(isset($menuHorizontal))
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
          <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
        @endif
      </div>
      @endif

      <!-- ! Not required for layout-without-menu -->
      @if(!isset($navbarHideToggle))
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
          <i class="bx bx-menu bx-sm"></i>
        </a>
      </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">

      @if($configData['hasCustomizer'] == true)
      <!-- Style Switcher -->
      <div class="navbar-nav align-items-center">
        <div class="nav-item dropdown-style-switcher dropdown me-2 me-xl-0">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <i class='bx bx-sm'></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-start dropdown-styles">
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="light">
                <span class="align-middle"><i class='bx bx-sun me-2'></i>Claro</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="dark">
                <span class="align-middle"><i class="bx bx-moon me-2"></i>Oscuro</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" data-theme="system">
                <span class="align-middle"><i class="bx bx-desktop me-2"></i>Sistema</span>
              </a>
            </li>
          </ul>
        </div>
      </div>
      <!--/ Style Switcher -->
      @endif




      <ul class="navbar-nav flex-row align-items-center ms-auto">

        <!-- Notification -->
        <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-1">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
              <i class="bx bx-bell bx-sm"></i>
              <span id="notification-icon" class="badge rounded-pill badge-notifications"></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end py-0">
              <li class="dropdown-menu-header border-bottom">
                  <div class="dropdown-header d-flex align-items-center py-3">
                      <h5 class="text-body mb-0 me-auto">Notificaciones</h5>
                      <a href="javascript:void(0)" class="dropdown-notifications-all text-body" data-bs-toggle="tooltip" data-bs-placement="top" title="Marcar todas como leídas">
                          <i class="bx fs-4 bx-envelope-open"></i>
                      </a>
                  </div>
              </li>
              <li class="dropdown-notifications-list scrollable-container">
                  <ul id="notification-list" class="list-group list-group-flush">
                      <!-- Las notificaciones se cargarán aquí -->
                  </ul>
              </li>
              <li class="dropdown-menu-footer border-top p-3">
                  <button class="btn btn-primary text-uppercase w-100">Ver todas las notificaciones</button>
              </li>
          </ul>
        </li>
        <!--/ Notification -->


        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <div class="avatar avatar-online">
              <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="#">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-online">
                      <img src="{{ Auth::user() ? Auth::user()->profile_photo_url : asset('assets/img/avatars/1.png') }}" alt class="w-px-40 h-auto rounded-circle">
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <span class="fw-medium d-block">
                      @if (Auth::check())
                      {{ Auth::user()->name }} {{ Auth::user()->lastname }}
                      @else
                        -
                      @endif
                    </span>
                    <small class="text-muted">
                      @if (Auth::check())
                        {{ __(Auth::user()->roles->first()->name) ?? 'Sin Rol' }}
                      @else
                        -
                      @endif</small>
                  </div>
                </div>
              </a>
            </li>
            @if (Auth::check() && Laravel\Jetstream\Jetstream::hasApiFeatures())
            <li>
              <a class="dropdown-item" href="{{ route('api-tokens.index') }}">
                <i class='bx bx-key me-2'></i>
                <span class="align-middle">API Tokens</span>
              </a>
            </li>
            @endif


              <li>
                <div class="dropdown-divider"></div>
              </li>
              @if (Auth::check())
              <li>
                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  <i class='bx bx-power-off me-2'></i>
                  <span class="align-middle">{{ __('Cerrar Sesión') }}</span>
                </a>
              </li>
              <form method="POST" id="logout-form" action="{{ route('logout') }}">
                @csrf
              </form>
              @else
              <li>
                <a class="dropdown-item" href="{{ Route::has('login') ? route('login') : url('auth/login-basic') }}">
                  <i class='bx bx-log-in me-2'></i>
                  <span class="align-middle">{{ __('Sign In') }}</span>
                </a>
              </li>
              @endif
            </ul>
          </li>
          <!--/ User -->
        </ul>
      </div>

      @if(!isset($navbarDetached))
    </div>
    @endif
  </nav>
  <!-- / Navbar -->
