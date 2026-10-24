@extends('layouts.main')

@section('title', 'Ringkasan — ' . ($family->name ?? 'FamKoji') . ' — FamKoji')

@section('content')
  @include('modules.dashboard.overview')
@endsection
