@extends('layouts.main')
@section('title','Users')
@section('content')
<div class="container-fluid p-0">
    <div class="col-12 d-flex justify-content-between">
        <div class="d-flex">
            <h3 class="h3 mb-3">@yield('title')</h3>
            <p class="ps-2"><small>Manage users</small></p>
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
                    <h5 class="card-title mb-0">Users</h5>
                    <div class="d-flex flex-column align-items-end">
                        @can('add_user')
                        <x-button class="btn-primary mb-4"><a href="{{ route('add-user') }}" class="text-white"><i class="mb-1 me-1" data-feather="user-plus"></i>Add User</a></x-button>
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
                            <th>
                                <a href="{{ route('users',['sort_by'=>'id','sort_order'=>$sort_order=='asc' && $sort_by =='id'?'desc':'asc','search'=>$search]) }}" class="text-dark">
                                    User Id
                                </a>
                            </th>
                            <th class="d-none d-xl-table-cell"> <a href="{{ route('users',['sort_by'=>'name','sort_order'=>$sort_order=='asc' && $sort_by =='name'?'desc':'asc','search'=>$search]) }}" class="text-dark">Name</a></th>
                            <th class="d-none d-xl-table-cell"> <a href="{{ route('users',['sort_by'=>'role','sort_order'=>$sort_order=='asc' && $sort_by =='role'?'desc':'asc','search'=>$search]) }}" class="text-dark">Role</a></th>
                            <th> <a href="{{ route('users',['sort_by'=>'email','sort_order'=>$sort_order=='asc' && $sort_by =='email'?'desc':'asc','search'=>$search]) }}" class="text-dark">Email</a></th>
                            <th> <a href="{{ route('users',['sort_by'=>'mobile','sort_order'=>$sort_order=='asc' && $sort_by =='mobile'?'desc':'asc','search'=>$search]) }}" class="text-dark">Mobile</a></th>
                            <th class="d-none d-md-table-cell">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($users->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center">{{"No Users Available"}}</td>
                        </tr>
                        @else
                        @foreach ($users as $user)
                        <tr>
                            <td>{{$user->id}}</td>
                            <td class="d-none d-xl-table-cell">{{$user->name}}</td>
                            <td class="d-none d-xl-table-cell">{{ $user->role ?? 'viewer' }}</td>
                            <td class="d-none d-xl-table-cell">{{$user->email}}</td>
                            <td class="d-none d-md-table-cell">+{{$user->country}}&nbsp;{{ $user->mobile }}</td>
                            <td class="d-none d-md-table-cell"><a href="{{route('view-user',['id'=>$user->id])}}"><i class="align-middle text-primary" data-feather="eye"></i></a>&nbsp;
                                @can('edit_user')
                                <a href="{{route('edit-user',['id'=>$user->id])}}"><i class="align-middle text-info" data-feather="edit"></i></a>&nbsp;
                                @endcan
                                @can('delete_user')
                                <i class="align-middle text-danger" data-bs-toggle="modal" data-bs-target="#deleteModel" onclick="setDeleteId('{{ $user->id }}')" data-feather="trash"></i>
                            </td>
                            @endcan
                        </tr>
                        @endforeach
                        @endif
                        <x-delete routeName="delete-user"></x-delete>
                    </tbody>
                </table>
                <div class="row mt-3 me-2">
                    <nav aria-label="Page navigation example d-flex col-12">
                        <ul class="pagination justify-content-end">
                            @if ($users->previousPageUrl())
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->appends(request()->all())->previousPageUrl() }}" aria-label="Previous">Previous</a>
                            </li>
                            @endif

                            @php
                            $currentPage = $users->currentPage();
                            $lastPage = $users->lastPage();
                            $startPage = max(1, $currentPage - 2);
                            $endPage = min($lastPage, $currentPage + 2);
                            @endphp

                            @if ($startPage > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ $users->appends(request()->all())->url(1) }}">1</a>
                            </li>
                            @if ($startPage > 2)
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                            @endif
                            @endif

                            @for ($i = $startPage; $i <= $endPage; $i++) <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                <a class="page-link" href="{{ $users->appends(request()->all())->url($i) }}">{{ $i }}</a>
                                </li>
                                @endfor

                                @if ($endPage < $lastPage) @if ($endPage < $lastPage - 1) <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                    </li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->appends(request()->all())->url($lastPage) }}">{{ $lastPage }}</a>
                                    </li>
                                    @endif

                                    @if ($users->nextPageUrl())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $users->appends(request()->all())->nextPageUrl() }}" aria-label="Next">Next</a>
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