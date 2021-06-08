@extends('shared.auth')

@section('content')
    <div class="text-center text-center">
        <h1 class="error-number">Opps..</h1>
        <h2>{{\App\Frame\Formatter\Trans::getMessageWord('pageNotFound')}}</h2>
    </div>
@endsection
