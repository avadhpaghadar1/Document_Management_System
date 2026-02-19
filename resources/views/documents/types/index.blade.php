@extends('layouts.main')
@section('title','Document Type')
@section('content')
<div class="container-fluid p-0">
    <div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>Manage document Types</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span><span><i data-feather="chevron-right"></i></span><span>@yield('title')</span></p>
            </div>
        </div>
    </div>
    <div class="row ">
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
        <div class="col-12">
            <div class="card flex-fill">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Document Types</h5>
                    @can('create_document_type')
                    <x-button class="btn-primary"><a href="{{ route('add-document-type') }}" class="text-white"><i class="mb-1 me-1" data-feather="plus"></i>Add Document Type</a></x-button>
                    @endcan
                </div>
                <div class="w-100 d-flex justify-content-end my-2">
                    <form method="GET" class="d-flex">
                        <x-input-label for="search" class="me-1 mt-2" :value="__('Search:')" />
                        <x-input type="text" class="me-1" name="search" value="{{request('search')}}" placeholder="search"></x-input>
                        <x-button class="btn-outline-primary me-4">Search</x-button>
                    </form>
                </div>
                <table class="table table-hover my-0">
                    <thead>
                        <tr>
                            <th><a href="{{ route('document-type',['sort_by'=>'name','sort_order'=>$sort_order=='asc' && $sort_by =='name'?'desc':'asc','search'=>$search]) }}" class="text-dark">Document Type Name</a></th>
                            <th class="d-none d-md-table-cell">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if($documentTypes->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center">{{"No Document Type Available"}}</td>
                        </tr>
                        @else
                        @foreach ($documentTypes as $documentType)
                        <tr>
                            <td>{{$documentType->name}}</td>
                            <td class="d-none d-md-table-cell">
                                @can('edit_document_type')
                                <a href="{{route('edit-document-type',['id'=>$documentType->id])}}"><i class="text-info me-1" data-feather="edit"></i></a>
                                @endcan
                                @can('delete_document_type')
                                <i class="text-danger ms-1" data-bs-toggle="modal" data-bs-target="#deleteModel" onclick="setDeleteId('{{ $documentType->id }}')" data-feather="trash-2"></i>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                        @endif
                    </tbody>
                </table>
                <x-delete routeName="delete-document-type"></x-delete>
                <div class="row mt-3 me-2">
                    <nav aria-label="Page navigation example d-flex col-12">
                        <ul class="pagination justify-content-end">
                            @if ($documentTypes->previousPageUrl())
                            <li class="page-item">
                                <a class="page-link" href="{{ $documentTypes->appends(request()->all())->previousPageUrl() }}" aria-label="Previous">Previous</a>
                            </li>
                            @endif

                            @php
                            $currentPage = $documentTypes->currentPage();
                            $lastPage = $documentTypes->lastPage();
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($lastPage, $currentPage + 2);
                            @endphp

                            @if ($startPage > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $documentTypes->appends(request()->all())->url(1) }}">1</a>
                            </li>
                            @if ($startPage > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                            @endif
                            @endif

                            @for ($i = $startPage; $i <= $endPage; $i++) <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ $documentTypes->appends(request()->all())->url($i) }}">{{ $i }}</a>
                                </li>
                                @endfor

                                @if ($endPage < $lastPage) @if ($endPage < $lastPage - 1) <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                    </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $documentTypes->appends(request()->all())->url($lastPage) }}">{{ $lastPage }}</a>
                                    </li>
                                    @endif

                                    @if ($documentTypes->nextPageUrl())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $documentTypes->appends(request()->all())->nextPageUrl() }}" aria-label="Next">Next</a>
                                    </li>
                                    @endif
                        </ul>
                    </nav>

                </div>
            </div>
        </div>
    </div>

</div>
@endsection