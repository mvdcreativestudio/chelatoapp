@extends('layouts/layoutMaster')

@section('title', 'Modificar Horarios - ' . $store->name)

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header">
                    <h4 class="card-title">Modificar Horarios - {{ $store->name }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('stores.saveHours', ['store' => $store->id]) }}">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Hora Apertura</th>
                                        <th>Hora Cierre</th>
                                        <th>Abierto 24 hs</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'] as $day)
                                    @php
                                        $dayData = $storeHours[$day] ?? null;
                                    @endphp
                                    <tr>
                                        <td>{{ $day }}</td>
                                        <td>
                                          <input type="time" name="hours[{{ $day }}][open]" class="form-control" value="{{ $dayData->open ?? '' }}">
                                        </td>
                                        <td>
                                          <input type="time" name="hours[{{ $day }}][close]" class="form-control" value="{{ $dayData->close ?? '' }}">
                                        </td>
                                        <td>
                                          <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="hours[{{ $day }}][open_all_day]" value="1" id="open24-{{ $day }}" {{ $dayData && $dayData->open_all_day ? 'checked' : '' }}>
                                            <label class="form-check-label" for="open24-{{ $day }}">
                                                Sí
                                            </label>
                                          </div>

                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">Guardar Horarios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection