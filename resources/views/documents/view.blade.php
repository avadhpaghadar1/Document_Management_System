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

                        <div class="col-12 mb-3 d-flex flex-wrap gap-2">
                            <a href="{{ route('document.versions', ['id' => $document->id]) }}" class="btn btn-outline-secondary btn-sm">
                                Versions ({{ $versionsCount ?? 0 }})
                            </a>

                            @if(!empty($canEdit) && in_array(($document->approval_status ?? 'draft'), ['draft', 'rejected']))
                                <form method="POST" action="{{ route('document.submit-approval', ['id' => $document->id]) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Submit for Approval</button>
                                </form>
                            @endif

                            @can('approve_document')
                                @if(($document->approval_status ?? 'draft') === 'pending')
                                    <form method="POST" action="{{ route('document.approve', ['id' => $document->id]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                    </form>

                                    <form method="POST" action="{{ route('document.reject', ['id' => $document->id]) }}" class="d-flex gap-2">
                                        @csrf
                                        <input name="rejected_reason" class="form-control form-control-sm" placeholder="Rejection reason" required maxlength="255" style="max-width: 260px;" />
                                        <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                @endif
                            @endcan
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

                            <h6 class="h6 mt-4">Approval Status</h6>
                            <p>
                                {{ $document->approval_status ?? 'draft' }}
                                @if(($document->approval_status ?? '') === 'approved' && !empty($document->approved_at))
                                    <small class="text-muted">({{ $document->approved_at }})</small>
                                @endif
                            </p>

                            @if(!empty($document->rejected_reason))
                                <h6 class="h6 mt-4 text-danger">Rejected Reason</h6>
                                <p class="text-danger">{{ $document->rejected_reason }}</p>
                            @endif
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

                                @php
                                    $analysis = $image['analysis'] ?? null;
                                @endphp

                                @if($analysis)
                                <div class="mt-2">
                                    @if(!empty($analysis->mime_type))
                                    <div class="d-flex">
                                        <p class="me-2">Type:</p>
                                        <p>{{ $analysis->mime_type }}</p>
                                    </div>
                                    @endif

                                    @if(!empty($analysis->pdf_page_count))
                                    <div class="d-flex">
                                        <p class="me-2">Pages:</p>
                                        <p>{{ $analysis->pdf_page_count }}</p>
                                    </div>
                                    @endif

                                    @if(!empty($analysis->image_width) && !empty($analysis->image_height))
                                    <div class="d-flex">
                                        <p class="me-2">Dimensions:</p>
                                        <p>{{ $analysis->image_width }} x {{ $analysis->image_height }}</p>
                                    </div>
                                    @endif

                                    @if(!empty($analysis->sha256))
                                    <div class="d-flex">
                                        <p class="me-2">SHA256:</p>
                                        <p title="{{ $analysis->sha256 }}">{{ substr($analysis->sha256, 0, 12) }}…</p>
                                    </div>
                                    @endif

                                    @if(!empty($analysis->ocr_engine))
                                    <div class="d-flex">
                                        <p class="me-2">OCR:</p>
                                        <p>{{ $analysis->ocr_engine }}{{ !empty($analysis->ocr_language) ? ' (' . $analysis->ocr_language . ')' : '' }}</p>
                                    </div>
                                    @endif

                                    @if(!empty($analysis->ocr_error))
                                    <div class="d-flex">
                                        <p class="me-2 text-danger">OCR Error:</p>
                                        <p class="text-danger">{{ $analysis->ocr_error }}</p>
                                    </div>
                                    @endif

                                    @if(!empty($analysis->ocr_text))
                                    <details class="mt-2">
                                        <summary class="text-primary" style="cursor:pointer;">Extracted Text</summary>
                                        <div class="mt-2" style="max-width: 520px; white-space: pre-wrap;">
                                            {{ \Illuminate\Support\Str::limit($analysis->ocr_text, 1500) }}
                                        </div>
                                    </details>
                                    @endif
                                </div>
                                @endif

                                @if($image['url'])
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('download', [ 'document' => $document->id, 'filename' => $image['name']]) }}" class="btn btn-primary">Download</a>

                                    @can('share_document')
                                    <form method="POST" action="{{ route('shared-links.create') }}" class="d-flex gap-2">
                                        @csrf
                                        <input type="hidden" name="document_id" value="{{ $document->id }}" />
                                        <input type="hidden" name="file_name" value="{{ $image['name'] }}" />
                                        <input type="number" name="expires_in_days" class="form-control form-control-sm" value="7" min="1" max="3650" style="max-width: 110px;" />
                                        <button class="btn btn-outline-primary btn-sm" type="submit">Create Share Link</button>
                                    </form>
                                    @endcan
                                </div>

                                @if(!empty($sharedLinks))
                                    @php
                                        $linksForFile = $sharedLinks->where('file_name', $image['name']);
                                    @endphp

                                    @if($linksForFile->count())
                                        <div class="mt-3" style="max-width: 520px;">
                                            <h6 class="h6">Shared Links</h6>
                                            @foreach($linksForFile as $link)
                                                <div class="d-flex justify-content-between align-items-center gap-2 border p-2 mb-2">
                                                    <div class="small" style="word-break: break-all;">
                                                        <div><a href="{{ route('shared-links.download', ['token' => $link->token]) }}" target="_blank">{{ route('shared-links.download', ['token' => $link->token]) }}</a></div>
                                                        @if(!empty($link->expires_at))
                                                            <div class="text-muted">Expires: {{ $link->expires_at }}</div>
                                                        @endif
                                                    </div>

                                                    @can('share_document')
                                                    <form method="POST" action="{{ route('shared-links.revoke', ['id' => $link->id]) }}">
                                                        @csrf
                                                        <button class="btn btn-outline-danger btn-sm" type="submit">Revoke</button>
                                                    </form>
                                                    @endcan
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                @endif
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