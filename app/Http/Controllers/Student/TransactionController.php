<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(){
        try{
            $user = JWTAuth::parseToken()->authenticate();
            $get_transactions = Transaction::where([
                'user_uuid' => $user->uuid,
            ])
            ->with(['detailTransaction', 'detailTransaction.package'])
            ->get();
            $transactions = [];
            foreach ($get_transactions as $index => $transaction) {
                $packages = [];
                foreach ($transaction->detailTransaction as $index1 => $detail) {
                    $packages[] =[
                        "package_uuid" => $detail->package_uuid,
                        "type_of_purchase" => $detail->type_of_purchase,
                        "name" => $detail->package['name'],
                        "image" => $detail->package['image'],
                        "price" => $detail->detail_amount,
                    ];
                }
                $transactions[] = [
                    "transaction_uuid" => $transaction->uuid,
                    "amount" => $transaction->transaction_amount,
                    "status" => $transaction->transaction_status,
                    "url" => $transaction->url,
                    "packages" => $packages,
                    "expired_date" => $transaction->expiry_date,
                    "created_at" => $transaction->created_at,
                    "updated_at" => $transaction->updated_at,
                ];
            }

            return response()->json([
                'message' => 'Sukses mengambil data',
                'transaction' => $transactions,
            ]);
        }catch (\Exception $e) {
            // Tangkap exception dan kirimkan pesan kesalahan
            return response()->json([
                'message' => $e
            ]);
        }

    }
}
