@extends('shared.popup_base')
@section('title')
    Home
@endsection

@section('custom-style')
    <link href="{{mix('dist/css/detail.css')}}" rel="stylesheet">
@endsection
@section('menus')
@endsection

@section('content')
    <form>
        <div class="clearfix"></div>
        {{--Start Title Page--}}
        <div class="row">
            <div class="col-md-6 col-sm-12 col-xs-12">
                <h4>Detail Page</h4>
            </div>
            <div class="col-md-6 col-sm-12 col-xs-12">
                <div class="btn-group pull-right px-2">
                    <button type="button" class="btn btn-danger btn-sm">Actions</button>
                    <button type="button" class="btn btn-danger btn-sm dropdown-toggle" data-toggle="dropdown"
                            aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="#">Button 7</a></li>
                        <li><a href="#">Button 8</a></li>
                        <li><a href="#">Button 9</a></li>
                    </ul>
                </div>
                <button class="btn btn-primary pull-right btn-sm" type="button">Button 1</button>
                <button class="btn btn-success pull-right btn-sm" type="button">Button 2</button>
                <button class="btn btn-danger pull-right btn-sm" type="button">Button 3</button>
                <button class="btn btn-warning pull-right btn-sm" type="button">Button 4</button>
                <button class="btn btn-info pull-right btn-sm" type="button">Button 5</button>
                <button class="btn btn-default pull-right btn-sm" type="button">Button 6</button>
            </div>
        </div>
        <hr class="col-12 title-divider">
        {{--End Title Page--}}
        <div class="clearfix"></div>
        <div class="tabs-container" role="tabpanel" data-example-id="togglable-tabs">
            <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                <li role="presentation" class="active">
                    <a href="#tab_content1" id="home-tab" role="tab" data-toggle="tab" aria-expanded="true">General</a>
                </li>
            </ul>
            <div id="myTabContent" class="tab-content">
                <div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">
                    {{--Full screen form--}}
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="x_panel">
                                <div class="x_title">
                                    <h2>User Report
                                        <small>Activity report</small>
                                    </h2>
                                    <ul class="nav navbar-right panel_toolbox">
                                        <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                        </li>
                                        <li class="dropdown">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"
                                               aria-expanded="false"><i class="fa fa-wrench"></i></a>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#">Settings 1</a>
                                                </li>
                                                <li><a href="#">Settings 2</a>
                                                </li>
                                            </ul>
                                        </li>
                                        <li><a class="close-link"><i class="fa fa-close"></i></a>
                                        </li>
                                    </ul>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="x_content">
                                    <div class="col-md-3 col-sm-3 col-xs-12 profile_left">
                                        <div class="profile_img" style="text-align: center">
                                            {{--<div id="crop-avatar">--}}
                                                <!-- Current avatar -->
                                                <img style="text-align: center" class="img-responsive avatar-view" src="{{asset('images/profile.jpg')}}"
                                                     alt="Avatar" title="Change the avatar">
                                            {{--</div>--}}
                                        </div>
                                        <h4 class="text-center">Samuel Doe</h4>
                                        <h4 class="text-center">837937</h4>
                                        <!-- end of skills -->
                                    </div>
                                    <div class="col-md-4 col-sm-3 col-xs-12">
                                        <table border="1" width="100%">
                                            <tr>
                                                <td>Field</td>
                                                <td> : </td>
                                                <td>Test Value</td>
                                            </tr>
                                            <tr>
                                                <td>Field</td>
                                                <td> : </td>
                                                <td>Test Value</td>
                                            </tr>
                                            <tr>
                                                <td>Field</td>
                                                <td> : </td>
                                                <td>Test Value</td>
                                            </tr>
                                            <tr>
                                                <td>Field</td>
                                                <td> : </td>
                                                <td>Test Value</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-4 col-sm-3 col-xs-12">
                                        <table border="1" width="100%">
                                            <tr>
                                                <td>Field</td>
                                                <td> : </td>
                                                <td>Test Value</td>
                                            </tr>
                                            <tr>
                                                <td>Field</td>
                                                <td> : </td>
                                                <td>Test Value</td>
                                            </tr>
                                            <tr>
                                                <td>Field</td>
                                                <td> : </td>
                                                <td>Test Value</td>
                                            </tr>
                                            <tr>
                                                <td>Field</td>
                                                <td> : </td>
                                                <td>Test Value</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{--End Full screen form--}}
                </div>
            </div>
        </div>
    </form>
@endsection

@section('custom-script')
@endsection
