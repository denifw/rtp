@extends('shared.mail')

@section('content')
    <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0">
        <!-- Body content -->
        <tr>
            <td class="content-cell">
                <h1>Dear {{$receiver}},</h1>
                <p>Your request has been successfully created.</p>
                <p>{{$pre_header}}</p>
                <!-- Action -->
                <table class="body-action" align="center" width="100%" cellpadding="0"
                       cellspacing="0">
                    <tr>
                        <td align="center">
                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td>
                                                    <a href="{{url('/password/reset?token='.$token)}}"
                                                       class="button button--blue"
                                                       target="_blank">Reset Password</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <p class="sub">If you’re having trouble with the button above, copy
                    and
                    paste the URL below into your web browser.
                    <br>{{url('/password/reset?token='.$token)}}</p>
                <!-- Sub copy -->
                <table class="body-sub" width="100%">
                    <tr>
                        <td>
                            <p>Regards,
                                <br>{{$app_name}}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
@endsection
