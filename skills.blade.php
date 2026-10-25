@extends('layouts.app')
@section('title', 'Skills')

@section('content')
<div class="page-head"><div><h1>Skills</h1><p>Students and businesses can type their own skills. Delete any that are wrong or inappropriate.</p></div></div>

<form method="POST" action="{{ route('admin.skills.store') }}" class="card p-3 mb-3 d-flex flex-row gap-2">
  @csrf
  <input name="name" class="form-control" placeholder="New skill" maxlength="60" aria-label="New skill" required>
  <button class="btn btn-primary text-nowrap">Add skill</button>
</form>

<div class="card"><div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Skill</th><th>Students</th><th>Jobs</th><th></th></tr></thead>
    <tbody>
      @foreach ($skills as $skill)
        <tr>
          <td>{{ $skill->name }}</td><td>{{ $skill->students_count }}</td><td>{{ $skill->job_posts_count }}</td>
          <td class="text-end"><form method="POST" action="{{ route('admin.skills.destroy', $skill->id) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" data-confirm="Delete this skill? It will be removed from students and jobs.">Delete</button></form></td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div></div>
@endsection
