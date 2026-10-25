@extends('layouts.app')
@section('title', 'Admin dashboard')

@section('content')
<div class="page-head">
  <div><h1>Admin dashboard</h1><p>Manage accounts, jobs and ratings. The administrator never makes hiring decisions.</p></div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg"><div class="stat"><div class="icon"><i class="bi bi-mortarboard"></i></div><div><div class="num">{{ $students }}</div><div class="label">Students</div></div></div></div>
  <div class="col-6 col-lg"><div class="stat sky"><div class="icon"><i class="bi bi-shop"></i></div><div><div class="num">{{ $businesses }}</div><div class="label">Businesses</div></div></div></div>
  <div class="col-6 col-lg"><div class="stat gold"><div class="icon"><i class="bi bi-briefcase"></i></div><div><div class="num">{{ $jobs }}</div><div class="label">Jobs posted</div></div></div></div>
  <div class="col-6 col-lg"><div class="stat violet"><div class="icon"><i class="bi bi-file-earmark-text"></i></div><div><div class="num">{{ $applications }}</div><div class="label">Applications</div></div></div></div>
  <div class="col-6 col-lg"><div class="stat"><div class="icon"><i class="bi bi-star"></i></div><div><div class="num">{{ $ratings }}</div><div class="label">Ratings</div></div></div></div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6"><a class="text-decoration-none" href="{{ route('admin.businesses') }}"><div class="card p-3" style="border-left: 6px solid var(--gold)"><strong>{{ $pendingBusinesses }}</strong> {{ $pendingBusinesses == 1 ? 'business is' : 'businesses are' }} waiting for approval</div></a></div>
  <div class="col-md-6"><a class="text-decoration-none" href="{{ route('admin.jobs') }}"><div class="card p-3" style="border-left: 6px solid var(--clay)"><strong>{{ $openReports }}</strong> open job {{ $openReports == 1 ? 'report' : 'reports' }}</div></a></div>
</div>

<div class="card p-4">
  <h2 class="section-title">Recent activity</h2>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th></tr></thead>
      <tbody>
        @forelse ($logs as $log)
          <tr><td>{{ $log->created_at->format('M j, g:i A') }}</td><td>{{ $log->user->email ?? '-' }}</td><td>{{ $log->action }}</td><td>{{ $log->details }}</td></tr>
        @empty
          <tr><td colspan="4" class="text-muted">No activity yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
