<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GeneralTransactions extends Migration
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
            "transaction_value" => [
                "type" => "FLOAT",
                "null" => false
            ],
            "message" => [
                "type" => "TEXT",
            ],
            "transaction_date" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => false
            ],
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("general_transactions");
    }

    public function down()
    {
        $this->forge->dropTable("general_transactions");
    }
}
