@extends('layouts.main')
@section('title','Uploads')
@section('content')
<div class="container-fluid p-0">
    <div class="col-12 d-flex justify-content-between">
        <div class="d-flex">
            <h3 class="h3 mb-3">@yield('title')</h3>
            <p class="ps-2"><small>Upload PDFs/images for analysis</small></p>
        </div>
        <div>
            <p><i class="mb-1" data-feather="home"></i><span class="mx-1">Home</span><span><i data-feather="chevron-right"></i></span><span>@yield('title')</span></p>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if (session('success'))
            <div class="alert alert-success text-center">{{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger text-center">{{ session('error') }}</div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upload</h5>
                </div>
                <div class="card-body">
                    @if(isset($ocrAvailability) && (!$ocrAvailability['tesseract'] || (!$ocrAvailability['pdftotext'] && !$ocrAvailability['pdftoppm'])))
                        <div class="alert alert-warning">
                            <div><strong>OCR is not configured on this machine.</strong></div>
                            <div class="small">Install Tesseract (for images) and Poppler tools (pdftotext/pdftoppm for PDFs), or set absolute paths in <code>.env</code>.</div>
                            <div class="small mt-2">
                                <div><code>TESSERACT_CMD</code> (current: {{ $ocrAvailability['tesseract_cmd'] }})</div>
                                <div><code>PDFTOTEXT_CMD</code> (current: {{ $ocrAvailability['pdftotext_cmd'] }})</div>
                                <div><code>PDFTOPPM_CMD</code> (current: {{ $ocrAvailability['pdftoppm_cmd'] }})</div>
                                <div><code>OCR_LANGUAGE</code> (current: {{ $ocrAvailability['language'] }})</div>
                            </div>
                        </div>
                    @endif

                    <div class="dropzone" id="uploadsDropzone">
                        <div class="dz-message" data-dz-message>
                            <span>Drop files here or click to choose</span>
                        </div>
                    </div>
                    <div class="small mt-2 text-muted">Accepted: PDF, JPG, PNG. Max 20MB each.</div>
                    <div class="small mt-2 text-muted">OCR tools are optional; if not installed you will see an OCR error on the file.</div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between">
                    <h5 class="card-title mb-0">Your uploads</h5>
                    <div class="small text-muted">Use these in “Add Document” → Attachment</div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover my-0">
                        <thead>
                            <tr>
                                <th>File</th>
                                <th class="d-none d-xl-table-cell">Type</th>
                                <th class="d-none d-md-table-cell">Size</th>
                                <th class="d-none d-xl-table-cell">Details</th>
                                <th class="d-none d-xl-table-cell">OCR</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($uploads->isEmpty())
                            <tr>
                                <td colspan="6" class="text-center">No uploads yet.</td>
                            </tr>
                            @else
                            @foreach($uploads as $upload)
                            <tr>
                                <td>{{ $upload->file_name }}</td>
                                <td class="d-none d-xl-table-cell">{{ $upload->mime_type ?? '-' }}</td>
                                <td class="d-none d-md-table-cell">
                                    @if(!empty($upload->file_size))
                                    {{ round($upload->file_size / 1024, 2) }} KB
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    @if(!empty($upload->pdf_page_count))
                                    Pages: {{ $upload->pdf_page_count }}
                                    @elseif(!empty($upload->image_width) && !empty($upload->image_height))
                                    {{ $upload->image_width }} x {{ $upload->image_height }}
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    @if(!empty($upload->ocr_text))
                                    <span class="badge bg-success">text</span>
                                    @elseif(!empty($upload->ocr_error))
                                    <span class="badge bg-danger" title="{{ $upload->ocr_error }}">error</span>
                                    @else
                                    <span class="badge bg-secondary">n/a</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('uploads.download', $upload->id) }}" class="btn btn-sm btn-primary">Download</a>
                                    <button type="button" class="btn btn-sm btn-danger js-delete-upload" data-upload-id="{{ $upload->id }}">Delete</button>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $uploads->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
