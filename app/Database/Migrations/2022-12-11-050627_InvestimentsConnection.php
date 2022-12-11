<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InvestimentsConnection extends Migration
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
            "investiment_id" => [
                "type" => "INT",
            ],
            "user_id" => [
                "type" => "INT",
            ],
            "investiment_amount" => [
                "type" => "INT",
            ]
           
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("investiments_connection");
    }

    public function down()
    {
        $this->forge->dropTable("investiments_connection");
    }
}
