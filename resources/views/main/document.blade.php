@extends('layouts.main')
@section('title','Documents')
@section('content')
<div class="container-fluid">
    <div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>Manage documents</small></p>
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
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Documents</h5>
                    <div>
                        <div class="d-flex justify-content-end">
                            @can('view_document_type')
                            <x-button class="btn-warning "><a href="{{ route('document-type') }}" class="text-white"><i class="mb-1 me-1" data-feather="file-text"></i>Document Type</a></x-button>
                            @endcan
                            <x-button class="btn-danger ms-1"><a href="{{ route('document-audit') }}" class="text-white"><i class="mb-1 me-1" data-feather="eye"></i>Document Audit</a></x-button>
                            <x-button class="btn-primary ms-1"><a href="{{ route('add-document') }}" class="text-white"><i class="mb-1 me-1" data-feather="file-plus"></i>Add New Document</a></x-button>
                        </div>
                        <div class="mt-2 ps-5 ms-3">
                            <div class="input-with-icon">
                                <x-input type="text" name="daterange" class="text-dark" placeholder="Filter by expiry date range" readonly></x-input>
                            </div>
                            <form id="dateRangeForm" method="GET" action="{{ route('document') }}">
                                <input type="hidden" id="start_date" name="start_date">
                                <input type="hidden" id="end_date" name="end_date">
                                <input type="hidden" name="daterange" id="daterange" />
                                <button type="submit" class="d-none">Submit</button>
                            </form>
                        </div>
                        <form method="GET" id="searchForm" class="d-flex mt-2 ps-5 ms-5">
                            <x-input-label for="search" value="search:" class="pt-2 pe-1"></x-input-label>
                            <x-input type="text" name="search" value="{{request('search')}}" placeholder="Search"></x-input>
                            <x-button name="submit" class="btn-outline-primary ms-1">Search</x-button>
                        </form>
                    </div>
                </div>
                <table class="table table-hover my-0">
                    <thead>
                        <tr>
                            <th><a href="{{ route('document',['sort_by'=>'id','sort_order'=>$sort_order=='asc' && $sort_by =='id'?'desc':'asc','search'=>$search]) }}" class="text-dark">Document Id</a></th>
                            <th class="d-none d-xl-table-cell"><a href="{{ route('document',['sort_by'=>'document_type_id','sort_order'=>$sort_order=='asc' && $sort_by =='document_type_id'?'desc':'asc','search'=>$search]) }}" class="text-dark">Document Type</a></th>
                            <th class="d-none d-xl-table-cell"><a href="{{ route('document',['sort_by'=>'expiry','sort_order'=>$sort_order=='asc' && $sort_by =='expiry'?'desc':'asc','search'=>$search]) }}" class="text-dark">Status</a></th>
                            <th><a href="{{ route('document',['sort_by'=>'expiry','sort_order'=>$sort_order=='asc' && $sort_by =='expiry'?'desc':'asc','search'=>$search]) }}" class="text-dark">Expiring</a></th>
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
                            <td class="d-none d-md-table-cell">{{$document->documentType->name}}</td>
                            @if($document->expiry>date('Y-m-d'))
                            <td><span class="badge bg-success">valid</span></td>
                            @else
                            <td><span class="badge bg-danger">expired</span></td>
                            @endif
                            <td class="d-none d-md-table-cell">{{$document->expiry}}</td>
                            <td class="d-none d-md-table-cell w-25"><a href="{{route('view-document',$document->id)}}">
                                    <i class="align-middle text-primary me-2" data-feather="eye"></i></a>
                                @if(
                                (isset($userPermissions[$document->id]['edit']) && $userPermissions[$document->id]['edit']) ||
                                (isset($groupPermissions[$document->id]['edit']) && $groupPermissions[$document->id]['edit'])
                                )
                                <a href="{{route('edit-document',['id'=>$document->id])}}">
                                    <i class="align-middle text-info me-2" data-feather="edit"></i></a>
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
                        <x-delete routeName="delete-document"></x-delete>
                        <tr>
                            <td><a href="{{route("export-document")}}"><x-button class="btn-primary d-flex justify-content-start "><i class="mt-1 me-2" data-feather="upload"></i>Export</x-button></a></td>
                            <td colspan="3"></td>
                            <td class="d-flex justify-content-end mt-2">
                                <nav aria-label="Page navigation example d-flex">
                                    <ul class="pagination justify-content-end">
                                        @if ($documents->previousPageUrl())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $documents->appends(request()->all())->previousPageUrl() }}" aria-label="Previous">Previous</a>
                                        </li>
                                        @endif

                                        @php
                                        $currentPage = $documents->currentPage();
                                        $lastPage = $documents->lastPage();
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($lastPage, $currentPage + 2);
                                        @endphp

                                        @if ($startPage > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $documents->appends(request()->all())->url(1) }}">1</a>
                                        </li>
                                        @if ($startPage > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                        @endif
                                        @endif

                                        @for ($i = $startPage; $i <= $endPage; $i++) <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $documents->appends(request()->all())->url($i) }}">{{ $i }}</a>
                                            </li>
                                            @endfor

                                            @if ($endPage < $lastPage) @if ($endPage < $lastPage - 1) <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                                </li>
                                                @endif
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $documents->appends(request()->all())->url($lastPage) }}">{{ $lastPage }}</a>
                                                </li>
                                                @endif

                                                @if ($documents->nextPageUrl())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $documents->appends(request()->all())->nextPageUrl() }}" aria-label="Next">Next</a>
                                                </li>
                                                @endif
                                    </ul>
                                </nav>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection