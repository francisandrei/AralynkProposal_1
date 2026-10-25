@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<div class="page-head"><div><h1>Reports and statistics</h1><p>A simple summary of how Aralynk is used.</p></div></div>

@php
  $cards = [
      ['Total students', $totalStudents], ['Total businesses', $totalBusinesses], ['Total job postings', $totalJobs],
      ['Open jobs', $activeJobs], ['Closed or completed jobs', $closedJobs], ['Total applications', $totalApps],
      ['Accepted applications', $acceptedApps], ['Rejected applications', $rejectedApps], ['Successful matches (completed)', $successful],
      ['Ratings given', $totalRatings],
  ];
@endphp
<div class="row g-3 mb-4">
  @foreach ($cards as $card)
    <div class="col-6 col-md-4 col-lg-3"><div class="stat"><div><div class="num">{{ $card[1] }}</div><div class="label">{{ $card[0] }}</div></div></div></div>
  @endforeach
  <div class="col-6 col-md-4 col-lg-3"><div class="stat gold"><div><div class="num">{{ $averageRating ? number_format($averageRating, 1) : '-' }}</div><div class="label">Average rating (of 5)</div></div></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-7"><div class="card p-4"><h2 class="section-title">Jobs by category</h2><canvas id="categoryChart" height="150"></canvas></div></div>
  <div class="col-lg-5"><div class="card p-4"><h2 class="section-title">Applications by status</h2><canvas id="statusChart" height="220"></canvas></div></div>
  <div class="col-12">
    <div class="card p-4">
      <h2 class="section-title">Most popular categories (by applications)</h2>
      <ol class="mb-0">
        @forelse ($popular as $row)
          <li>{{ $row->name }} <span class="text-muted">({{ $row->total }} applications)</span></li>
        @empty
          <li class="text-muted">No data yet.</li>
        @endforelse
      </ol>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
  new Chart(document.getElementById('categoryChart'), {
    type: 'bar',
    data: { labels: @json($categoryNames), datasets: [{ label: 'Jobs', data: @json($categoryTotals), backgroundColor: '#0E6B4F', borderRadius: 6 }] },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
  new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: { labels: @json($statusNames), datasets: [{ data: @json($statusTotals), backgroundColor: ['#94A39D', '#2A6FDB', '#0E6B4F', '#D64545', '#17201D', '#F4B400'] }] }
  });
</script>
@endpush
