@extends('layouts.main')
@section('title','Document Versions')
@section('content')
<div class="container-fluid">
    <div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>History</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span><span><i data-feather="chevron-right"></i></span><span>Documents</span><span><i data-feather="chevron-right"></i></span><span>@yield('title')</span></p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if (session('success'))
            <div class="alert alert-success text-center">
                {{ session('success') }}
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger text-center">
                {{ session('error') }}
            </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-0">Document #{{ $document->id }} ({{ $document->documentType->name ?? 'Unknown' }})</h5>
                        <div class="text-muted">Current expiry: {{ $document->expiry }}</div>
                    </div>
                    <div>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('view-document', ['id' => $document->id]) }}">Back to Document</a>
                    </div>
                </div>

                <div class="card-body">
                    @if($versions->isEmpty())
                        <div class="text-center">No versions found.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover my-0">
                                <thead>
                                    <tr>
                                        <th>Version</th>
                                        <th>Created At</th>
                                        <th>Created By</th>
                                        <th>Summary</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($versions as $v)
                                        @php
                                            $doc = $v->snapshot['document'] ?? [];
                                        @endphp
                                        <tr>
                                            <td>#{{ $v->version }}</td>
                                            <td>{{ $v->created_at }}</td>
                                            <td>{{ $v->creator->name ?? 'System' }}</td>
                                            <td>
                                                <div class="small">
                                                    <div>Status: {{ $doc['approval_status'] ?? 'draft' }}</div>
                                                    <div>Expiry: {{ $doc['expiry'] ?? '' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                @can('restore_document_version')
                                                    <form method="POST" action="{{ route('document-version.restore', ['id' => $v->id]) }}">
                                                        @csrf
                                                        <button class="btn btn-outline-primary btn-sm" type="submit">Restore</button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
