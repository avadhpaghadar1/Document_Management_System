@extends('layouts.main')
@section('title','Groups')
@section('content')
<div class="container-fluid p-0">
    <div class="col-12 d-flex justify-content-between">
        <div class="d-flex">
            <h3 class="h3 mb-3">@yield('title')</h3>
            <p class="ps-2"><small>Manage groups</small></p>
        </div>
        <div>
            <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span><span><i class="" data-feather="chevron-right"></i></span><span>@yield('title')</span></p>
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
            <div class="card flex-fill">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Groups</h5>
                    <div class="d-flex flex-column align-items-end">
                        @can('create_group')
                        <x-button class="btn-primary mb-3"><a href="{{ route('add-group') }}" class="text-white"><i class="mb-1 me-1" data-feather="plus-square"></i>Add New Group</a></x-button>
                        @endcan
                        <form method="GET" class="d-flex">
                            <x-input-label for="search" value="search:" class="pt-2 pe-1"></x-input-label>
                            <x-input type="text" name="search" value="{{request('search')}}" placeholder="Search Name"></x-input>
                            <x-button name="submit" class="btn-outline-primary ms-1">Search</x-button>
                        </form>
                    </div>
                </div>
                <table class="table table-hover my-0">
                    <thead>
                        <tr>
                            <th><a href="{{ route('groups',['sort_by'=>'id','sort_order'=>$sort_order=='asc' && $sort_by =='id'?'desc':'asc','search'=>$search]) }}" class="text-dark">Id</a></th>
                            <th><a href="{{ route('groups',['sort_by'=>'name','sort_order'=>$sort_order=='asc' && $sort_by =='name'?'desc':'asc','search'=>$search]) }}" class="text-dark">Name</a></th>
                            <th class="d-none d-md-table-cell">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if($groups->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center">{{"No Groups Available"}}</td>
                        </tr>
                        @else
                        @foreach ($groups as $group)
                        <tr>
                            <td>{{ $group->id }}</td>
                            <td>{{$group->name}}</td>
                            <td class="d-none d-md-table-cell"><a href="{{route('view-group',['id'=>$group->id])}}"><i class="align-middle text-primary" data-feather="eye"></i></a>&nbsp;
                                @can('edit_group')
                                <a href="{{route('edit-group',['id'=>$group->id])}}"><i class="align-middle text-info" data-feather="edit"></i></a>&nbsp;
                                @endcan
                                @can('delete_group')
                                <i class="align-middle text-danger" data-bs-toggle="modal" data-bs-target="#deleteModel" onclick="setDeleteId('{{ $group->id }}')" data-feather="trash"></i>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                        @endif
                        <x-delete routeName="delete-group"></x-delete>
                    </tbody>
                </table>
                <div class="row mt-3 me-2">
                    <nav aria-label="Page navigation example d-flex col-12">
                        <ul class="pagination justify-content-end">
                            @if ($groups->previousPageUrl())
                            <li class="page-item">
                                <a class="page-link" href="{{ $groups->appends(request()->all())->previousPageUrl() }}" aria-label="Previous">Previous</a>
                            </li>
                            @endif

                            @php
                            $currentPage = $groups->currentPage();
                            $lastPage = $groups->lastPage();
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($lastPage, $currentPage + 2);
                            @endphp

                            @if ($startPage > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $groups->appends(request()->all())->url(1) }}">1</a>
                            </li>
                            @if ($startPage > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                            @endif
                            @endif

                            @for ($i = $startPage; $i <= $endPage; $i++) <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ $groups->appends(request()->all())->url($i) }}">{{ $i }}</a>
                                </li>
                                @endfor

                                @if ($endPage < $lastPage) @if ($endPage < $lastPage - 1) <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                    </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $groups->appends(request()->all())->url($lastPage) }}">{{ $lastPage }}</a>
                                    </li>
                                    @endif

                                    @if ($groups->nextPageUrl())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $groups->appends(request()->all())->nextPageUrl() }}" aria-label="Next">Next</a>
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