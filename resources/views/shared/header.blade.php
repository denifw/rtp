<div class="top_nav">
    <div class="nav_menu">
        <nav>
            <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
            </div>

            <ul class="nav navbar-nav navbar-right">
                <li class="">
                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown"
                       aria-expanded="false">
                        {{--<img src="{{asset($profile_picture)}}" alt=""> {{$user['us_name']}}--}}
                        {{$user['us_name']}}
                        <span class=" fa fa-angle-down"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-usermenu pull-right">
                        {{--<li><a href="{{url('/view?page=Crm/Profile')}}"> Profile</a></li>--}}
                        {{--<li><a href="{{url('/view?page=Auth/ChangePassword')}}"> Change Password</a></li>--}}
                        {!! $switcher !!}
                        <li><a href="{{url('/changePassword')}}"><i
                                        class="fa fa-edit pull-right"></i> Change Password</a>
                        </li>
                        <li><a href="javascript:;" onclick="App.logoutSystem()"><i
                                        class="fa fa-sign-out pull-right"></i> Log Out</a>
                            <form id="logout_form" method="GET" action="{{url('/logout')}}"></form>
                        </li>
                    </ul>
                </li>

                <li role="presentation" class="dropdown" id="notif_element">
                    <a href="javascript:;" class="dropdown-toggle info-number" data-toggle="dropdown"
                       aria-expanded="false" id="notif_button">
                        <i class="fa fa-envelope-o"></i>
                        {{--<span class="badge bg-red">1</span>--}}
                    </a>
                    {{--<ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">--}}
                    {{--<li>--}}
                    {{--<a>--}}
                    {{--<span class="image">--}}
                    {{--<img src="{{mix('dist/images/img.jpg')}}" alt="Profile Image"/>--}}
                    {{--</span>--}}
                    {{--<span>--}}
                    {{--<span>{{$user['us_name']}}</span>--}}
                    {{--<span class="time">3 mins ago</span>--}}
                    {{--</span>--}}
                    {{--<span class="message">Film festivals used to be do-or-die moments for movie makers. They were where...</span>--}}
                    {{--</a>--}}
                    {{--</li>--}}
                    {{--<li>--}}
                    {{--<div class="text-center">--}}
                    {{--<a>--}}
                    {{--<strong>See All Alerts</strong>--}}
                    {{--<i class="fa fa-angle-right"></i>--}}
                    {{--</a>--}}
                    {{--</div>--}}
                    {{--</li>--}}
                    {{--</ul>--}}
                </li>
            </ul>
        </nav>
    </div>
</div>