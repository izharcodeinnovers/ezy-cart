<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use App\Enums\PaymentMethod;
use App\Enums\OrderStatus;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function processPayment(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data) {
            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $this->generateTransactionId(),
                'payment_method' => $data['payment_method'] ?? $order->payment_method,
                'amount' => $order->grand_total,
                'status' => PaymentStatus::Processing,
                'gateway_response' => $data['gateway_response'] ?? null,
            ]);

            // Simulate payment processing
            $this->simulateGateway($payment);

            return $payment->fresh();
        });
    }

    protected function simulateGateway(Payment $payment): void
    {
        // In a real implementation, this would call the actual payment gateway
        // For now, we'll simulate success for cash on delivery and card payments
        $isSuccess = in_array($payment->payment_method, [
            PaymentMethod::CashOnDelivery->value,
            PaymentMethod::CreditCard->value,
            PaymentMethod::DebitCard->value,
        ]);

        if ($isSuccess) {
            $payment->update(['status' => PaymentStatus::Completed]);
            $payment->order->update([
                'payment_status' => PaymentStatus::Completed,
                'order_status' => OrderStatus::Confirmed,
            ]);
        } else {
            $payment->update(['status' => PaymentStatus::Failed]);
            $payment->order->update(['payment_status' => PaymentStatus::Failed]);
        }
    }

    public function refundPayment(Payment $payment, float $amount = null): Payment
    {
        return DB::transaction(function () use ($payment, $amount) {
            $amount = $amount ?? $payment->amount;

            if ($amount > $payment->amount) {
                throw new \Exception('Refund amount cannot exceed payment amount');
            }

            $payment->update([
                'status' => $amount === $payment->amount
                    ? PaymentStatus::Refunded
                    : PaymentStatus::PartiallyRefunded,
            ]);

            $payment->order->update([
                'payment_status' => $amount === $payment->amount
                    ? PaymentStatus::Refunded
                    : PaymentStatus::PartiallyRefunded,
            ]);

            if ($amount === $payment->amount) {
                $payment->order->update(['order_status' => OrderStatus::Refunded]);
            }

            return $payment->fresh();
        });
    }

    public function getPayments(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Payment::with('order.user');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('transaction_id', 'like', "%{$filters['search']}%")
                  ->orWhereHas('order', function ($oq) use ($filters) {
                      $oq->where('order_number', 'like', "%{$filters['search']}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    public function getPaymentStats(): array
    {
        return [
            'total' => Payment::count(),
            'completed' => Payment::where('status', PaymentStatus::Completed)->count(),
            'pending' => Payment::where('status', PaymentStatus::Pending)->count(),
            'failed' => Payment::where('status', PaymentStatus::Failed)->count(),
            'refunded' => Payment::where('status', PaymentStatus::Refunded)->count(),
            'total_revenue' => Payment::where('status', PaymentStatus::Completed)->sum('amount'),
            'total_refunded' => Payment::where('status', PaymentStatus::Refunded)->sum('amount'),
        ];
    }

    protected function generateTransactionId(): string
    {
        return 'TXN-' . now()->format('YmdHis') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
    }
}