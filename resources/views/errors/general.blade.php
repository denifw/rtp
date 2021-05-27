@extends('shared.auth')

@section('content')
    <div class="text-center text-center">
        <h1 class="error-number">Opps..</h1>
        <h2>{!! $error_message !!}</h2>
        @if ($back_url !== null && empty($back_url) === false)
            <a href="{{ $back_url }}" class="btn btn-primary" style="text-decoration: none"> Back to Home</a>
        @else
            <a href="javascript:;" class="btn btn-primary" style="text-decoration: none" onclick="window.close()">
                Close</a>
        @endif
    </div>
@endsection
