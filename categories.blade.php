@extends('layouts.app')
@section('title', 'Categories')

@section('content')
<div class="page-head"><div><h1>Job categories</h1><p>Add, rename, hide or delete categories.</p></div></div>

<form method="POST" action="{{ route('admin.categories.store') }}" class="card p-3 mb-3 d-flex flex-row gap-2">
  @csrf
  <input name="name" class="form-control" placeholder="New category name" maxlength="80" aria-label="New category" required>
  <button class="btn btn-primary text-nowrap">Add category</button>
</form>

<div class="card"><div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>Name</th><th>Jobs</th><th>Status</th><th></th></tr></thead>
    <tbody>
      @foreach ($categories as $category)
        <tr>
          <td>
            <form method="POST" action="{{ route('admin.categories.update', $category->id) }}" class="d-flex gap-2">
              @csrf @method('PUT')
              <input name="name" class="form-control form-control-sm" value="{{ $category->name }}" maxlength="80" aria-label="Category name">
              <button class="btn btn-sm btn-outline-primary">Rename</button>
            </form>
          </td>
          <td>{{ $jobCounts[$category->id] ?? 0 }}</td>
          <td>@include('partials.status', ['status' => $category->is_active ? 'active' : 'inactive'])</td>
          <td class="text-end">
            <div class="d-flex gap-1 justify-content-end">
              <form method="POST" action="{{ route('admin.categories.toggle', $category->id) }}">@csrf<button class="btn btn-sm btn-outline-secondary">{{ $category->is_active ? 'Deactivate' : 'Activate' }}</button></form>
              <form method="POST" action="{{ route('admin.categories.destroy', $category->id) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" data-confirm="Delete this category?">Delete</button></form>
            </div>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div></div>
@endsection
