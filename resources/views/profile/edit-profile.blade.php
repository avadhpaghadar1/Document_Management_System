@extends('layouts.main')
@section('title','My Profile')
@section('content')
<div class="container-fluid">
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
                <div class="card-body">
                    <div class="m-sm-3">
                    
                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('patch')
                            <div class="border-bottom mb-3">
                                <h4 class="h4"><i class="align-middle me-1" data-feather="user"></i>My Profile</h4>
                            </div>
                            <div class="w-100 d-flex flex-column align-items-center">
                                <div class="mb-3 w-50">
                                    <x-input-label for="name" :value="__('Name')" />
                                    <x-input type="text" name="name" class="{{ $errors->has('name') ? 'is-invalid' : '' }}" :value="old('name',$user->name)" placeholder="Enter Name" />
                                    <x-input-error :messages="$errors->get('name')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-input type="email" name="email" class="{{ $errors->has('email') ? 'is-invalid' : '' }}" :value="old('email',$user->email)" placeholder="Enter Email" />
                                    <x-input-error :messages="$errors->get('email')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="country" :value="__('Country Code')" />
                                    <select class="form-control {{ $errors->has('country') ? 'is-invalid' : '' }}" id="country" name="country">
                                        <option value="2">None</option>
                                        @foreach($countryCodes as $countryCode)
                                        <option value="{{ $countryCode['code'] }}" {{$user->country==$countryCode['code']?"selected":""}}>{{ $countryCode['code'] }} {{ $countryCode['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('country')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="mobile" :value="__('Mobile Number')" />
                                    <x-input type="text" name="mobile" class="{{ $errors->has('mobile') ? 'is-invalid' : '' }}" :value="old('mobile',$user->mobile)" placeholder="Enter Mobile Number" />
                                    <x-input-error :messages="$errors->get('mobile')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="profile" :value="__('Profile Image')" />
                                    <x-input type="file" id="profile" name="image"   accept=".jpg, .jpeg, .png"/>
                                    @foreach ($profiles as $profile)
                                    <img src="{{ asset('storage/' . $profile->image) }}" class="mt-3 w-100 img-fluid" alt="No-Image"/>
                                    @endforeach
                                   
                                </div>
                                <div class="mt-3 w-100 d-flex justify-content-center">
                                    <x-button type="submit" class="btn-primary w-50"><i class="mb-1 me-1" data-feather="save"></i>Save</x-button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="m-sm-3">
                        <form method="POST" action="{{ route('change-password') }}">
                            @csrf
                            <div class="border-bottom mb-3">
                                <h4 class="h4"><i class="align-middle me-1" data-feather="lock"></i>Change Password</h4>
                            </div>
                            <div class="w-100 d-flex flex-column align-items-center">
                                
                                <div class="mb-3 w-50">
                                    <x-input-label for="password" :value="__('Password')" />
                                    <x-input type="password" name="password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Enter password" />
                                    <x-input-error :messages="$errors->get('password')" />
                                </div>
                                <div class="mb-3 w-50">
                                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                    <x-input type="password" name="password_confirmation" class="{{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}" placeholder="Enter Confirm Password" />
                                    <x-input-error :messages="$errors->get('password_confirmation')" />
                                </div>
                                <div class="mt-3 w-100 d-flex justify-content-center">
                                    <x-button class="btn-primary w-50"><i class="mb-1 me-1" data-feather="save"></i>Save</x-button>
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