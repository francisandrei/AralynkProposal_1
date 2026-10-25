@extends('layouts.app')
@section('title', 'Jobs')

@section('content')
<div class="page-head"><div><h1>Jobs</h1><p>Hide or delete inappropriate posts and handle reports.</p></div></div>

@if (count($reports) > 0)
  <div class="card p-4 mb-4" style="border-left: 6px solid var(--clay)">
    <h2 class="section-title text-danger">Reported job posts</h2>
    @foreach ($reports as $report)
      <div class="border-top py-3">
        <strong>{{ $report->jobPost->title }}</strong> @if ($report->jobPost->is_hidden)<span class="badge text-bg-danger">Hidden</span>@endif
        <div class="small">Reason: {{ $report->reason }} <span class="text-muted">(reported by {{ $report->reporter->email }}, {{ $report->created_at->format('M j, g:i A') }})</span></div>
        <div class="d-flex flex-wrap gap-1 mt-2">
          <form method="POST" action="{{ route('admin.jobs.hide', $report->job_post_id) }}">@csrf<button class="btn btn-sm btn-danger">Hide post</button></form>
          <form method="POST" action="{{ route('admin.job-reports.close', $report->id) }}">@csrf<button name="status" value="resolved" class="btn btn-sm btn-outline-success">Mark as resolved</button></form>
          <form method="POST" action="{{ route('admin.job-reports.close', $report->id) }}">@csrf<button name="status" value="dismissed" class="btn btn-sm btn-outline-secondary">Dismiss</button></form>
        </div>
      </div>
    @endforeach
  </div>
@endif

<div class="card"><div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Job</th><th>Business</th><th>Category</th><th>Applications</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse ($jobs as $job)
        <tr>
          <td>{{ $job->title }}<div class="small text-muted">{{ $job->job_type }}, apply by {{ date('M j, Y', strtotime($job->deadline)) }}</div></td>
          <td>{{ $job->businessProfile->business_name }}</td>
          <td>{{ $job->category->name }}</td>
          <td>{{ $job->applications_count }}</td>
          <td>@include('partials.status', ['status' => $job->status]) @if ($job->is_hidden)<span class="badge text-bg-danger">Hidden</span>@endif</td>
          <td class="text-end">
            <div class="d-flex gap-1 justify-content-end">
              @if ($job->is_hidden)
                <form method="POST" action="{{ route('admin.jobs.unhide', $job->id) }}">@csrf<button class="btn btn-sm btn-outline-success">Show again</button></form>
              @else
                <form method="POST" action="{{ route('admin.jobs.hide', $job->id) }}">@csrf<button class="btn btn-sm btn-outline-warning" data-confirm="Hide this job from students?">Hide</button></form>
              @endif
              <form method="POST" action="{{ route('admin.jobs.destroy', $job->id) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" data-confirm="Delete this job permanently?">Delete</button></form>
            </div>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-4">No jobs yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div></div>
@endsection
