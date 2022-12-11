<?php

namespace App\Controllers\Api;

use App\Models\GeneralUser;
use CodeIgniter\RESTful\ResourceController;
use App\Models\GeneralUserModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GeneralUserController extends ResourceController
{
    private $db;
    public function __construct(){

        $this->db = db_connect();

    }

    //POST
    public function openNewAccount(){

        $rules = [
            "user_fullName" => "required",
            "user_CPF" => "required|is_unique[general_user.user_CPF]",
            "account_password" => "required",
            "user_email" => "required|valid_email|is_unique[general_user.user_email]",
            "user_phone" => "required|is_unique[general_user.user_phone]",
        ];

        if(!$this->validate($rules)){

            $response = [
                "status" => 500,
                "message" => $this->validator->getErrors(),
                "error" => true,
                "data" => []
            ];

        } else {

            $user_obj = new GeneralUserModel();

            $data = [
                "acc_id" => rand(1000, 9999),
                "account_number" => rand(1000, 99999),
                "user_fullName" => $this->request->getVar("user_fullName"),
                "user_profilePicture" => $this->request->getVar("user_profilePicture"),
                "user_docPicture" => $this->request->getVar("user_docPicture"),
                "user_CPF" => $this->request->getVar("user_CPF"),
                "account_password" => password_hash($this->request->getVar("account_password"), PASSWORD_DEFAULT),
                "user_email" => $this->request->getVar("user_email"),
                "user_phone" => $this->request->getVar("user_phone"),
                "user_randomKey" => hash('md5', $this->request->getVar("user_phone")),
                "user_balance" => 0,
                "isValid" => true,
            ];

            if($user_obj->insert($data)){
                $response = [
                    "status" => 200,
                    "message" => "Account open successfully",
                    "error" => false,
                    "data" => $data
                ];
            } else {
                $response = [
                    "status" => 500,
                    "message" => "Account doesnot open",
                    "error" => true,
                    "data" => []
                ];
            }

        }

        return $this->respondCreated($response);

    }

    //POST
    public function userAccountLogin(){

        $rules = [
            "user_CPF" => "required",
            "account_password" => "required"
        ];

        if(!$this->validate($rules)){

            $response = [
                "status" => 500,
                "message" => $this->validator->getErrors(),
                "error" => true,
                "data" => []
            ];

        } else {

            $user_CPF = $this->request->getVar("user_CPF");
            $account_password = $this->request->getVar("account_password");


            $user_obj = new GeneralUserModel();

            $account_data = $user_obj->where("user_CPF", $user_CPF)->first();

            if(!empty($account_data)){
                //conta existe

                if (password_verify($account_password, $account_data['account_password'])){

                    $iat = time();
                    $nbf = $iat;
                    $exp = $iat + 900;

                    $payload = [
                        'iat' => $iat,
                        'nbf' => $nbf,
                        'exp' => $exp,
                        'account_data' => $account_data
                    ];
                    
                    $token = JWT::encode($payload, $_ENV["JWT_TOKEN"], 'HS256');

                    if($account_data["isValid"] == 1){
                        $response = [
                            "status" => 200,
                            "message" => "User logged in account",
                            "error" => false,
                            "data" => [
                                "token" => $token
                            ]
                        ];
                    } else {
                        $response = [
                            "status" => 500,
                            "message" => "Account not approved",
                            "error" => true,
                            "data" => []
                        ];
                    }

                   
                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Password didn't match",
                        "error" => true,
                        "data" => []
                    ];
                }

            } else {

                $response = [
                    "status" => 500,
                    "message" => "Account doesnot exists",
                    "error" => true,
                    "data" => []
                ];

            }

        }

        return $this->respondCreated($response);

    }

    //GET
    public function userAccountDetails(){

        $auth = $this->request->getHeader("Authorization");

        if(isset($auth)){
            $token = $auth->getValue();

            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            $builder = $this->db->table("general_user as user");
            $builder->select("user.*");
            $builder->where("user.id", $decoded_data->account_data->id);
            $account_details = $builder->get()->getRow();

            $response = [
                "status" => 200,
                "message" => "account details",
                "error" => false,
                "data" => $account_details
            ];
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


    public function dashboardAccountDetails(){
        $auth = $this->request->getHeader("Authorization");

        if(isset($auth)){
            $token = $auth->getValue();


            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            $builder = $this->db->table("general_user as user");
            $builder->select("user.*");
            $builder->where("user.id", $decoded_data->account_data->id);
            $account_details = $builder->get()->getRow();

            $response = [
                "status" => 200,
                "message" => "account details",
                "error" => false,
                "data" => [
                    "acc_id" => $account_details->acc_id,
                    "account_number" => $account_details->account_number,
                    "user_fullName" => $account_details->user_fullName,
                    "user_profilePicture" => $account_details->user_profilePicture,
                    "user_balance" => $account_details->user_balance,
                ]
                
            ];
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


    public function searchUser($user_search_id){

        $auth = $this->request->getHeader("Authorization");

        if(isset($auth)){
            $token = $auth->getValue();


            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){
                $builder = $this->db->table("general_user as users");

                $builder->select("users.*");
                $builder->like("users.acc_id", $user_search_id);
                $data = $builder->get()->getRow();

                $response = [
                    "status" => 200,
                    "message" => "User searched details",
                    "error" => false,
                    "data" =>   [
                        "acc_id" => $data->acc_id,
                        "account_number" => $data->account_number,
                        "user_fullName" => $data->user_fullName,
                        "user_profilePicture" => $data->user_profilePicture
                    ]
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

}
