<?php

namespace App\Controllers\Api;

use App\Models\GeneralFriendListModel;
use App\Models\GeneralUserModel;
use CodeIgniter\RESTful\ResourceController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class GeneralFriendListController extends ResourceController
{

    private $db;
    public function __construct(){

        $this->db = db_connect();

    }
    
    public function sendFriendRequest(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));
        
            if($decoded_data){

                $friendList_obj = new GeneralFriendListModel();
                $user_obj = new GeneralUserModel();

                $data = [
                    "sender_id" => $decoded_data->account_data->acc_id,
                    "receiver_id" => $this->request->getVar("receiver_id"),
                    "isFriend" => false
                ];

                $receiver_account_exists = $user_obj->where("acc_id", $this->request->getVar("receiver_id"))->first();

                $builder = $this->db->table("general_friend-list as friend");
                $builder->select("friend.*");
                $builder->where("friend.sender_id", $decoded_data->account_data->id);
                $builder->where("friend.receiver_id", $this->request->getVar("receiver_id"));
                $friendlist_check = $builder->get()->getRow();

                if($friendlist_check){
                    $builder = $this->db->table("general_friend-list as friend");
                    $builder->select("friend.*");
                    $builder->where("friend.sender_id", $this->request->getVar("receiver_id"));
                    $builder->where("friend.receiver_id", $decoded_data->account_data->id );
                    $second_friendlist_check = $builder->get()->getRow();

                    if($second_friendlist_check){
                        if($receiver_account_exists && $this->request->getVar("receiver_id") != $decoded_data->account_data->acc_id){
                            if($friendList_obj->insert($data)){
                                $response = [
                                    "status" => 200,
                                    "message" => "Friend request sended!",
                                    "error" => false,
                                    "data" => []
                                ];
                            } else {
                                $response = [
                                    "status" => 500,
                                    "message" => "Friend request not sended",
                                    "error" => true,
                                    "data" => []
                                ];
                            }
                        } else {
                            $response = [
                                "status" => 500,
                                "message" => "User not found",
                                "error" => true,
                                "data" => []
                            ];
                        }
                    } else {
                        $response = [
                            "status" => 500,
                            "message" => "Request alredy sended",
                            "error" => true,
                            "data" => []
                        ];
                    }
                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Request alredy sended",
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

    public function accepFriendRequest($friend_req_id){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));
       
            if($decoded_data){

                $friendList_obj = new GeneralFriendListModel();
                $friend_request = $friendList_obj->where("id", $friend_req_id)->first();

                if($friend_request){

                    $builder = $this->db->table("general_friend-list as friend");
                    $builder->select("friend.*");
                    $builder->where("friend.receiver_id", $decoded_data->account_data->acc_id);
                    $friend_request_check = $builder->get()->getRow();

                    if($friend_request_check){
                        $builder = $this->db->table("general_friend-list as friend");
                        $builder->set("isFriend", true);
                        $builder->where("friend.receiver_id", $decoded_data->account_data->acc_id);
                        $accepted_request = $builder->update();

                        $response = [
                            "status" => 200,
                            "message" => "Friend request accepted!",
                            "error" => false,
                            "data" => $accepted_request
                        ];
                    }

                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Friend request not found",
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

    public function showInvites(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));
        
            if($decoded_data){

                $builder = $this->db->table("general_friend-list as friend");
                $builder->select("friend.sender_id, user.user_fullName as invited_by");
                $builder->join("general_user as user", "user.acc_id = friend.sender_id");
                $builder->where("friend.receiver_id", $decoded_data->account_data->acc_id);
                $builder->where("friend.isFriend", false);
                $invites = $builder->get()->getResultArray();

                if($invites){
                    $response = [
                        "status" => 200,
                        "message" => "Account invites",
                        "error" => false,
                        "data" => $invites
                    ];
                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Account doesnot have invites",
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

    public function showFriends(){

        $auth = $this->request->getHeader("Authorization");

        if (isset($auth)) {

            $token = $auth->getValue();
            $decoded_data = JWT::decode($token, new Key($_ENV["JWT_TOKEN"], 'HS256'));
        
            if($decoded_data){

                $builder = $this->db->table("general_friend-list as friend");
                $builder->select("friend.*");
                $builder->where("friend.receiver_id", $decoded_data->account_data->acc_id);
                $builder->orWhere("friend.sender_id", $decoded_data->account_data->acc_id);
                $builder->where("friend.isFriend", true);
                $friend_list = $builder->get()->getResultArray();

                if($friend_list){
                    $response = [
                        "status" => 200,
                        "message" => "Account friends",
                        "error" => false,
                        "data" => $friend_list
                    ];
                } else {
                    $response = [
                        "status" => 500,
                        "message" => "Account doesnot have friends",
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
