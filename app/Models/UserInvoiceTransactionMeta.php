<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvoiceTransactionMeta extends Model
{
    protected $table = 'user_invoice_transactions_meta';
    protected $casts = [
        'meta_data' => 'array',
    ];
}
