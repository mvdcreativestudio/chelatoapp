@extends('layouts/layoutMaster')

@section('title', 'Omnicanalidad')

@section('vendor-style')
  @vite('resources/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.scss')
@endsection

@section('page-style')
  @vite('resources/assets/vendor/scss/pages/app-chat.scss')
@endsection

@section('vendor-script')
  @vite('resources/assets/vendor/libs/bootstrap-maxlength/bootstrap-maxlength.js')
@endsection

@section('page-script')
  @vite('resources/assets/js/app-chat.js')
@endsection

@section('content')
<div class="app-chat overflow-hidden card">
  <div class="row g-0">
    <!-- Sidebar Left -->
    <div class="col app-chat-sidebar-left app-sidebar overflow-hidden" id="app-chat-sidebar-left">
      <div class="chat-sidebar-left-user sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap p-4 mt-2">
        <div class="avatar avatar-xl avatar-online">
          <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
        </div>
        <h5 class="mt-3 mb-1">John Doe</h5>
        <small class="text-muted">UI/UX Designer</small>
        <i class="bx bx-x bx-sm cursor-pointer close-sidebar me-1 fs-4 " data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-left"></i>
      </div>
      <div class="sidebar-body px-4 pb-4">
        <div class="my-3">
          <span class="text-muted text-uppercase">About</span>
          <textarea id="chat-sidebar-left-user-about" class="form-control chat-sidebar-left-user-about mt-2" rows="4" maxlength="120">Dessert chocolate cake lemon drops jujubes. Biscuit cupcake ice cream bear claw brownie brownie marshmallow.</textarea>
        </div>
        <div class="my-4">
          <span class="text-muted text-uppercase">Status</span>
          <div class="d-grid gap-1 mt-2">
            <div class="form-check form-check-success">
              <input name="chat-user-status" class="form-check-input" type="radio" value="active" id="user-active" checked>
              <label class="form-check-label" for="user-active">Active</label>
            </div>
            <div class="form-check form-check-danger">
              <input name="chat-user-status" class="form-check-input" type="radio" value="busy" id="user-busy">
              <label class="form-check-label" for="user-busy">Busy</label>
            </div>
            <div class="form-check form-check-warning">
              <input name="chat-user-status" class="form-check-input" type="radio" value="away" id="user-away">
              <label class="form-check-label" for="user-away">Away</label>
            </div>
            <div class="form-check form-check-secondary">
              <input name="chat-user-status" class="form-check-input" type="radio" value="offline" id="user-offline">
              <label class="form-check-label" for="user-offline">Offline</label>
            </div>
          </div>
        </div>
        <div class="my-4">
          <span class="text-muted text-uppercase">Settings</span>
          <ul class="list-unstyled d-grid gap-2 mt-2">
            <li class="d-flex justify-content-between align-items-center">
              <div>
                <i class='bx bx-check-circle me-1'></i>
                <span class="align-middle">Two-step Verification</span>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="twoStepVerification">
              </div>
            </li>
            <li class="d-flex justify-content-between align-items-center">
              <div>
                <i class='bx bx-bell me-1'></i>
                <span class="align-middle">Notification</span>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="switchNotification" checked>
              </div>
            </li>
          </ul>
        </div>
        <div class="d-flex mt-4">
          <button class="btn btn-primary" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-left">Logout</button>
        </div>
      </div>
    </div>
    <!-- /Sidebar Left-->

    <!-- Chat & Contacts -->
    <div class="col app-chat-contacts app-sidebar flex-grow-0 overflow-hidden border-end" id="app-chat-contacts">
      <div class="sidebar-header pt-3 px-3 mx-1">
        <div class="d-flex align-items-center me-3 me-lg-0">
          <div class="flex-shrink-0 avatar avatar-online me-2" data-bs-toggle="sidebar" data-overlay="app-overlay-ex" data-target="#app-chat-sidebar-left">
            <img class="user-avatar rounded-circle cursor-pointer" src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar">
          </div>
          <div class="flex-grow-1 input-group input-group-merge rounded-pill ms-1">
            <span class="input-group-text" id="basic-addon-search31"><i class="bx bx-search fs-4"></i></span>
            <input type="text" class="form-control chat-search-input" placeholder="Buscar..." aria-label="Buscar..." aria-describedby="basic-addon-search31">
          </div>
        </div>
        <i class="bx bx-x cursor-pointer position-absolute top-0 end-0 mt-2 me-1 fs-4 d-lg-none d-block" data-overlay data-bs-toggle="sidebar" data-target="#app-chat-contacts"></i>
      </div>
      <hr class="container-m-nx mt-3 mb-0">
      <div class="sidebar-body">

        <!-- Chats -->
        <ul class="list-unstyled chat-contact-list pt-1" id="chat-list">
          <li class="chat-contact-list-item chat-contact-list-item-title">
            <h5 class="text-primary mb-0">Conversaciones</h5>
          </li>
          <li class="chat-contact-list-item chat-list-item-0 d-none">
            <h6 class="text-muted mb-0">No se encontraron conversaciones</h6>
          </li>
          <li class="chat-contact-list-item">
            <a class="d-flex align-items-center">
              <div class="flex-shrink-0 avatar avatar-online">
                <img src="{{ asset('assets/img/avatars/13.png') }}" alt="Avatar" class="rounded-circle">
              </div>
              <div class="chat-contact-info flex-grow-1 ms-3">
                <h6 class="chat-contact-name text-truncate m-0">Martín Santamaría</h6>
                <p class="chat-contact-status text-truncate mb-0 text-muted">Estamos trabajando en eso.</p>
              </div>
              <small class="text-muted mb-auto">5 Minutos</small>
            </a>
          </li>
          <li class="chat-contact-list-item active">
            <a class="d-flex align-items-center">
              <div class="flex-shrink-0 avatar avatar-offline">
                <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle">
              </div>
              <div class="chat-contact-info flex-grow-1 ms-3">
                <h6 class="chat-contact-name text-truncate m-0">Gastón Dotta</h6>
                <p class="chat-contact-status text-truncate mb-0 text-muted">En cuánto llega mi pedido?</p>
              </div>
              <small class="text-muted mb-auto">30 Minutos</small>
            </a>
          </li>
          <li class="chat-contact-list-item">
            <a class="d-flex align-items-center">
              <div class="flex-shrink-0 avatar avatar-busy">
                <span class="avatar-initial rounded-circle bg-label-success">NM</span>
              </div>
              <div class="chat-contact-info flex-grow-1 ms-3">
                <h6 class="chat-contact-name text-truncate m-0">Nicolás Malceñido</h6>
                <p class="chat-contact-status text-truncate mb-0 text-muted">Dale, te lo mando en breve</p>
              </div>
              <small class="text-muted mb-auto">Ayer</small>
            </a>
          </li>
        </ul>
        <!-- Contacts -->
        <ul class="list-unstyled chat-contact-list mb-0" id="contact-list">
          <li class="chat-contact-list-item chat-contact-list-item-title">
            <h5 class="text-primary mb-0">Contactos</h5>
          </li>
          <li class="chat-contact-list-item contact-list-item-0 d-none">
            <h6 class="text-muted mb-0">No se encontraron contactos</h6>
          </li>
          <li class="chat-contact-list-item">
            <a class="d-flex align-items-center">
              <div class="flex-shrink-0 avatar avatar-offline">
                <img src="{{ asset('assets/img/avatars/4.png') }}" alt="Avatar" class="rounded-circle">
              </div>
              <div class="chat-contact-info flex-grow-1 ms-3">
                <h6 class="chat-contact-name text-truncate m-0">Silvana Traversa</h6>
                <p class="chat-contact-status text-truncate mb-0 text-muted">Gerente</p>
              </div>
            </a>
          </li>
          <li class="chat-contact-list-item">
            <a class="d-flex align-items-center">
              <div class="flex-shrink-0 avatar avatar-busy">
                <img src="{{ asset('assets/img/avatars/5.png') }}" alt="Avatar" class="rounded-circle">
              </div>
              <div class="chat-contact-info flex-grow-1 ms-3">
                <h6 class="chat-contact-name text-truncate m-0">Nicolas Baladón</h6>
                <p class="chat-contact-status text-truncate mb-0 text-muted">Viajero</p>
              </div>
            </a>
          </li>
          <li class="chat-contact-list-item">
            <a class="d-flex align-items-center">
              <div class="avatar d-block flex-shrink-0">
                <span class="avatar-initial rounded-circle bg-label-primary">SP</span>
              </div>
              <div class="chat-contact-info flex-grow-1 ms-3">
                <h6 class="chat-contact-name text-truncate m-0">Santiago Paradelo</h6>
                <p class="chat-contact-status text-truncate mb-0 text-muted">Developer</p>
              </div>
            </a>
          </li>
        </ul>
      </div>
    </div>
    <!-- /Chat contacts -->

    <!-- Chat History -->
    <div class="col app-chat-history">
      <div class="chat-history-wrapper">
        <div class="chat-history-header border-bottom">
          <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex overflow-hidden align-items-center">
              <i class="bx bx-menu bx-sm cursor-pointer d-lg-none d-block me-2" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-contacts"></i>
              <div class="flex-shrink-0 avatar">
                <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-right">
              </div>
              <div class="chat-contact-info flex-grow-1 ms-3">
                <h6 class="m-0">Gastón Dotta</h6>
                <small class="user-status text-muted">Sumeria</small>
              </div>
            </div>
            <div class="d-flex align-items-center">
              <i class="bx bx-phone-call cursor-pointer d-sm-block d-none me-3 fs-4"></i>
              <i class="bx bx-video cursor-pointer d-sm-block d-none me-3 fs-4"></i>
              <i class="bx bx-search cursor-pointer d-sm-block d-none me-3 fs-4"></i>
              <div class="dropdown">
                <button class="btn p-0" type="button" id="chat-header-actions" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="bx bx-dots-vertical-rounded fs-4"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="chat-header-actions">
                  <a class="dropdown-item" href="javascript:void(0);">Ver Contacto</a>
                  <a class="dropdown-item" href="javascript:void(0);">Silenciar</a>
                  <a class="dropdown-item" href="javascript:void(0);">Bloquear</a>
                  <a class="dropdown-item" href="javascript:void(0);">Vaciar Conversación</a>
                  <a class="dropdown-item" href="javascript:void(0);">Reportar</a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="chat-history-body">
          <ul class="list-unstyled chat-history mb-0">
            <li class="chat-message chat-message-right">
              <div class="d-flex overflow-hidden">
                <div class="chat-message-wrapper flex-grow-1">
                  <div class="chat-message-text">
                    <p class="mb-0">¡Chicos! 😄</p>
                  </div>
                  <div class="text-end text-muted mt-1">
                    <i class='bx bx-check-double text-success'></i>
                    <small>10:00 AM</small>
                  </div>
                </div>
                <div class="user-avatar flex-shrink-0 ms-3">
                  <div class="avatar avatar-sm">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                  </div>
                </div>
              </div>
            </li>
            <li class="chat-message">
              <div class="d-flex overflow-hidden">
                <div class="user-avatar flex-shrink-0 me-3">
                  <div class="avatar avatar-sm">
                    <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle">
                  </div>
                </div>
                <div class="chat-message-wrapper flex-grow-1">
                  <div class="chat-message-text">
                    <p class="mb-0">Hola Gastón. ¿Cómo podemos ayudarte?</p>
                  </div>
                  <div class="text-muted mt-1">
                    <small>10:02 AM</small>
                  </div>
                </div>
              </div>
            </li>
            <li class="chat-message chat-message-right">
              <div class="d-flex overflow-hidden">
                <div class="chat-message-wrapper flex-grow-1">
                  <div class="chat-message-text">
                    <p class="mb-0">Me gustaría hacer un pedido</p>
                  </div>
                  <div class="text-end text-muted mt-1">
                    <i class='bx bx-check-double text-success'></i>
                    <small>10:03 AM</small>
                  </div>
                </div>
                <div class="user-avatar flex-shrink-0 ms-3">
                  <div class="avatar avatar-sm">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                  </div>
                </div>
              </div>
            </li>
            <li class="chat-message">
              <div class="d-flex overflow-hidden">
                <div class="user-avatar flex-shrink-0 me-3">
                  <div class="avatar avatar-sm">
                    <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle">
                  </div>
                </div>
                <div class="chat-message-wrapper flex-grow-1">
                  <div class="chat-message-text mt-2">
                    <p class="mb-0">Bien! ¿Qué te gustaría pedir?</p>
                  </div>
                  <div class="text-muted mt-1">
                    <small>10:05 AM</small>
                  </div>
                </div>
              </div>
            </li>
            <li class="chat-message chat-message-right">
              <div class="d-flex overflow-hidden">
                <div class="chat-message-wrapper flex-grow-1">
                  <div class="chat-message-text">
                    <p class="mb-0">1 Litro de helado por favor</p>
                  </div>
                  <div class="text-end text-muted mt-1">
                    <i class='bx bx-check-double text-success'></i>
                    <small>10:06 AM</small>
                  </div>
                </div>
                <div class="user-avatar flex-shrink-0 ms-3">
                  <div class="avatar avatar-sm">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                  </div>
                </div>
              </div>
            </li>
            <li class="chat-message">
              <div class="d-flex overflow-hidden">
                <div class="user-avatar flex-shrink-0 me-3">
                  <div class="avatar avatar-sm">
                    <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle">
                  </div>
                </div>
                <div class="chat-message-wrapper flex-grow-1">
                  <div class="chat-message-text">
                    <p class="mb-0">Excelente. ¿Qué variaciones?</p>
                  </div>
                  <div class="chat-message-text mt-2">
                    <p class="mb-0">Tenemos disponibles</p>
                    <p class="mb-0">Menta Granizada, Crema Tramontana, Dulce de Leche Granizado</p>
                  </div>
                  <div class="text-muted mt-1">
                    <small>10:08 AM</small>
                  </div>
                </div>
              </div>
            </li>
            <li class="chat-message chat-message-right">
              <div class="d-flex overflow-hidden">
                <div class="chat-message-wrapper flex-grow-1">
                  <div class="chat-message-text">
                    <p class="mb-0">Dulce de Leche Granizado y Crema Tramontana</p>
                  </div>
                  <div class="text-end text-muted mt-1">
                    <i class='bx bx-check-double text-success'></i>
                    <small>10:10 AM</small>
                  </div>
                </div>
                <div class="user-avatar flex-shrink-0 ms-3">
                  <div class="avatar avatar-sm">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                  </div>
                </div>
              </div>
            </li>
            <li class="chat-message">
              <div class="d-flex overflow-hidden">
                <div class="user-avatar flex-shrink-0 me-3">
                  <div class="avatar avatar-sm">
                    <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle">
                  </div>
                </div>
                <div class="chat-message-wrapper flex-grow-1">
                  <div class="chat-message-text">
                    <p class="mb-0">¿Podrías facilitarme tu dirección?</p>
                  </div>
                  <div class="text-muted mt-1">
                    <small>10:15 AM</small>
                  </div>
                </div>
              </div>
            </li>
            <li class="chat-message chat-message-right">
              <div class="d-flex overflow-hidden">
                <div class="chat-message-wrapper flex-grow-1 w-50">
                  <div class="chat-message-text">
                    <p class="mb-0">Si, Av. Rivera 1772</p>
                  </div>
                  <div class="text-end text-muted mt-1">
                    <i class='bx bx-check-double'></i>
                    <small>10:15 AM</small>
                  </div>
                </div>
                <div class="user-avatar flex-shrink-0 ms-3">
                  <div class="avatar avatar-sm">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle">
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>
        <!-- Chat message form -->
        <div class="chat-history-footer">
          <form class="form-send-message d-flex justify-content-between align-items-center ">
            <input class="form-control message-input border-0 me-3 shadow-none" placeholder="Escriba su mensaje aquí...">
            <div class="message-actions d-flex align-items-center">
              <i class="speech-to-text bx bx-microphone bx-sm cursor-pointer"></i>
              <label for="attach-doc" class="form-label mb-0">
                <i class="bx bx-paperclip bx-sm cursor-pointer mx-3 text-body"></i>
                <input type="file" id="attach-doc" hidden>
              </label>
              <button class="btn btn-primary d-flex send-msg-btn">
                <i class="bx bx-paper-plane me-md-1 me-0"></i>
                <span class="align-middle d-md-inline-block d-none">Enviar</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- /Chat History -->

    <!-- Sidebar Right -->
    <div class="col app-chat-sidebar-right app-sidebar overflow-hidden" id="app-chat-sidebar-right">
      <div class="sidebar-header d-flex flex-column justify-content-center align-items-center flex-wrap p-4 mt-2">
        <div class="avatar avatar-xl avatar-online">
          <img src="{{ asset('assets/img/avatars/2.png') }}" alt="Avatar" class="rounded-circle">
        </div>
        <h6 class="mt-3 mb-1">Felecia Rower</h6>
        <small class="text-muted">NextJS Developer</small>
        <i class="bx bx-x bx-sm cursor-pointer close-sidebar me-1 fs-4 d-block" data-bs-toggle="sidebar" data-overlay data-target="#app-chat-sidebar-right"></i>
      </div>
      <div class="sidebar-body px-4 pb-4">
        <div class="my-3">
          <span class="text-muted text-uppercase">About</span>
          <p class="mb-0 mt-2">A Next. js developer is a software developer who uses the Next. js framework alongside ReactJS to build web applications.</p>
        </div>
        <div class="my-4">
          <span class="text-muted text-uppercase">Personal Information</span>
          <ul class="list-unstyled d-grid gap-2 mt-2">
            <li class="d-flex align-items-center">
              <i class='bx bx-envelope'></i>
              <span class="align-middle ms-2">josephGreen@email.com</span>
            </li>
            <li class="d-flex align-items-center">
              <i class='bx bx-phone-call'></i>
              <span class="align-middle ms-2">+1(123) 456 - 7890</span>
            </li>
            <li class="d-flex align-items-center">
              <i class='bx bx-time-five'></i>
              <span class="align-middle ms-2">Mon - Fri 10AM - 8PM</span>
            </li>
          </ul>
        </div>
        <div class="mt-4">
          <span class="text-muted text-uppercase">Options</span>
          <ul class="list-unstyled d-grid gap-2 mt-2">
            <li class="cursor-pointer d-flex align-items-center">
              <i class='bx bx-bookmark'></i>
              <span class="align-middle ms-2">Add Tag</span>
            </li>
            <li class="cursor-pointer d-flex align-items-center">
              <i class='bx bx-star'></i>
              <span class="align-middle ms-2">Important Contact</span>
            </li>
            <li class="cursor-pointer d-flex align-items-center">
              <i class='bx bx-image-alt'></i>
              <span class="align-middle ms-2">Shared Media</span>
            </li>
            <li class="cursor-pointer d-flex align-items-center">
              <i class='bx bx-trash-alt'></i>
              <span class="align-middle ms-2">Delete Contact</span>
            </li>
            <li class="cursor-pointer d-flex align-items-center">
              <i class='bx bx-block'></i>
              <span class="align-middle ms-2">Block Contact</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
    <!-- /Sidebar Right -->

    <div class="app-overlay"></div>
  </div>
</div>
@endsection
