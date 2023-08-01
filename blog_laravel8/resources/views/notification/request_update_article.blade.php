@extends('layouts.email')
@section('pin')
<table border="0" cellpadding="10" cellspacing="0" class="paragraph_block block-3" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt; word-break: break-word;" width="100%">
    <tr>
        <td class="pad">
            <div style="color:#393d47;font-family:'Open Sans','Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;line-height:150%;text-align:center;mso-line-height-alt:21px;">
                <p style="margin: 0; word-break: break-word;">Hello!,this is response for your request article:</p>
            </div>
        </td>
    </tr>
</table>
<table border="0" cellpadding="10" cellspacing="0" class="button_block block-4" role="presentation" style="mso-table-lspace: 0pt; mso-table-rspace: 0pt;" width="100%">
    <tr>
        <td class="pad">
            <div align="center" class="alignment">
                <a href="www.example.com" style="text-decoration:none;display:inline-block;color:#ffffff;background-color:#eece5e;border-radius:13px;width:auto;border-top:0px solid #8a3b8f;font-weight:undefined;border-right:0px solid #8a3b8f;border-bottom:0px solid #8a3b8f;border-left:0px solid #8a3b8f;padding-top:5px;padding-bottom:5px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;font-size:16px;text-align:center;mso-border-alt:none;word-break:keep-all;" target="_blank">
                    <span style="padding-left:30px;padding-right:30px;font-size:16px;display:inline-block;letter-spacing:normal;">
                        <span style="word-break: break-word; line-height: 32px;"> {{ $article }}
                        </span>
                        <span style="padding-left:30px;padding-right:30px;font-size:16px;display:inline-block;letter-spacing:normal;">
                            <span style="word-break: break-word; line-height: 32px;">
                            </span>
                            <span style="padding-left:30px;padding-right:30px;font-size:16px;display:inline-block;letter-spacing:normal;">
                                <span style="word-break: break-word; line-height: 32px;"> 
                                </span>
                            </span>
                        </a>
                    </div>
        </td>
    </tr>
</table>
@endsection
