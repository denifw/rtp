@extends('shared.base')
@section('title')
    {{ $page_title }}
@endsection

@section('custom-style')
    @if ($custom_style !== '')
        <link href="{{mix($custom_style)}}" rel="stylesheet">
    @endif
@endsection


@section('menus')
    {!! $content_menu !!}
@endsection

@section('content')
    {!! $content_body !!}
@endsection

@section('custom-script')
    @if ($custom_script !== '')
        <script src="{{mix($custom_script)}}"></script>
    @endif
@endsection
