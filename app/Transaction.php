<?php

namespace App;
use Illuminate\Database\Eloquent\Model;


class Transaction extends Model{
    
    
    protected $table = 'transactions';
    protected $fillable = ['customer_id','type','amount'];
    
    public function customer(){
        return $this->belongsTo('App\Customer', 'customer_id', 'id');
    }
    
}