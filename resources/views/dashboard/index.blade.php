@extends('layouts.main')

@section('title', 'Ringkasan — ' . ($family->name ?? 'famespace') . ' — famespace')

@section('content')
  @include('modules.dashboard.overview')
@endsection
