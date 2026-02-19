@extends('layouts.main')
@section('title','Settings')
@section('content')
<div class="container-fluid">
    <div class="col-12 d-flex justify-content-between">
        <div class="d-flex">
            <h3 class="h3 mb-3">@yield('title')</h3>
        </div>
        <div>
            <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span><span><i class="" data-feather="chevron-right"></i></span><span>@yield('title')</span></p>
        </div>
    </div>
    <div class="row">
        @if (session('success'))
        <div class="alert alert-success text-center">
            {{ session('success') }}
        </div>
        @endif
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="setting">
                        @csrf
                        <table id="table">
                            @foreach ($details as $index => $detail)
                            <tr>
                                <td>
                                    <x-input type="day" class="my-1 {{ $errors->has('inputs.' . $index . '.day') ? 'is-invalid' : '' }}" name="inputs[{{ $index }}][day]" value="{{$detail->day}}" placeholder="Enter days" />
                                    <x-input-error :messages="$errors->get('inputs.' . $index . '.day')" />
                                </td>
                                <td>
                                    @php
                                    $selectBefore = '';
                                    $selectAfter = '';

                                    if ($detail->name == "dayBefore") {
                                    $selectBefore = 'selected';
                                    } elseif ($detail->name == "dayAfter") {
                                    $selectAfter = 'selected';
                                    }
                                    @endphp
                                    <select class="form-select mx-2 m-1" name="inputs[{{ $index }}][name]">
                                        <option class="text-success" value="dayBefore" {{ $selectBefore }}>days before expiration/due date</option>
                                        <option class="text-danger" value="dayAfter" {{ $selectAfter }}>days after expiration/due date</option>
                                    </select>
                                </td>
                                <td>
                                    <x-button type="button" class="btn-danger mx-3 remove-table-row"><i class="align-middle" data-feather="trash-2"></i></x-button>
                                </td>
                            </tr>
                            @endforeach
                            @if (old('inputs'))
                            @foreach (old('inputs') as $key => $input)
                            @if (!isset($details[$key]))
                            <tr>
                                <td>
                                    <x-input type="text" class="my-1 {{ $errors->has('inputs.' . $key . '.day') ? 'is-invalid' : '' }}" name="inputs[{{ $key }}][day]" value="{{ old('inputs.' . $key . '.day') }}" placeholder="Enter days" required />
                                    <x-input-error :messages="$errors->get('inputs.' . $key . '.day')" />
                                </td>
                                <td>
                                    <select class="form-select mx-2 m-1" name="inputs[{{ $key }}][name]" required>
                                        <option class="text-success" value="dayBefore" {{ old('inputs.' . $key . '.name') == 'dayBefore' ? 'selected' : '' }}>days before expiration/due date</option>
                                        <option class="text-danger" value="dayAfter" {{ old('inputs.' . $key . '.name') == 'dayAfter' ? 'selected' : '' }}>days after expiration/due date</option>
                                    </select>
                                </td>
                                <td>
                                    <x-button type="button" class="btn-danger mx-3 remove-table-row"><i class="align-middle" data-feather="trash-2"></i></x-button>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                            @endif
                        </table>
                        <div class="d-block mt-3">
                            <x-button class="btn-info" type="button" id="add" name="add"><i class="align-middle" data-feather="plus"></i> Add Notification</x-button>
                        </div>
                        <div class="d-block mt-3 text-end">
                            <x-button class="btn-primary"><i class="align-middle" data-feather="save"></i> Save Setting</x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection