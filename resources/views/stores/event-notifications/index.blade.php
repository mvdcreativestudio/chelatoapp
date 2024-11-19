@extends('layouts/layoutMaster')

@section('title', 'Configuraciones de Eventos por Tienda')

@section('vendor-script')
    @vite(['resources/assets/js/events-notifications/events-notifications-index.js'])
@endsection

@section('page-script')
<script type="text/javascript">
    window.eventToggleStatus = "{{ route('stores.events.toggle-status', ':id') }}";
    window.csrfToken = "{{ csrf_token() }}";
</script>
@vite(['resources/assets/js/app-stores-list.js'])
@endsection

@section('content')
<div class="container">
    <h2 class="mb-4">Configuraciones de Eventos para {{ $store->name }}</h2>

    <div class="row mb-4">
        <!-- Total de Eventos -->
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card animated-card card-border-shadow-info h-100 cursor-pointer">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-layer"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ $totalEvents }}</h4>
                    </div>
                    <p class="mb-1 fw-medium me-1">Total de Eventos</p>
                </div>
            </div>
        </div>

        <!-- Eventos Activos -->
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card animated-card card-border-shadow-success h-100 cursor-pointer">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ $activeEvents }}</h4>
                    </div>
                    <p class="mb-1 fw-medium me-1">Eventos Activos</p>
                </div>
            </div>
        </div>

        <!-- Eventos Inactivos -->
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card animated-card card-border-shadow-warning h-100 cursor-pointer">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ $inactiveEvents }}</h4>
                    </div>
                    <p class="mb-1 fw-medium me-1">Eventos Inactivos</p>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs mb-4" role="tablist">
        @foreach($eventConfigurations->groupBy(fn($config) => $config['event']->eventType->getTypeDescription()) as $eventType => $events)
            @php
                $firstEvent = $events->first();
                $eventTypeEnum = $firstEvent['event']->eventType->event_type_name;
            @endphp
            <li class="nav-item">
                <a class="nav-link @if ($loop->first) active @endif" data-bs-toggle="tab" href="#tab-{{ Str::slug($eventTypeEnum->value) }}" role="tab">
                    {{ $eventTypeEnum->getDescription() }}
                </a>
            </li>
        @endforeach
    </ul>

    <div class="tab-content">
        @foreach($eventConfigurations->groupBy('event.eventType.event_type_name') as $eventType => $events)
            <div class="tab-pane fade @if ($loop->first) show active @endif" id="tab-{{ Str::slug($eventType) }}" role="tabpanel">
                <div class="row">
                    @foreach($events as $config)
                        <div class="col-sm-6 col-lg-4 mb-4">
                            <div class="card h-100 card-border-shadow-primary animated-card">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar me-2">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="bx bx-bell"></i>
                                            </span>
                                        </div>
                                        <h5 class="mb-0">{{ $config['event']->getEventDescription() }}</h5>
                                    </div>
                                    <p class="mb-3 text-muted">{{ $config['event']->eventType->getTypeDescription() }}</p>
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input toggle-event-status" type="checkbox" 
                                               data-event-id="{{ $config['event']->id }}"
                                               data-store-id="{{ $store->id }}"
                                               @if($config['is_active']) checked @endif>
                                        <label class="form-check-label">
                                            {{ $config['is_active'] ? 'Activo' : 'Inactivo' }}
                                        </label>
                                    </div>
                                    @if($config['is_active'] && $config['email_recipient'])
                                        <p class="text-muted"><strong>Receptor de correo:</strong> {{ $config['email_recipient'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
    .tab-content{
        padding: 0;
    }

    .card-border-shadow-primary {
        border: 1px solid #7367f0;
    }

    .animated-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .animated-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .avatar-initial {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
</style>
@endsection
