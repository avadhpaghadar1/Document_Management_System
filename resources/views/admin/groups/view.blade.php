@extends('layouts.main')
@section('title','View Group')
@section('content')
<div class="container-fluid">
<div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>Manage groups</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span>
                <span><i data-feather="chevron-right"></i></span>
                <span>Groups</span>
                <span><i data-feather="chevron-right"></i></span>
                <span>@yield('title')</span></p>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <table class="table">
                        <h3 class="h3">Group Name: {{$group->name}}</h3>
                        <thead>
                            <tr>
                                <th scope="col" class="w-25">No.</th>
                                <th scope="col" class="w-25">Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                            <tr>
                                <td>{{ $loop->index + 1 }}</td>
                                <td>{{$user->name}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection