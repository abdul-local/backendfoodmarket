<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Notification;

class MidtransController extends Controller
{
    //
    public function callback (Request $request){

        // set Konfigurasi midtrans
        Config::$serverKey=config('services.mindtrans.serverKey');
        Config::$clientKey=config('services.mindtrans.clientKey');
        Config::$isProduction=config('services.mindtrans.isProduction');
        Config::$isSanitized=config('services.mindtrans.isSanitized');
        Config::$is3ds=config('services.mindtrans.is3ds');



        // buat instance midtrans notification
        $notification = new Notification();


        // assign ke variabel untuk memudahkan codingan 

        $status=$notification->transaction_status;
        $type=$notification->payment_type;
        $fraud=$notification->fraud_status;
        $order_id=$notification->order_id;

        //Cari transaksi berdasarkan id
        $transaction=Transaction::findOrFail($order_id);


        // Handle notifikasi status midtrans
        if ($status=='capture'){
            if ($type=='credit_card'){
                if ($fraud=='challenge'){
                    $transaction->status='PENDING';

                }else {
                    $transaction->status='SUCCESS';

                }
            }else if ($status=='settlement'){
                $transaction->status='SUCCESS';

            }
            else if ($status=='pending'){
                $transaction->status='PENDING';
                
            }
            else if ($status=='deny'){
                $transaction->status='CANCELLED';
                
            }
            else if ($status=='expire'){
                $transaction->status='CANCELLED';
                
            }
            else if ($status=='cancel'){
                $transaction->status='CANCELLED';
                
            }
        }



        // simpan transaksi

        $transaction->save();

    }
    public function success(){
        return view('midtrans.success');
    }
    public function unfinished(){
        return view('midtrans.unfinished');
    }
    public function error(){
        return view('midtrans.error');
    }
}
