@extends('layouts.main')
@section('title','Dashboard')
@section('content')
<div class="container-fluid p-0">
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
    <div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>At-a-glance</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span><span><i class="" data-feather="chevron-right"></i></span><span>@yield('title')</span></p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-3">
            <a class="text-decoration-none" href="{{ route('document', ['start_date' => $today, 'end_date' => $in7, 'status' => 'all']) }}">
            <div class="card bg-primary ">
                <div class="card-body">
                    <h1 class="mt-1 mb-3 text-dark">{{$expiring7}}</h1>
                    <div class="mb-0 ">
                        <span class="text-dark">Expiring in 7 Days</span>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <div class="col-3">
            <a class="text-decoration-none" href="{{ route('document', ['start_date' => $today, 'end_date' => $in30, 'status' => 'all']) }}">
            <div class="card bg-info">
                <div class="card-body">
                    <h1 class="mt-1 mb-3 text-dark">{{$expiring30}}</h1>
                    <div class="mb-0">
                        <span class="text-dark">Expiring in 30 Days</span>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <div class="col-3">
            <a class="text-decoration-none" href="{{ route('document', ['status' => 'expired']) }}">
            <div class="card  bg-danger">
                <div class="card-body">
                    <h1 class="mt-1 mb-3 text-dark">{{$expired}}</h1>
                    <div class="mb-0">
                        <span class="text-dark">Expired</span>
                    </div>
                </div>
            </div>
            </a>
        </div>
        <div class="col-3">
            <div class="card bg-warning">
                <div class="card-body">
                    <h1 class="mt-1 mb-3 text-dark">{{$total}}</h1>
                    <div class="mb-0">
                        <span class="text-dark">Total Document</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-4">
            <a class="text-decoration-none" href="{{ route('document', ['start_date' => $today, 'end_date' => $in90, 'status' => 'all']) }}">
                <div class="card">
                    <div class="card-body">
                        <h3 class="mb-0">{{ $expiring90 }}</h3>
                        <div class="text-muted">Expiring in 90 Days</div>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-lg-12 col-xxl-12 d-flex">
            <div class="card flex-fill">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Quick view</h5>
                    <x-button class="btn-primary"><a href="{{ route('add-document') }}" class="text-white"><i class="mb-1 me-1" data-feather="file-plus"></i>Add Document</a></x-button>
                </div>
                <table class="table table-hover my-0">
                    <thead>
                        <tr>
                            <th>Document Id</th>
                            <th class="d-none d-xl-table-cell">Document Type</th>
                            <th class="d-none d-xl-table-cell">Status</th>
                            <th>Expiring</th>
                            <th class="d-none d-md-table-cell">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($documents->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center">{{"No Documents Available"}}</td>
                        </tr>
                        @else
                        @foreach ($documents as $document)
                        <tr>
                            <td>{{$document->id}}</td>
                            <td class="d-none d-xl-table-cell">{{$document->documentType->name}}</td>
                            @if($document->expiry>date('Y-m-d'))
                            <td><span class="badge bg-success">valid</span></td>
                            @else
                            <td><span class="badge bg-danger">expired</span></td>
                            @endif
                            <td class="d-none d-xl-table-cell">{{$document->expiry}}</td>
                            <td class="d-none d-md-table-cell"><a href="{{route('view-document',$document->id)}}"><i class="align-middle text-primary me-2" data-feather="eye"></i></a>
                                @if(
                                (isset($userPermissions[$document->id]['edit']) && $userPermissions[$document->id]['edit']) ||
                                (isset($groupPermissions[$document->id]['edit']) && $groupPermissions[$document->id]['edit'])
                                )
                                <a href="{{route('edit-document',['id'=>$document->id])}}"><i class="align-middle text-info me-2" data-feather="edit"></i></a>
                                @endif
                                @if(
                                (isset($userPermissions[$document->id]['delete']) && $userPermissions[$document->id]['delete']) ||
                                (isset($groupPermissions[$document->id]['delete']) && $groupPermissions[$document->id]['delete'])
                                )
                                <i class="align-middle text-danger" data-bs-toggle="modal" data-bs-target="#deleteModel" onclick="setDeleteId('{{ $document->id }}')" data-feather="trash-2"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        @endif
                        <tr>
                            <td colspan="4"></td>
                            <td class="d-flex justify-content-end"><x-button class="btn-primary"><a href="{{ route('document') }}" class="text-white">View All</a></x-button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection