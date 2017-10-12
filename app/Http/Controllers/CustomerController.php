<?php
  
namespace App\Http\Controllers;
  
use App\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
  
  
class CustomerController extends Controller{
  
  
    public function index(){
  
        $customers  = Customer::all();
  
        return response()->json($customers);
  
    }
  
    public function getCustomer($id){
  
        $customer  = Customer::find($id);
  
        return response()->json($customer,200);
    }
  
    public function createCustomer(Request $request){
        
        $this->validate($request, [
            'email' => 'required|email|unique:customers',
            'first_name' => 'required',
            'last_name' => 'required',
            'country' => 'required',
        ]);
        
        
        if (!array_key_exists($request->get('country'),Customer::getCountries())){
            $returnData = array(
                'status' => 'error',
                'message' => 'Country ISO code not exists'
            );
            return response()->json($returnData, 500);
        }
        
        $customer = Customer::create([
            'first_name'        =>$request->get('first_name'),
            'last_name'         =>$request->get('last_name'),
            'country'           =>$request->get('country'),
            'email'             =>$request->get('email'),
            'balanced'          =>0,
            'bonus_balanced'    =>0,
            'bonus_to_receive'  =>rand(5, 20),
        ]);
        return response()->json($customer,200);
  
    }
    
    public function updateCustomer(Request $request,$id){
        
        $this->validate($request, [
            'email' => 'required|email|unique:customers',
            'first_name' => 'required',
            'last_name' => 'required',
            'country' => 'required',
        ]);
        
        if (!array_key_exists($request->get('country'),Customer::getCountries())){
            $returnData = array(
                'status' => 'error',
                'message' => 'Country ISO code not exists'
            );
            return response()->json($returnData, 500);
        }
        
        $customer  = Customer::findOrFail($id);
        $customer->first_name = $request->get('first_name');
        $customer->last_name = $request->get('last_name');
        $customer->country = $request->get('country');
        $customer->email = $request->get('email');
        $customer->save();
  
        return response()->json($customer);
    }
    
    public function deleteCustomer($id){
        $customer  = Customer::find($id);
        $customer->delete();
 
        return response()->json('deleted');
    }
  
}