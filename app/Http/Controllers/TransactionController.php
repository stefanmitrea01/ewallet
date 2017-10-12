<?php

namespace App\Http\Controllers;

use App\Customer;
use App\Transaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller {

    public function createTransaction(Request $request, $id) {

        $this->validate($request, [
            'amount' => 'required|integer',
            'type' => 'required'
        ]);

        $customer = Customer::findOrFail($id);
        if ($request->get('type') == 'deposit') {
            $transaction = Transaction::create([
                'customer_id' => $customer->id,
                'type' => 'deposit',
                'amount' => $request->get('amount'),
            ]);
            if ($transaction->type == 'deposit') {
                $customer->balanced = $customer->balanced + $transaction->amount;
                $countDeposits = $customer->transaction()->where('type', 'deposit')->get()->count();
                if ($countDeposits % 3 == 0) {
                    $transactionBonus = Transaction::create([
                        'customer_id' => $customer->id,
                        'type' => 'bonus',
                        'amount' => ($customer->bonus_to_receive / 100) * $transaction->amount,
                    ]);

                    $customer->bonus_balanced = $customer->bonus_balanced + (($customer->bonus_to_receive / 100) * $transaction->amount);
                }
                $customer->save();
            }
        }
        if ($request->get('type') == 'withdraw') {
            if (($customer->balanced != 0) && ($customer->balanced > $request->get('amount')) ) {
                $transaction = Transaction::create([
                    'customer_id' => $customer->id,
                    'type' => 'withdraw',
                    'amount' => $request->get('amount'),
                ]);
                $customer->balanced = $customer->balanced - $transaction->amount;
                $customer->save();
            } else {
                $returnData = array(
                    'status' => 'error',
                    'message' => 'Insufficient funds'
                );
                return response()->json($returnData, 500);
            }
        }
        
        return response()->json($transaction, 200);
    }
    public function raportTransaction(Request $request) {
        if(!$request->has('date')){
            $returnData = array(
                'status' => 'error',
                'message' => 'No date provided'
            );
            return response()->json($returnData, 500);
        }
        
        $this->validate($request, [
            'date' => 'date'
        ]);
        $start = new \DateTime('now');
        $toDate = new \DateTime($request->get('date'));
        
        $transactions  = Transaction::query()
            ->whereBetween('transactions.created_at', [$toDate->format('Y-m-d H:i:s'), $start->format('Y-m-d H:i:s')])
            ->select('created_at as date',
                    DB::raw("(SELECT customers.country FROM customers
                        WHERE customers.id = transactions.customer_id
                        GROUP BY customers.country) as country"),
                    DB::raw("SUM( ( CASE WHEN type = 'deposit' THEN amount END ) ) AS total_deposit_amount"),
                    DB::raw("SUM( ( CASE WHEN type = 'withdraw' THEN amount END ) ) AS total_withdraw_amount"),
                    DB::raw("COUNT(DISTINCT customer_id) as unique_customers"),
                    DB::raw("COUNT( ( CASE WHEN type = 'withdraw' THEN amount END ) ) AS no_of_withdraws"),
                    DB::raw("COUNT( ( CASE WHEN type = 'deposit' THEN amount END ) ) AS no_of_deposits"),
                    DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d") as date'))
            ->orderBy('transactions.created_at', 'desc')
            ->groupBy('country')
            ->get()
            ->toArray();
        
        
        
        echo '<pre>';
        print_r($transactions);
        exit;
        
        return response()->json($transactions, 200);
    }

}
