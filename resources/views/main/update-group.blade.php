@extends('layouts.main')
@section('title','Update Group')
@section('content')
<div class="container-fluid">
<div class="row ">
        <div class="col-12 d-flex justify-content-between">
            <div class="d-flex">
                <h3 class="h3 mb-3">@yield('title')</h3>
                <p class="ps-2"><small>Manage groups</small></p>
            </div>
            <div>
                <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span><span>
                    <i data-feather="chevron-right"></i></span>
                    <span>Groups</span>
                    <span><i data-feather="chevron-right"></i></span>
                    <span>@yield('title')</span></p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="m-sm-3">
                        <form method="POST" action="{{ route('update-group',['id'=>$group->id]) }}">
                            @csrf
                            @method("PATCH")
                            <div class="border-bottom mb-3">
                                <h4 class="h4">Update Group</h4>
                            </div>
                            <div class="w-100 d-flex flex-column align-items-center">
                                <div class="mb-3 w-50">
                                    <x-input-label for="name" :value="__('Name')" />
                                    <x-input type="text" name="name" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" :value="old('name',$group->name)" placeholder="Enter Name" />
                                    <x-input-error :messages="$errors->get('name')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="name" :value="__('Name')" />
                                    <select class="form-control" id="select" name="users[]" multiple="multiple">
                                        <option>None</option>
                                        @foreach ($users as $user)
                                        <option value="{{$user->id}}" {{ $members->contains($user->id) ? 'selected' : '' }}>{{$user->name}}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('name')" />
                                </div>
                                <div class="mt-3 w-100 d-flex justify-content-center">
                                    <x-button class="btn-primary w-50"><i class="mb-1 me-1" data-feather="plus-square"></i>Update Group</x-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection