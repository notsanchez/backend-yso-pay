<?php

namespace App\Controllers\Api;

use App\Models\GeneralCardsModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class GeneralCardsController extends ResourceController
{

    private $db;
    public function __construct(){

        $this->db = db_connect();

    }

    public function requestNewCard(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));
        
            if($decoded_data){

                $rules = [
                    "card_limit" => "required",
                ];

                if (!$this->validate($rules)) {
                    $response = [
                        "status" => 500,
                        "message" => $this->validator->getErrors(),
                        "error" => true,
                        "data" => []
                    ];

                } else {

                    $card_obj = new GeneralCardsModel();

                    $data = [
                        "user_acc_id" => $decoded_data->account_data->id,
                        "card_limit" => $this->request->getVar("card_limit"),
                        "usage_balance" => 0,
                        "card_number" => rand(999999999999999, 9999999999999999),
                        "card_exp_date" => date('d-m-Y', strtotime('+5 years')),
                        "card_cvv" => rand(99, 999),
                        "isValid" => false,
                    ];

                    if($card_obj->insert($data)){
                        $response = [
                            "status" => 200,
                            "message" => "Requested to create a new card send successfully!",
                            "error" => false,
                        ];
                    } else {
                        $response = [
                            "status" => 500,
                            "message" => "Requested to create a new card failed",
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


    public function getAllCardsForUser(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("general_cards as cards");
                $builder->select("cards.*");
                $builder->where("cards.user_acc_id", $decoded_data->account_data->id);
                $builder->where("cards.isValid", "1");
                $user_cards = $builder->get()->getResultArray();

                if($user_cards){

                    $response = [
                        "status" => 200,
                        "message" => "Account cards",
                        "error" => false,
                        "data" => $user_cards
                    ];
                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Account does not have any card",
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


    public function cardDetails($card_id){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));

            if($decoded_data){

                $builder = $this->db->table("general_cards as cards");
                $builder->select("cards.*");
                $builder->where("cards.id", $card_id);
                $builder->where("cards.user_acc_id", $decoded_data->account_data->id);
                $builder->where("cards.isValid", "1");
                $card_details = $builder->get()->getRow();

                if($card_details){
                    $response = [
                        "status" => 200,
                        "message" => "Card details",
                        "error" => false,
                        "data" => $card_details
                    ];
                } else {
                    $response = [
                        "status" => 400,
                        "message" => "Card not found",
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
