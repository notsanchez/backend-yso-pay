<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GeneralCards extends Migration
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
            "user_acc_id" => [
                "type" => "INT",
                "null" => false
            ],
            "card_limit" => [
                "type" => "FLOAT",
                "null" => false
            ],
            "usage_balance" => [
                "type" => "FLOAT",
                "null" => false
            ],
            "card_number" => [
                "type" => "VARCHAR",
                "constraint" => 50,
            ],
            "card_exp_date" => [
                "type" => "VARCHAR",
                "constraint" => 50,
            ],
            "card_cvv" => [
                "type" => "VARCHAR",
                "constraint" => 50,
            ],
            "isValid" => [
                "type" => "BOOL",
            ],
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("general_cards");
    }

    public function down()
    {
        $this->forge->dropTable("general_cards");
    }
}
