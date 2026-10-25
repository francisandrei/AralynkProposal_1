@extends('layouts.app')
@section('title', 'Log in')

@section('content')
<div class="auth-wrap">
  <div class="auth-side">
    <h2>Welcome back</h2>
    <p class="mb-0" style="color: rgba(255,255,255,.8)">Log in to see your matches, applications and messages.</p>
    <ul>
      <li><i class="bi bi-bullseye"></i> See why each job fits you</li>
      <li><i class="bi bi-calendar-check"></i> Keep your availability up to date</li>
      <li><i class="bi bi-star"></i> Rate the people you worked with</li>
    </ul>
  </div>
  <div class="auth-form">
    <h1 class="h3 mb-4">Log in to Aralynk</h1>
    <form method="POST" action="{{ route('login.submit') }}">
      @csrf
      <div class="mb-3"><label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus></div>
      <div class="mb-3"><label class="form-label" for="password">Password</label>
        <input id="password" type="password" name="password" class="form-control" required></div>
      <button class="btn btn-primary w-100 btn-lg">Log in</button>
    </form>
    <hr class="my-4">
    <p class="mb-1 small">New student? <a href="{{ route('register.student') }}">Create a student account</a></p>
    <p class="mb-0 small">Local business? <a href="{{ route('register.business') }}">Register your business</a></p>
  </div>
</div>
@endsection
