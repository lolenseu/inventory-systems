@extends('layout')
@section('title', 'Login')

@section('content')

<link rel="stylesheet" href="{{ asset('css/login.css') }}">

<div class="form-container login">
  <h2>Login</h2>
  
    @if(session('success'))
        <p class="success">{{ session('success') }}</p>
    @endif

    @if(session('error'))
        <p class="error">{{ session('error') }}</p>
    @endif

  <form method="POST" action="{{ route('login') }}">
    @csrf
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
</div>
@endsection
