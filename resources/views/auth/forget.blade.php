@extends('shared.auth')

@section('content')
    <form method="POST" action="{{ url('/password/forgot') }}" aria-label="{{ __('Login') }}">
        @csrf
        <h1>Forget Password</h1>
        @if (count($errors) > 0)
            <div class="alert alert-error">
                <button class="close" data-dismiss="alert"></button>
                @foreach($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif
        @if (empty($message) === false)
            <div class="alert alert-success">
                <button class="close" data-dismiss="alert"></button>
                {{ $message }}
            </div>
        @endif
        <div>
            <input type="text" class="form-control" placeholder="Username" required=""
                   name="us_username" autocomplete="new-us_username"/>
        </div>
        <div>
            <button class="btn btn-success submit">Reset Password</button>
            <a class="reset_pass" href="{{url('/login')}}">Login</a>
        </div>
    </form>
@endsection
