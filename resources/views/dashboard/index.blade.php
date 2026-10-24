@extends('layouts.main')

@section('title', 'Ringkasan — ' . ($family->name ?? 'FamSpace') . ' — FamSpace')

@section('content')
  @include('modules.dashboard.overview')
@endsection
