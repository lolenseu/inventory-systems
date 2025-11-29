@extends('layout')
@section('title', 'Login')

@section('content')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}">

<div class="form-container login">
  <h2>Login</h2>
  
  @if(session('success'))
    <div class="success">{{ session('success') }}</div>
  @endif
  
  @if(session('error'))
    <div class="error">{{ session('error') }}</div>
  @endif

  <form method="POST" action="{{ route('login') }}">
    @csrf
    
    <input type="text" name="in_game_name" placeholder="In-Game Name" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
</div>

<script src="{{ asset('js/auth/login.js') }}"></script>
@endsection
