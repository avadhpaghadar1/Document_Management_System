@extends('layouts.main')
@section('title','Audit Document')
@section('content')
<div class="container-fluid">
    <div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>Manage documents</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span>
                    <span><i data-feather="chevron-right"></i></span>
                    <span>Documents</span>
                    <span><i data-feather="chevron-right"></i></span>
                    <span>@yield('title')</span>
                </p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card flex-fill">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Documents Audit Logs</h5>
                    <form method="GET" class="d-flex  mt-3">
                        <x-input-label for="search" value="search:" class="pt-2 pe-1"></x-input-label>
                        <x-input type="text" name="search" value="{{request('search')}}"></x-input>
                        <x-button name="submit" class="btn-outline-primary ms-1">Search</x-button>
                    </form>
                </div>
                <table class="table table-hover my-0">
                    <thead>
                        <tr>
                            <th><a href="{{ route('document-audit',['sort_by'=>'id','sort_order'=>$sort_order=='asc' && $sort_by =='id'?'desc':'asc','search'=>$search]) }}" class="text-dark">Document Id</a></th>
                            <th class="d-none d-xl-table-cell"><a href="{{ route('document-audit',['sort_by'=>'document_type_id','sort_order'=>$sort_order=='asc' && $sort_by =='document_type_id'?'desc':'asc','search'=>$search]) }}" class="text-dark">Document Type</a></th>
                            <th class="d-none d-xl-table-cell"><a href="{{ route('document-audit',['sort_by'=>'user_id','sort_order'=>$sort_order=='asc' && $sort_by =='user_id'?'desc':'asc','search'=>$search]) }}" class="text-dark">Username</a></th>
                            <th><a href="{{ route('document-audit',['sort_by'=>'action','sort_order'=>$sort_order=='asc' && $sort_by =='action'?'desc':'asc','search'=>$search]) }}" class="text-dark">Action</a></th>
                            <th class="d-none d-md-table-cell"><a href="{{ route('document-audit',['sort_by'=>'updated_at','sort_order'=>$sort_order=='asc' && $sort_by =='updated_at'?'desc':'asc','search'=>$search]) }}" class="text-dark">Date</a></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($audits as $audit)
                        <tr>
                            <td>{{$audit->document_id}}</td>
                            <td class="d-none d-xl-table-cell">{{$audit->documentType->name ?? 'N/A'}}</td>
                            <td class="d-none d-xl-table-cell">{{ $audit->user->name ?? 'N/A' }}</td>
                            <td class="d-none d-xl-table-cell">{{$audit->action}}</td>
                            <td class="d-none d-md-table-cell">{{$audit->updated_at->format('Y-m-d') }}</td>
                        </tr>
                        @endforeach
                        <tr>
                            <td colspan="4"></td>
                            <td class="d-flex justify-content-end">
                                <nav aria-label="Page navigation example d-flex col-12">
                                    <ul class="pagination justify-content-end">
                                        @if ($audits->previousPageUrl())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $audits->appends(request()->all())->previousPageUrl() }}" aria-label="Previous">Previous</a>
                                        </li>
                                        @endif

                                        @php
                                        $currentPage = $audits->currentPage();
                                        $lastPage = $audits->lastPage();
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($lastPage, $currentPage + 2);
                                        @endphp

                                        @if ($startPage > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $audits->appends(request()->all())->url(1) }}">1</a>
                                        </li>
                                        @if ($startPage > 2)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                        @endif
                                        @endif

                                        @for ($i = $startPage; $i <= $endPage; $i++) <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $audits->appends(request()->all())->url($i) }}">{{ $i }}</a>
                                            </li>
                                            @endfor

                                            @if ($endPage < $lastPage) @if ($endPage < $lastPage - 1) <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                                </li>
                                                @endif
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $audits->appends(request()->all())->url($lastPage) }}">{{ $lastPage }}</a>
                                                </li>
                                                @endif

                                                @if ($audits->nextPageUrl())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $audits->appends(request()->all())->nextPageUrl() }}" aria-label="Next">Next</a>
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