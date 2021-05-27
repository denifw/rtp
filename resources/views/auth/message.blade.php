@extends('shared.auth')

@section('content')
    <form method="POST" action="" aria-label="{{ __('Login') }}">
        @csrf
        <h1>Success</h1>
        @if (empty($message) === false)
            <div class="alert alert-success">
                <button class="close" data-dismiss="alert"></button>
                {{ $message }}
            </div>
        @endif
        <div>
            <a class="btn btn-success" href="{{url('/login')}}">Login</a>
        </div>
    </form>
@endsection
