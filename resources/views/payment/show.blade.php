@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    {{ __('Payment Details') }}
                    <a href="{{ route('payment.index') }}" class="btn btn-sm btn-secondary float-end">{{ __('Back to List') }}</a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning" role="alert">
                            {{ session('warning') }}
                        </div>
                    @endif

                    <table class="table table-bordered">
                        <tr>
                            <th width="40%">{{ __('Transaction ID') }}</th>
                            <td><strong>{{ $payment->tran_id }}</strong></td>
                        </tr>
                        <tr>
                            <th>{{ __('Amount') }}</th>
                            <td><strong>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</strong></td>
                        </tr>
                        <tr>
                            <th>{{ __('Status') }}</th>
                            <td>
                                @if($payment->status == 'success')
                                    <span class="badge bg-success">{{ __('Success') }}</span>
                                @elseif($payment->status == 'pending')
                                    <span class="badge bg-warning">{{ __('Pending') }}</span>
                                @elseif($payment->status == 'failed')
                                    <span class="badge bg-danger">{{ __('Failed') }}</span>
                                @elseif($payment->status == 'cancelled')
                                    <span class="badge bg-secondary">{{ __('Cancelled') }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>{{ __('Payment Method') }}</th>
                            <td>{{ $payment->payment_method ?? __('N/A') }}</td>
                        </tr>
                        @if($payment->bank_tran_id)
                        <tr>
                            <th>{{ __('Bank Transaction ID') }}</th>
                            <td>{{ $payment->bank_tran_id }}</td>
                        </tr>
                        @endif
                        @if($payment->card_type)
                        <tr>
                            <th>{{ __('Card Type') }}</th>
                            <td>{{ $payment->card_type }}</td>
                        </tr>
                        @endif
                        @if($payment->card_no)
                        <tr>
                            <th>{{ __('Card Number') }}</th>
                            <td>{{ $payment->card_no }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>{{ __('Product/Service') }}</th>
                            <td>{{ $payment->product_name ?? __('N/A') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Category') }}</th>
                            <td>{{ $payment->product_category ?? __('N/A') }}</td>
                        </tr>
                        <tr>
                            <th>{{ __('Created At') }}</th>
                            <td>{{ $payment->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @if($payment->paid_at)
                        <tr>
                            <th>{{ __('Paid At') }}</th>
                            <td>{{ $payment->paid_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                        @endif
                    </table>

                    @if($payment->cus_name || $payment->cus_email)
                    <h5 class="mt-4">{{ __('Customer Information') }}</h5>
                    <table class="table table-bordered">
                        @if($payment->cus_name)
                        <tr>
                            <th width="40%">{{ __('Name') }}</th>
                            <td>{{ $payment->cus_name }}</td>
                        </tr>
                        @endif
                        @if($payment->cus_email)
                        <tr>
                            <th>{{ __('Email') }}</th>
                            <td>{{ $payment->cus_email }}</td>
                        </tr>
                        @endif
                        @if($payment->cus_phone)
                        <tr>
                            <th>{{ __('Phone') }}</th>
                            <td>{{ $payment->cus_phone }}</td>
                        </tr>
                        @endif
                        @if($payment->cus_address)
                        <tr>
                            <th>{{ __('Address') }}</th>
                            <td>{{ $payment->cus_address }}</td>
                        </tr>
                        @endif
                        @if($payment->cus_city || $payment->cus_postcode)
                        <tr>
                            <th>{{ __('City/Postcode') }}</th>
                            <td>{{ $payment->cus_city }}{{ $payment->cus_postcode ? ', ' . $payment->cus_postcode : '' }}</td>
                        </tr>
                        @endif
                        @if($payment->cus_country)
                        <tr>
                            <th>{{ __('Country') }}</th>
                            <td>{{ $payment->cus_country }}</td>
                        </tr>
                        @endif
                    </table>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('payment.create') }}" class="btn btn-primary">{{ __('Make New Payment') }}</a>
                        <a href="{{ route('payment.index') }}" class="btn btn-secondary">{{ __('View All Payments') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

