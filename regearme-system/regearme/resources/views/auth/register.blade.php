@extends('layout')
@section('title', 'Sign Up')

@section('content')

<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">

<div class="form-container register">
  <h2>Sign Up</h2>

  @if(session('success'))
    <p class="success">{{ session('success') }}</p>
  @endif

  @if($errors->any())
    <ul class="error">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  @endif

  <form method="POST" action="{{ route('register') }}" onsubmit="return validatePasswords()">
    @csrf
    <input type="text" name="in_game_name" placeholder="In-Game Name" required>
    <input type="text" name="player_id" placeholder="Player ID" required>
    <input type="password" name="password" id="password" placeholder="Password" required>
    <input type="password" name="password_confirmation" id="confirm_password" placeholder="Confirm Password" required>
    <button type="submit">Create Account</button>
  </form>
</div>

<script>
function validatePasswords() {
  const pass = document.getElementById('password').value;
  const confirm = document.getElementById('confirm_password').value;
  if (pass !== confirm) {
    alert("Passwords do not match!");
    return false;
  }
  return true;
}
</script>
@endsection
