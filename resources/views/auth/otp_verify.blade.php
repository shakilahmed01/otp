@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">OTP Verification</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('otp.verify') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="token" class="form-label">Verification Code</label>
                            <input id="token" type="text" class="form-control @error('token') is-invalid @enderror" name="token" value="{{ old('token') }}" required autofocus>

                            @error('token')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Verify</button>
                    </form>

                    <hr>

                    <form method="POST" action="{{ route('otp.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-link">Resend code</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
