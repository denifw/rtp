@extends('shared.auth')

@section('content')
    <form method="POST" action="{{ url('/password/reset') }}" aria-label="{{ __('Login') }}">
        <input type="hidden" name="token" value="{{ $token }}">
        @csrf
        <h1>Reset Password</h1>
        @if (count($errors) > 0)
            <div class="alert alert-error">
                <button class="close" data-dismiss="alert"></button>
                @foreach($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif
        <div>
            <input type="password" class="form-control" placeholder="Password" required=""
                   name="us_password" autocomplete="new-us_password"/>
        </div>
        <div>
            <input type="password" class="form-control" placeholder="Password Confirmation" required=""
                   name="us_password_confirmation"/>
        </div>
        <div>
            <button class="btn btn-success submit">Reset</button>
        </div>
    </form>
@endsection
