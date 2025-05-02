@extends('layouts.main')
@section('title','View User')
@section('content')
    <div class="container-fluid">
    <div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>Manage users</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span>
                <span><i data-feather="chevron-right"></i></span>
                <span>Users</span>
                <span><i data-feather="chevron-right"></i></span>
                <span>@yield('title')</span></p>
            </div>
        </div>
    </div>
        <div class="card mb-3">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col" class="w-25">Name</th>
                                    <th scope="col" class="w-25">Email</th>
                                    <th scope="col" class="w-25">Mobile</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $user->name}}</td>
                                    <td>{{ $user->email}}</td>
                                    <td>{{"+"}}{{ $user->country}}{{" "}}{{$user->mobile}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col" class="w-25">No.</th>
                                    <th scope="col" class="w-25">Permission</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($permissions as $index=> $permission)
                                <tr>
                                    <td>{{$index+1}}</td>
                                    <td>{{$permission->name}}</td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                        <p class="card-text"><small class="text-body-secondary">Last updated {{$user->updated_at}}</small></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection