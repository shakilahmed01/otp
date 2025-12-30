@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Initiate Payment') }}</div>

                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('payment.init') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="amount" class="form-label">{{ __('Amount') }} <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="1" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" required>
                            @error('amount')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="currency" class="form-label">{{ __('Currency') }}</label>
                            <select class="form-control @error('currency') is-invalid @enderror" id="currency" name="currency">
                                <option value="BDT" {{ old('currency', 'BDT') == 'BDT' ? 'selected' : '' }}>BDT - Bangladeshi Taka</option>
                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                            </select>
                            @error('currency')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="product_name" class="form-label">{{ __('Product/Service Name') }}</label>
                            <input type="text" class="form-control @error('product_name') is-invalid @enderror" id="product_name" name="product_name" value="{{ old('product_name') }}" placeholder="e.g., Product Purchase">
                            @error('product_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="product_category" class="form-label">{{ __('Product Category') }}</label>
                            <input type="text" class="form-control @error('product_category') is-invalid @enderror" id="product_category" name="product_category" value="{{ old('product_category') }}" placeholder="e.g., Electronics">
                            @error('product_category')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <hr>
                        <h5>{{ __('Customer Information') }}</h5>

                        <div class="mb-3">
                            <label for="cus_name" class="form-label">{{ __('Full Name') }}</label>
                            <input type="text" class="form-control @error('cus_name') is-invalid @enderror" id="cus_name" name="cus_name" value="{{ old('cus_name', Auth::user()->name) }}">
                            @error('cus_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cus_email" class="form-label">{{ __('Email Address') }}</label>
                            <input type="email" class="form-control @error('cus_email') is-invalid @enderror" id="cus_email" name="cus_email" value="{{ old('cus_email', Auth::user()->email) }}">
                            @error('cus_email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cus_phone" class="form-label">{{ __('Phone Number') }}</label>
                            <input type="text" class="form-control @error('cus_phone') is-invalid @enderror" id="cus_phone" name="cus_phone" value="{{ old('cus_phone') }}" placeholder="+8801XXXXXXXXX">
                            @error('cus_phone')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cus_address" class="form-label">{{ __('Address') }}</label>
                            <textarea class="form-control @error('cus_address') is-invalid @enderror" id="cus_address" name="cus_address" rows="2">{{ old('cus_address') }}</textarea>
                            @error('cus_address')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="cus_city" class="form-label">{{ __('City') }}</label>
                                <input type="text" class="form-control @error('cus_city') is-invalid @enderror" id="cus_city" name="cus_city" value="{{ old('cus_city') }}">
                                @error('cus_city')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="cus_postcode" class="form-label">{{ __('Postal Code') }}</label>
                                <input type="text" class="form-control @error('cus_postcode') is-invalid @enderror" id="cus_postcode" name="cus_postcode" value="{{ old('cus_postcode') }}">
                                @error('cus_postcode')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cus_country" class="form-label">{{ __('Country') }}</label>
                            <input type="text" class="form-control @error('cus_country') is-invalid @enderror" id="cus_country" name="cus_country" value="{{ old('cus_country', 'Bangladesh') }}">
                            @error('cus_country')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                {{ __('Proceed to Payment') }}
                            </button>
                            <a href="{{ route('payment.index') }}" class="btn btn-secondary">
                                {{ __('View Payment History') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

