@extends('shared.auth')

@section('content')
    <form method="POST" action="{{ url('/login') }}" aria-label="{{ __('Login') }}">
        @csrf
        <h1>Login</h1>
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
        <div class="form-group bad">
            <input type="text" name="us_username" class="form-control" placeholder="Username"
                   value="{{$us_username ?? ''}}" required autofocus/>
        </div>
        <div>
            <input type="password" class="form-control" placeholder="Password" required=""
                   name="us_password"/>
        </div>
        <div>
            <button class="btn btn-success submit">Log in</button>
            <a class="reset_pass" href="{{url('/password/forgot')}}">Forgot Password</a>
        </div>
    </form>
@endsection
