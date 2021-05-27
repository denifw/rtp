@extends('shared.mail')

@section('content')
    <table class="email-body_inner" align="center" width="100%" cellpadding="0" cellspacing="0" style="color: #2B2B2B">
        <!-- Body content -->
        {{--Logo funda--}}
        <tr>
            <td>
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr style="background: #2a3f54">
                        <td style="padding: 10px;"><img style="float: left; max-height: 50px"
                                                        src="{!! $ss_logo !!}" alt="{!! $ss_name !!}">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="content-cell">
                {!! $mail_body!!}
            </td>
        </tr>
    </table>
@endsection
