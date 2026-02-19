@extends('layouts.main')
@section('title','Recycle Bin')
@section('content')
<div class="container-fluid">
    <div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>Deleted documents</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span><span><i data-feather="chevron-right"></i></span><span>@yield('title')</span></p>
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
                    <h5 class="card-title mb-0">Recycle Bin</h5>
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('document') }}">Back to Documents</a>
                </div>

                <div class="card-body">
                    @if($documents->isEmpty())
                        <div class="text-center">No deleted documents.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover my-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th>Expiry</th>
                                        <th>Deleted At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $doc)
                                        <tr>
                                            <td>{{ $doc->id }}</td>
                                            <td>{{ $doc->documentType->name ?? '' }}</td>
                                            <td>{{ $doc->expiry }}</td>
                                            <td>{{ $doc->deleted_at }}</td>
                                            <td class="d-flex gap-2">
                                                @can('restore_document')
                                                <form method="POST" action="{{ route('recycle-bin.restore', ['id' => $doc->id]) }}">
                                                    @csrf
                                                    <button class="btn btn-outline-primary btn-sm" type="submit">Restore</button>
                                                </form>
                                                @endcan

                                                @can('force_delete_document')
                                                <form method="POST" action="{{ route('recycle-bin.force-delete', ['id' => $doc->id]) }}" onsubmit="return confirm('Permanently delete this document?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm" type="submit">Delete Permanently</button>
                                                </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $documents->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
