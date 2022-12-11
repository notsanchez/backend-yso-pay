<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\GeneralTransactionsModel;
use App\Models\GeneralUserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GeneralTransactionController extends ResourceController
{

    private $db;
    public function __construct(){

        $this->db = db_connect();

    }

    public function transactToUser(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {
        
            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $rules = [
                    "receiver_id" => "required",
                    "transaction_value" => "required"
                ];

                if (!$this->validate($rules)) {
                    $response = [
                        "status" => 500,
                        "message" => $this->validator->getErrors(),
                        "error" => true,
                        "data" => []
                    ];

                } else {

                    $transaction_obj = new GeneralTransactionsModel();
                    $user_obj = new GeneralUserModel();

                    $account_exists = $user_obj->where("acc_id", $this->request->getVar("receiver_id"))->first();

                    if($account_exists && $account_exists["acc_id"] != $decoded_data->account_data->acc_id){

                        $builder = $this->db->table("general_user as user");
                        $builder->select("user.user_balance");
                        $builder->where("user.id", $decoded_data->account_data->id);
                        $account_balance = $builder->get()->getRow();

                        if($account_balance->user_balance >= $this->request->getVar("transaction_value")){

                            $builder = $this->db->table("general_user as user");
                            $builder->select("user.user_fullName, user.user_balance");
                            $builder->where("user.acc_id", $this->request->getVar("receiver_id"));
                            $receiver_data = $builder->get()->getRow();

                            if($this->request->getVar("message")){
                                $data = [
                                    "sender_id" => $decoded_data->account_data->acc_id,
                                    "receiver_id" => $this->request->getVar("receiver_id"),
                                    "transaction_value" => $this->request->getVar("transaction_value"),
                                    "message" => $this->request->getVar("message"),
                                    "transaction_date" => date('d-m-y h:i:s'),
                                    "receiver_full_Name" => $receiver_data->user_fullName
                                ];
                            } else {
                                $data = [
                                    "sender_id" => $decoded_data->account_data->acc_id,
                                    "receiver_id" => $this->request->getVar("receiver_id"),
                                    "transaction_value" => $this->request->getVar("transaction_value"),
                                    "message" => "",
                                    "transaction_date" => date('d-m-y h:i:s'),
                                    "receiver_full_Name" => $receiver_data->user_fullName
                                ];
                            }
        
                            if($transaction_obj->insert($data)){

                                $builder->set("user_balance", intval($account_balance->user_balance) - intval($this->request->getVar("transaction_value")));
                                $builder->where("user.id", $decoded_data->account_data->id);
                                $update_sender = $builder->update();

                                if($update_sender){

                                    $builder->set("user_balance", intval($receiver_data->user_balance) + intval($this->request->getVar("transaction_value")));
                                    $builder->where("user.acc_id", $this->request->getVar("receiver_id"));
                                    $update_receiver = $builder->update();
                                    if($update_receiver){
                                        $response = [
                                            "status" => 200,
                                            "message" => "Transaction successfully",
                                            "error" => false,
                                            "data" => $data,
                                        ];
                                    }
                                }
                        
                            } else {
                                $response = [
                                    "status" => 500,
                                    "message" => "Transaction not successfully",
                                    "error" => true,
                                    "data" => []
                                ];
                            }
                        } else {
                            $response = [
                                "status" => 500,
                                "message" => "Account not have sufficient balance",
                                "error" => true,
                                "data" => []
                            ];
                        }

                    } else {

                        $response = [
                            "status" => 404,
                            "message" => "Account not found",
                            "error" => true,
                            "data" => []
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

    public function userTransactions(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("general_transactions as transactions");
                $builder->select("transactions.*");
                $builder->where("transactions.sender_id", $decoded_data->account_data->acc_id);
                $builder->orWhere("transactions.receiver_id", $decoded_data->account_data->acc_id);
                $user_transactions = $builder->get()->getResultArray();
                
                $response = [
                    "status" => 200,
                    "message" => "All user transactions",
                    "error" => false,
                    "user_id" => $decoded_data->account_data->acc_id,
                    "data" => $user_transactions
                ];

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


    public function transactionDetails($transaction_id){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("general_transactions as transactions");
                $builder->select("transactions.*, general_user.user_fullName as receiver_fullName");
                $builder->join("general_user", "acc_id = transactions.receiver_id");
                $builder->where("transactions.id", $transaction_id);
                $transaction_details = $builder->get()->getRow();

                if($transaction_details->sender_id == $decoded_data->account_data->acc_id || $transaction_details->receiver_id == $decoded_data->account_data->acc_id){

                    if($transaction_details->sender_id == $decoded_data->account_data->acc_id){
                        $data = [
                            "transaction_type" => "payment",
                            "transaction_id" => $transaction_details->id,
                            "sender_id" => $transaction_details->sender_id,
                            "receiver_id" => $transaction_details->receiver_id,
                            "transaction_value" => $transaction_details->transaction_value,
                            "message" => $transaction_details->message,
                            "transaction_date" => $transaction_details->transaction_date,
                            "sender_fullName" =>  $decoded_data->account_data->user_fullName,
                            "receiver_fullName" => $transaction_details->receiver_fullName,
                        ];
                    } else {

                        $builder = $this->db->table("general_transactions as transactions");
                        $builder->select("transactions.*, general_user.user_fullName as sender_fullName");
                        $builder->join("general_user", "acc_id = transactions.sender_id");
                        $builder->where("transactions.id", $transaction_id);
                        $details_transaction = $builder->get()->getRow();

                        $data = [
                            "transaction_type" => "receipt",
                            "transaction_id" => $details_transaction->id,
                            "sender_id" => $details_transaction->sender_id,
                            "receiver_id" => $details_transaction->receiver_id,
                            "transaction_value" => $details_transaction->transaction_value,
                            "message" => $details_transaction->message,
                            "transaction_date" => $details_transaction->transaction_date,
                            "sender_fullName" =>  $details_transaction->sender_fullName,
                            "receiver_fullName" => $decoded_data->account_data->user_fullName,
                        ];
                    }
                    
                    $response = [
                        "status" => 200,
                        "message" => "Transaction details",
                        "error" => false,
                        "data" => $data
                    ];
                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Transaction not found",
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
