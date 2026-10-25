@extends('layouts.app')
@section('title', 'Ratings')

@section('content')
<div class="page-head"><div><h1>Ratings</h1><p>Remove abusive or false ratings and comments. Ratings never change who gets hired.</p></div></div>

<form class="d-flex gap-2 mb-3" method="GET" action="{{ route('admin.ratings') }}">
  <select name="max" class="form-select" style="max-width: 240px" aria-label="Show">
    <option value="">All ratings</option>
    @foreach ([1, 2, 3] as $n)
      <option value="{{ $n }}" {{ $max == $n ? 'selected' : '' }}>{{ $n }} {{ $n == 1 ? 'star' : 'stars' }} or less</option>
    @endforeach
  </select>
  <button class="btn btn-primary">Filter</button>
</form>

<div class="card"><div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Stars</th><th>From</th><th>About</th><th>Job</th><th>Comment</th><th></th></tr></thead>
    <tbody>
      @forelse ($ratings as $rating)
        <tr>
          <td>@include('partials.stars', ['value' => $rating->stars])</td>
          <td>{{ $rating->rater->name }}<div class="small text-muted">{{ $rating->rater_role }}</div></td>
          <td>{{ $rating->ratee->name }}</td>
          <td>{{ $rating->application->jobPost->title }}</td>
          <td class="small">{{ $rating->comment }}</td>
          <td class="text-end"><form method="POST" action="{{ route('admin.ratings.destroy', $rating->id) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" data-confirm="Remove this rating?">Remove</button></form></td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-4">No ratings yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div></div>
@endsection
