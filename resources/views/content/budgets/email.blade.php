@extends('layouts.email')

@section('header')
    Presupuesto #{{ $budget->id }}
@endsection

@section('content')
    <p>Estimado/a {{ $budget->client->name ?? $budget->lead->name ?? 'cliente' }},</p>
    
    <p>Adjunto encontrará el presupuesto #{{ $budget->id }}.</p>
    
    @if($budget->due_date)
        <p>Fecha de vencimiento: {{ date('d/m/Y', strtotime($budget->due_date)) }}</p>
    @endif
    
    <p>Total del presupuesto: ${{ number_format($budget->total, 2) }}</p>
    
    <p>Quedamos a su disposición para cualquier consulta.</p>
    
    <p>Saludos cordiales,<br></p>
@endsection