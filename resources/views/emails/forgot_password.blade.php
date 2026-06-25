@extends('emails.layouts',['title'=>'Reset Password'])
@section('content')
<div style="background: rgba(0, 0, 0, 0.07); margin: 0 auto">
    <div class="box_content" style="
            background: white;
            width: 60%;
            height: 100%;
            padding: 2rem;
            margin: 0 auto;
        ">
        <div style="margin-bottom: 1.25rem">
            <a href="{{env('APP_URL')}}" target="_blank">
                <img src="{{ asset('assets/media/logos/logo.svg') }}" width="100" class="logo mb-4" alt="" />
            </a>
            <div style="text-align: center">
                <img src="{{ asset('assets/media/images/reset.png') }}" style="width: 10rem" alt="" />
            </div>
            <h1 style="
                    font-size: 1.125rem;
                    font-weight: 700;
                    margin-bottom: 1.25rem;
                    text-align: center;
                    margin-top: 1.25rem;
                ">
                Halo, {{$admin->name}}!
            </h1>
            <p style="font-size: 0.75rem; color: black; text-align:center">
                Kami menerima permintaan untuk mereset password akun Anda pada tanggal {{date('d/m/Y H:i')}} WIB.
                Password Anda telah di-reset secara otomatis menjadi password baru yang dibuat secara acak di bawah ini.
                Silakan gunakan password baru ini untuk masuk ke akun Anda. Demi keamanan, harap segera ubah password Anda setelah berhasil masuk.
            </p>
            <div style="text-align: center; margin-top: 1.75rem; margin-bottom: 1.75rem;">
                <div style="
                        display: inline-block;
                        padding: 0.75rem 2rem;
                        background-color: #f3f4f6;
                        border: 1px dashed #d1d5db;
                        font-family: monospace;
                        font-size: 1.125rem;
                        color: #1f2937;
                        border-radius: 0.5rem;
                        font-weight: 700;
                        letter-spacing: 0.05em;
                    ">{{ $new_password }}</div>
            </div>
        </div>
        <div>
            <div style="text-align: center; margin-top: 0.5rem" class="d-none">
                <a href="https://www.youtube.com/" target="_blank" style="text-decoration: none">
                    <img src="{{ asset('assets/media/images/yt.png') }}" width="40" alt="" />
                </a>
                <a href="https://www.facebook.com/" target="_blank" style="text-decoration: none">
                    <img src="{{ asset('assets/media/images/fb.png') }}" width="40" alt="" />
                </a>
                <a href="https://www.instagram.com/" target="_blank" style="text-decoration: none">
                    <img src="{{ asset('assets/media/images/ig.png') }}" width="40" alt="" />
                </a>
                <a href="https://www.twitter.com/" target="_blank" style="text-decoration: none">
                    <img src="{{ asset('assets/media/images/twt.png') }}" width="40" alt="" />
                </a>
            </div>
            <p style="
                    text-align: center;
                    margin-top: 0.25rem;
                    color: rgba(51, 51, 51, 0.5);
                    font-size: 0.75rem;
                ">
                Copyright © {{date('Y')}} {{env("APP_NAME")}}.
            </p>
        </div>
    </div>
</div>
@endsection
