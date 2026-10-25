@extends('layouts.app')
@section('title', 'Applications')

@section('content')
<div class="page-head"><div><h1>Applications</h1><p>Monitoring only. The business decides who to hire.</p></div></div>

<form class="d-flex gap-2 mb-3" method="GET" action="{{ route('admin.applications') }}">
  <select name="status" class="form-select" style="max-width: 220px" aria-label="Status">
    <option value="">All statuses</option>
    @foreach (['Pending', 'Reviewed', 'Accepted', 'Rejected', 'Withdrawn', 'Completed'] as $option)
      <option {{ $status == $option ? 'selected' : '' }}>{{ $option }}</option>
    @endforeach
  </select>
  <button class="btn btn-primary">Filter</button>
</form>

<div class="card"><div class="table-responsive">
  <table class="table">
    <thead><tr><th>Date</th><th>Student</th><th>Job</th><th>Business</th><th>Status</th></tr></thead>
    <tbody>
      @forelse ($applications as $app)
        <tr>
          <td>{{ $app->created_at->format('M j, Y') }}</td>
          <td>{{ $app->student->full_name }}</td>
          <td>{{ $app->jobPost->title }}</td>
          <td>{{ $app->jobPost->businessProfile->business_name }}</td>
          <td>@include('partials.status', ['status' => $app->status])</td>
        </tr>
      @empty
        <tr><td colspan="5" class="text-center text-muted py-4">No applications.</td></tr>
      @endforelse
    </tbody>
  </table>
</div></div>
@endsection
