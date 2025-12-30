@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>{{ __('Payment History') }}</span>
                    <a href="{{ route('payment.create') }}" class="btn btn-sm btn-primary">{{ __('Make New Payment') }}</a>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if($payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Transaction ID') }}</th>
                                        <th>{{ __('Amount') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $payment)
                                        <tr>
                                            <td><strong>{{ $payment->tran_id }}</strong></td>
                                            <td>{{ number_format($payment->amount, 2) }} {{ $payment->currency }}</td>
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
                                            <td>{{ \Illuminate\Support\Str::limit($payment->product_name ?? __('N/A'), 30) }}</td>
                                            <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                <a href="{{ route('payment.show', $payment->id) }}" class="btn btn-sm btn-info">
                                                    {{ __('View') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-center mt-3">
                            {{ $payments->links() }}
                        </div>
                    @else
                        <div class="alert alert-info">
                            {{ __('No payment records found.') }}
                        </div>
                        <div class="text-center">
                            <a href="{{ route('payment.create') }}" class="btn btn-primary">{{ __('Make Your First Payment') }}</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

