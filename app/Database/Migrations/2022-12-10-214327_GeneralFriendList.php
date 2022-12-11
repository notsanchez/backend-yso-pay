<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GeneralFriendList extends Migration
{
    public function up()
    {
        $this->forge->addField([
            "id" => [
                "type" => "INT",
                "constraint" => 5,
                "unsigned" => true,
                "auto_increment" => true
            ],
            "sender_id" => [
                "type" => "INT",
                "null" => false
            ],
            "receiver_id" => [
                "type" => "INT",
                "null" => false
            ],
            "isFriend" => [
                "type" => "BOOL"
            ]
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("general_friend-list");
    }

    public function down()
    {
        $this->forge->dropTable("general_friend-list");
    }
}
