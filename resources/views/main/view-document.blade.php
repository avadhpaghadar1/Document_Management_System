@extends('layouts.main')
@section('title','View Document')
@section('content')
<div class="container-fluid p-0">
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
                <span>@yield('title')</span></p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="border-bottom mb-3">
                            <h4 class="h4">Document Data</h4>
                        </div>
                        <div class="col-6">
                            <h6 class="h6 mt-2">Document ID</h6>
                            <p>{{$document->id}}</p>
                            <h6 class="h6 mt-4">Document Type Name</h6>
                            <p>{{$document->documentType->name}}</p>
                            <h6 class="h6 mt-4">Note</h6>
                            <p>{{$document->note}}</p>
                            <h6 class="h6 mt-4">Expiration Date</h6>
                            <p>{{$document->expiry}}</p>
                        </div>
                        <div class="col-6">
                            @foreach ($fields as $field)
                            <div class="input-group my-3">
                                <span class="input-group-text">{{$field->field_name}}</span>
                                <x-input type="{{$field->field_type}}" :value="$field->field_value" disabled></x-input>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="border-bottom mb-3">
                            <h4 class="h4">Notification</h4>
                        </div>
                        <div class="col-6">
                            <h6 class="h6 mt-2">Notification Frequency</h6>
                            @foreach ($notifications as $index=> $notification)
                            @php
                            $expiryDate = \Carbon\Carbon::parse($document->expiry);
                            if ($notification->name === 'dayBefore') {
                            $newDate = $expiryDate->subDays($notification->day);
                            } elseif ($notification->name === 'dayAfter') {
                            $newDate = $expiryDate->addDays($notification->day);
                            }
                            @endphp
                            <div class="d-flex justify-content-between my-1">
                                <p class="h6 pt-1">
                                    @if($index+1==1)
                                    {{$num="1st"}}
                                    @elseif($index+1==2)
                                    {{$num="2nd"}}
                                    @elseif($index+1==3)
                                    {{$num="3rd"}}
                                    @else
                                    {{$num=$index+1 .'th'}}
                                    @endif
                                    Notification
                                </p>
                                <p class="ps-1"> {{$newDate->toDateString()}}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="border-bottom mb-3">
                            <h4 class="h4">Attachment</h4>
                        </div>
                        <div class="d-flex">
                            @foreach($images as $image)
                            <div class="mx-2 p-4 border">
                                @if($image['url'])
                                <p>{{ $image['name'] }}</p>
                                @else
                                <p class="my-2">{{ $image['name'] }}</p>
                                @endif
                                <div class="d-flex mt-2">
                                    <p>File Size:</p>
                                    <p>{{ round($image['size'] / 1024, 2) }} KB</p>
                                </div>
                                @if($image['url'])
                                <a href="{{ route('download', [ 'filename' => $image['name']]) }}" class="btn btn-primary">Download</a>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="border-bottom mb-3">
                            <h4 class="h4">Owner</h4>
                        </div>
                        <div>
                            <h6 class="h6 mt-2">Owners</h6>
                            @foreach ($owners as $owner)
                            <p>{{$owner->name}}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection