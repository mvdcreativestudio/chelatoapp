<?php

namespace App\Repositories;

use App\Models\Transaction;

class TransactionRepository
{
    public function getAllTransactions()
    {
        return Transaction::orderBy('created_at', 'desc')->get();
    }

    public function getFormattedTransactions()
    {
        $transactions = $this->getAllTransactions();

        return $transactions->map(function ($transaction) {
            return [
                'id' => $transaction->TransactionId,
                'order_link' => $this->getOrderLink($transaction),
                'order_id' => $transaction->order ? $transaction->order->id : 'N/A',
                'type' => $this->getTransactionType($transaction->type),
                'type_icon' => $this->getTransactionTypeIcon($transaction->type),
                'status' => $this->getTransactionStatus($transaction->status),
                'status_icon' => $this->getStatusIcon($transaction->status),
                'status_class' => $this->getStatusBadgeClass($transaction->status),
                'row_class' => $this->getRowClass($transaction->status),
                'created_at' => $transaction->created_at->format('d/m/Y H:i'),
            ];
        });
    }

    protected function getOrderId($transaction)
    {
        if (!$transaction->order) {
            return null;
        }

        return $transaction->order->id;
    }

    protected function getOrderLink($transaction)
    {
        if (!$transaction->order) {
            return null;
        }

        return url('/admin/orders/' . $transaction->order->uuid);
    }

    protected function getTransactionType($type)
    {
        $types = [
            'sale' => 'Venta',
            'refund' => 'Devolución',
            'cancellation' => 'Cancelación',
            'void' => 'Anulación',
        ];

        return $types[$type] ?? 'Desconocido';
    }

    protected function getTransactionTypeIcon($type)
    {
        $icons = [
            'sale' => 'bx-cart-alt',
            'refund' => 'bx-undo',
            'cancellation' => 'bx-x-circle',
            'void' => 'bx-trash',
        ];

        return $icons[$type] ?? 'bx-help-circle';
    }

    protected function getTransactionStatus($status)
    {
        $statuses = [
            'pending' => 'Pendiente',
            'void_request' => 'Anulación Pendiente',
            'voided' => 'Anulación',
            'failed' => 'Fallida',
            'completed' => 'Completada',
            'reversed' => 'Reversada',
            'canceled' => 'Cancelada',
        ];

        return $statuses[$status] ?? 'Desconocido';
    }

    protected function getStatusIcon($status)
    {
        $icons = [
            'pending' => 'bx-time-five',
            'void_request' => 'bx-history',
            'voided' => 'bx-trash',
            'failed' => 'bx-x-circle',
            'completed' => 'bx-check-circle',
            'reversed' => 'bx-refresh',
            'canceled' => 'bx-x-circle',
        ];

        return $icons[$status] ?? 'bx-help-circle';
    }

    protected function getStatusBadgeClass($status)
    {
        $classes = [
            'pending' => 'bg-label-warning',
            'void_request' => 'bg-label-info',
            'voided' => 'bg-label-secondary',
            'failed' => 'bg-label-danger',
            'completed' => 'bg-label-success',
            'reversed' => 'bg-label-primary',
            'canceled' => 'bg-label-danger',
        ];

        return $classes[$status] ?? 'bg-light text-dark';
    }

    protected function getRowClass($status)
    {
        $classes = [
            // 'completed' => 'table-success',
            // 'failed' => 'table-danger',
            // 'pending' => 'table-warning',
            // 'voided' => 'table-secondary',
            // 'canceled' => 'table-danger',
        ];

        return $classes[$status] ?? '';
    }
}
