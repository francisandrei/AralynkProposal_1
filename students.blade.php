@extends('layouts.app')
@section('title', 'Students')

@section('content')
<div class="page-head"><div><h1>Students</h1><p>Activate or deactivate student accounts.</p></div></div>

<form class="d-flex gap-2 mb-3" method="GET" action="{{ route('admin.students') }}">
  <input name="q" class="form-control" style="max-width: 340px" placeholder="Search name, email or student ID" value="{{ $search }}" aria-label="Search">
  <button class="btn btn-primary">Search</button>
</form>

<div class="card"><div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Name</th><th>Student ID</th><th>Program</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse ($students as $user)
        <tr>
          <td>{{ $user->studentProfile->full_name ?? $user->name }}<div class="small text-muted">{{ $user->email }}</div></td>
          <td>{{ $user->studentProfile->student_no ?? '' }}</td>
          <td>{{ $user->studentProfile->course->code ?? '' }}, year {{ $user->studentProfile->year_level ?? '' }}</td>
          <td>@include('partials.status', ['status' => $user->status])</td>
          <td class="text-end">
            <form method="POST" action="{{ route('admin.students.status', $user->id) }}">
              @csrf
              @if ($user->status == 'active')
                <button name="status" value="inactive" class="btn btn-sm btn-outline-danger" data-confirm="Deactivate this account?">Deactivate</button>
              @else
                <button name="status" value="active" class="btn btn-sm btn-outline-success">Activate</button>
              @endif
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">No students found.</td></tr>
      @endforelse
    </tbody>
  </table>
</div></div>
@endsection
