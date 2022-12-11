<?php

namespace App\Controllers\Api;

use App\Models\GeneralPaymentsModel;
use App\Models\GeneralUserModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class GeneralPaymentsController extends ResourceController
{
    private $db;
    public function __construct(){

        $this->db = db_connect();

    }
    
    public function requestPayment(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {
        
            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $rules = [
                    "payment_value" => "required",
                ];

                if (!$this->validate($rules)) {
                    $response = [
                        "status" => 500,
                        "message" => $this->validator->getErrors(),
                        "error" => true,
                        "data" => []
                    ];

                } else {

                    $payment_obj = new GeneralPaymentsModel();
                    
                    if($this->request->getVar("payment_description")){
                        $data = [
                            "payment_id" => rand(999999999999999, 9999999999999999),
                            "receipt_user_id" => $decoded_data->account_data->id,
                            "payment_description" => $this->request->getVar("payment_description"),
                            "payment_value" => $this->request->getVar("payment_value"),
                            "is_paid" => false,
                        ];
                    } else {
                        $data = [
                            "payment_id" => rand(999999999999999, 9999999999999999),
                            "receipt_user_id" => $decoded_data->account_data->id,
                            "payment_description" => "",
                            "payment_value" => $this->request->getVar("payment_value"),
                            "is_paid" => false,
                        ];
                    }

                    if ($payment_obj->insert($data)) {
                        $response = [
                            "status" => 200,
                            "message" => "Payment created",
                            "error" => false,
                            "data" => $data
                        ];
                    }
                }
            }

        } else {
            $response = [
                "status" => 500,
                "message" => "User must be login",
                "error" => true,
                "data" => []
            ];
        }

        return $this->respondCreated($response);

    }


    public function paymentDetails($payment_id){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("general_payments as payment");
                $builder->select("payment.payment_id, payment.payment_description, payment.payment_value, payment.paid_by_id, payment.paid_date, payment.is_paid, user.user_fullName as receipt_fullName");
                $builder->join("general_user as user", "user.id = payment.receipt_user_id");
                $builder->where("payment.payment_id", $payment_id);
                $payment_details = $builder->get()->getRow();

                if($payment_details){
                    $response = [
                        "status" => 200,
                        "message" => "Payment details",
                        "error" => false,
                        "data" => $payment_details
                    ];
                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Payment ID not found",
                        "error" => true,
                        "data" => []
                    ];
                }

            }

        } else {
            $response = [
                "status" => 500,
                "message" => "User must be login",
                "error" => true,
                "data" => []
            ];
        }

        return $this->respondCreated($response);

    }

    public function payPaymentLink($payment_id){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("general_payments as payment");
                $builder->select("payment.*, user.user_fullName as receipt_fullName");
                $builder->join("general_user as user", "user.id = payment.receipt_user_id");
                $builder->where("payment.payment_id", $payment_id);
                $payment_details = $builder->get()->getRow();

                if($payment_details->receipt_user_id != $decoded_data->account_data->id){

                    if($payment_details->is_paid != "1"){

                        if($payment_details){
                            // $decoded_data->account_data->acc_id
                            $builder = $this->db->table("general_user as user");
                            $builder->select("user.user_balance");
                            $builder->where("id", $decoded_data->account_data->id);
                            $balance = $builder->get()->getRow();
        
                            if($balance->user_balance >= $payment_details->payment_value){
        
                                $builder = $this->db->table("general_payments as payment");
                                $builder->set("paid_by_id", $decoded_data->account_data->id);
                                $builder->set("paid_date", date('d-m-Y'));
                                $builder->set("is_paid", true);
                                $builder->where("payment.payment_id", $payment_id);
                                $payment_update = $builder->update();
        
                                if($payment_update){
    
                                    $user_obj = new GeneralUserModel();
    
                                    $account_receipt = $user_obj->where("id", $payment_details->receipt_user_id)->first();
    
                                    $builder = $this->db->table("general_user as user");
                                    $builder->set("user.user_balance", $account_receipt["user_balance"] + $payment_details->payment_value);
                                    $builder->where("user.id", $payment_details->receipt_user_id);
                                    $account_receipt_add_found = $builder->update();
    
                                    if($account_receipt_add_found){
    
                                        $builder = $this->db->table("general_user as user");
                                        $builder->set("user_balance", $account_receipt["user_balance"] - $payment_details->payment_value);
                                        $builder->where("user.id", $decoded_data->account_data->id);
                                        $account_payer_remove_found = $builder->update();
    
                                        if($account_payer_remove_found){
                                            $response = [
                                                "status" => 200,
                                                "message" => "Payment paid successfully",
                                                "error" => false,
                                                "data" => $payment_details->payment_value
                                            ];
    
                                        }
    
                                    }
                                    
                                } else {
                                    $response = [
                                        "status" => 500,
                                        "message" => "Payment cannot be paid",
                                        "error" => true,
                                        "data" => []
                                    ];
                                }
                                
                            } else {
                                $response = [
                                    "status" => 500,
                                    "message" => "Insufficient funds for payment",
                                    "error" => true,
                                    "data" => []
                                ];
                            }
        
                        } else {
                            $response = [
                                "status" => 500,
                                "message" => "Payment ID not found",
                                "error" => true,
                                "data" => []
                            ];
                        }

                    } else {
                        $response = [
                            "status" => 500,
                            "message" => "Payment Alredy paid",
                            "error" => true,
                            "data" => []
                        ];
                    }
                    

                } else {
                    $response = [
                        "status" => 500,
                        "message" => "You cannot paid your payment link",
                        "error" => true,
                        "data" => []
                    ];
                }

            }

        } else {
            $response = [
                "status" => 500,
                "message" => "User must be login",
                "error" => true,
                "data" => []
            ];
        }

        return $this->respondCreated($response);

    }

}
