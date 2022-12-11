<?php

namespace App\Controllers\Api;

use App\Models\GeneralInvestimentsModel;
use App\Models\InvestimentsConnectionsModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GeneralInvestimentsController extends ResourceController
{

    private $db;
    public function __construct(){

        $this->db = db_connect();

    }

    public function createInvestiment(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));
        
            if($decoded_data){

                $rules = [
                    "investiment_type" => "required",
                    "investiment_name" => "required",
                    "income_percentage_per_year" => "required",
                    "days_for_withdrawal" => "required",
                ];

                if (!$this->validate($rules)) {
                    $response = [
                        "status" => 500,
                        "message" => $this->validator->getErrors(),
                        "error" => true,
                        "data" => []
                    ];

                } else {

                    $investiment_obj = new GeneralInvestimentsModel();

                    $data = [
                        "investiment_type" => $this->request->getVar("investiment_type"),
                        "investiment_name" => $this->request->getVar("investiment_name"),
                        "income_percentage_per_year" => $this->request->getVar("income_percentage_per_year"),
                        "days_for_withdrawal" => $this->request->getVar("days_for_withdrawal"),
                    ];

                    if($investiment_obj->insert($data)){

                        $response = [
                            "status" => 200,
                            "message" => "Investiment successfully created!",
                            "error" => false,
                            "data" => $data
                        ];

                    } else {

                        $response = [
                            "status" => 500,
                            "message" => "Investiment cannot be created",
                            "error" => true,
                            "data" => []
                        ];

                    }

                }

            }

        } else {

            $response = [
                "status" => 500,
                "message" => "User must be logged in",
                "error" => true,
                "data" => []
            ];
        }

        return $this->respondCreated($response);


    }

    public function showInvestimentsType(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("general_investiments as investiment");
                $builder->select("investiment.*");
                $investiments = $builder->get()->getResultArray();

                if($investiments){

                    $response = [
                        "status" => 200,
                        "message" => "All investiments",
                        "error" => false,
                        "data" => $investiments
                    ];

                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Investiments not found",
                        "error" => true,
                        "data" => []
                    ];

                }

            }

        } else {

            $response = [
                "status" => 500,
                "message" => "User must be logged in",
                "error" => true,
                "data" => []
            ];
        }

        return $this->respondCreated($response);

    }

    public function investimentType($investiment_id){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("general_investiments as investiment");
                $builder->select("investiment.*");
                $builder->where("investiment.id", $investiment_id);
                $investiments = $builder->get()->getResultArray();

                if($investiments){

                    $response = [
                        "status" => 200,
                        "message" => "Investiment info",
                        "error" => false,
                        "data" => $investiments
                    ];

                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Investiment not found",
                        "error" => true,
                        "data" => []
                    ];

                }

            }

        } else {

            $response = [
                "status" => 500,
                "message" => "User must be logged in",
                "error" => true,
                "data" => []
            ];
        }

        return $this->respondCreated($response);

    }

    public function createInvestimentOrder($investiment_id){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));
        
            if($decoded_data){

                $investiment_amount = $this->request->getVar("investiment_amount");

                $builder = $this->db->table("general_user as user");
                $builder->select("user.user_balance");
                $builder->where("user.id", $decoded_data->account_data->id);
                $user_balance = $builder->get()->getRow();
                
                if($user_balance->user_balance >= $investiment_amount){

                    $investimentConn_obj = new InvestimentsConnectionsModel();

                    $data = [
                        "investiment_id" => $investiment_id,
                        "user_id" => $decoded_data->account_data->id,
                        "investiment_amount" => $investiment_amount,
                    ];

                    if($investimentConn_obj->insert($data)){

                        $builder = $this->db->table("general_user as user");
                        $builder->set("user_balance", (int) $user_balance->user_balance - (int) $investiment_amount);
                        $builder->where("user.id", $decoded_data->account_data->id);
                        $user_balance_update = $builder->update();

                        if($user_balance_update){
                            $response = [
                                "status" => 200,
                                "message" => "Investiment order created successfully!",
                                "error" => false,
                                "data" => []
                            ];

                        }

                    }

                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Insuficient balance for investiment",
                        "error" => true,
                        "data" => []
                    ];
                }

            }
        } else {

            $response = [
                "status" => 500,
                "message" => "User must be logged in",
                "error" => true,
                "data" => []
            ];
        }

        return $this->respondCreated($response);

    }

    public function investimentOrders(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("investiments_connection as invest_conn");
                $builder->select("invest_conn.*, general_investiments.investiment_type, general_investiments.investiment_name");
                $builder->join("general_investiments", "general_investiments.id = invest_conn.investiment_id");
                $builder->where("invest_conn.user_id", $decoded_data->account_data->id);
                $investiment_orders = $builder->get()->getResultArray();

                if($investiment_orders){
                    $response = [
                        "status" => 200,
                        "message" => "Your investiment orders",
                        "error" => false,
                        "data" => $investiment_orders
                    ];

                }

            }

        } else {

            $response = [
                "status" => 500,
                "message" => "User must be logged in",
                "error" => true,
                "data" => []
            ];
        }

        return $this->respondCreated($response);

    }


    public function singleInvestimentOrderDetails($investiment_id){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("investiments_connection as invest_conn");
                $builder->select("invest_conn.*, general_investiments.investiment_type, general_investiments.investiment_name, general_investiments.income_percentage_per_year, general_investiments.days_for_withdrawal");
                $builder->join("general_investiments", "general_investiments.id = invest_conn.investiment_id");
                $builder->where("invest_conn.id", $investiment_id);
                $investiment_order = $builder->get()->getRow();
                
                if($investiment_order->user_id == $decoded_data->account_data->id){
                    $response = [
                        "status" => 200,
                        "message" => "Investiment order details",
                        "error" => false,
                        "data" => $investiment_order
                    ];
                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Investiment not found",
                        "error" => true,
                        "data" => []
                    ];
                }

            }

        } else {

            $response = [
                "status" => 500,
                "message" => "User must be logged in",
                "error" => true,
                "data" => []
            ];
        }

        return $this->respondCreated($response);

    }


    public function withdrawalOrder($order_id){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("investiments_connection as invest_conn");
                $builder->select("invest_conn.*, general_investiments.investiment_type, general_investiments.investiment_name, general_investiments.income_percentage_per_year, general_investiments.days_for_withdrawal");
                $builder->join("general_investiments", "general_investiments.id = invest_conn.investiment_id");
                $builder->where("invest_conn.id", $order_id);
                $investiment_order = $builder->get()->getRow();

                if(date('d-m-Y', strtotime("+$investiment_order->days_for_withdrawal days")) < date('d-m-Y')){

                    $builder = $this->db->table("general_user as user");
                    $builder->select("user.user_balance");
                    $builder->where("user.id", $decoded_data->account_data->id);
                    $user_balance = $builder->get()->getRow();

                    if($user_balance){
                        $builder = $this->db->table("general_user as user");
                        $builder->set("user_balance", (int) $user_balance->user_balance + (int) $investiment_order->investiment_amount);
                        $builder->where("user.id", $decoded_data->account_data->id);
                        $user_balance_update = $builder->update();

                        if($user_balance_update){

                            $response = [
                                "status" => 200,
                                "message" => "Balance received successfully",
                                "error" => false,
                                "data" => []
                            ];

                        }

                    }
                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Withdrawal time not supported",
                        "error" => true,
                        "data" => []
                    ];
                }

            }
        
        } else {

            $response = [
                "status" => 500,
                "message" => "User must be logged in",
                "error" => true,
                "data" => []
            ];
        }

        return $this->respondCreated($response);

    }


    

}
