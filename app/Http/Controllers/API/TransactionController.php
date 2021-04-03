<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Midtrans\Config;
use Midtrans\Snap;

class TransactionController extends Controller
{
    public function all(Request $request){
        $id=$request->input('id');
        $limit=$request->input('limit', 6);
        $user_id=$request->input('user_id');
        $food_id= $request->input('food_id');
        $status= $request->input('status');


      

         if ($id){
             $trnsaction=Transaction::with(['food','user'])->find($id);
             if ($trnsaction){
                 return ResponseFormatter::success($trnsaction,'Data product berhasil di ambil');
             }else {
                 return ResponseFormatter::error(null, 'Product tidak ada', 404);
             }
         }

         $transaction=Transaction::with(['user','food'])->where('user_id',Auth::user()->id);
         if ($food_id){
             $transaction->where('food_id', $request->food_id);
         }
    
        if ($status){
            $transaction->where('status', $request->status);
        }
       
       
        
        return ResponseFormatter::success(
            $transaction->paginate($limit),
            'Data list Product berhasil di ambil'

        );

    }
    
    public function update (Request $request, $id){
        $transaction= Transaction::findOrFail($id);
        $transaction->update($request->all());

        return ResponseFormatter::success($transaction,'transaction berhasil di perbaharui');
    }
    public function checkout(Request $request){
        $request->validate([
            'food_id'=>'required|exists:food,id',
            'user_id'=>'required|exists:user,id',
            'quantity'=>'required',
            'total'=>'required',
            'status'=>'required',

        ]);
        $transaction=Transaction::create([
            'food_id'=>$request->food_id,
            'user_id'=>$request->user_id,
            'quantity'=>$request->quantity,
            'total'=>$request->total,
            'status'=>$request->status,
            'payment_url'=>''
        ]);

        // konfigurasi mistransi
        Config::$serverKey=config('services.mindtrans.serverKey');
        Config::$clientKey=config('services.mindtrans.clientKey');
        Config::$isProduction=config('services.mindtrans.isProduction');
        Config::$isSanitized=config('services.mindtrans.isSanitized');
        Config::$is3ds=config('services.mindtrans.is3ds');

        // memanggil transaction berdasarkan id
        $transaction=Transaction::with(['user','food'])->find($transaction->id);

        // membuat transaksi dengan midtrans
        $midtrans=[
            'transaction_details'=>[
                'order_id'=>$transaction->id,
                'gross_amount'=>(int) $transaction->total

            ],
            'customer_details'=>[
                "first_name"=>$transaction->user->name,
                "email"=>$transaction->user->email,
                "enabled_payments"=>['gopay','bank_transfer'],
                'vtweb'=>[]
            ]


            ];

        // memanggil midtrans

        try {
            //code...
            // ambil halaman payment url
            $paymentUrl=Snap::createTransaction($midtrans)->redirect_url;
            $transaction->payment_url = $paymentUrl;
            $transaction->save();
              // mengembalikan data ke API
            
              return ResponseFormatter::success($transaction,'Transaction Success');

            



        } catch (Exception $error) {
            return ResponseFormatter::error($error, 'Transsaction Failed');
        }

     

    }
}
