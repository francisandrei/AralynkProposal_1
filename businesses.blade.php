@extends('layouts.app')
@section('title', 'Businesses')

@section('content')
<div class="page-head"><div><h1>Businesses</h1><p>Approve new businesses before they can log in and post jobs.</p></div></div>

<div class="card"><div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Business</th><th>Contact</th><th>Address</th><th>Jobs</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @forelse ($businesses as $user)
        @php $profile = $user->businessProfile; @endphp
        <tr>
          <td><strong>{{ $profile->business_name ?? $user->name }}</strong><div class="small text-muted">{{ $profile->category->name ?? '' }}, {{ $user->email }}</div></td>
          <td>{{ $profile->contact_person ?? '' }}<div class="small text-muted">{{ $profile->contact_no ?? '' }}</div></td>
          <td class="small">{{ $profile->address ?? '' }}</td>
          <td>{{ $profile ? $profile->jobPosts->count() : 0 }}</td>
          <td>@include('partials.status', ['status' => $user->status])</td>
          <td class="text-end">
            <form method="POST" action="{{ route('admin.businesses.status', $user->id) }}">
              @csrf
              @if ($user->status == 'pending')
                <button name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                <button name="action" value="reject" class="btn btn-sm btn-outline-danger" data-confirm="Reject this business?">Reject</button>
              @elseif ($user->status == 'active')
                <button name="action" value="deactivate" class="btn btn-sm btn-outline-danger" data-confirm="Deactivate this business?">Deactivate</button>
              @else
                <button name="action" value="activate" class="btn btn-sm btn-outline-success">Activate</button>
              @endif
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="6" class="text-center text-muted py-4">No businesses yet.</td></tr>
      @endforelse
    </tbody>
  </table>
</div></div>
@endsection
