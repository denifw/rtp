@extends('shared.mail')

@section('content')
    <table class="email-body_inner" align="center" width="570" cellpadding="0" cellspacing="0">
        <!-- Body content -->
        <tr>
            <td class="content-cell">
                <h1>Dear {{$receiver}},</h1>
                <p>{{$pre_header}}</p>
                <p> {{$mail_body}}</p>
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
