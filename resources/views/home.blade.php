@extends('layouts.app')

@section('content')

  <div class="container">
    <main-component :user-id="{{ $userId }}"></main-component>
  </div>
@endsection