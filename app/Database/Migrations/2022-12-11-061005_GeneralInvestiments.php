<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GeneralInvestiments extends Migration
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
            "investiment_type" => [
                "type" => "VARCHAR",
                "constraint" => 50,
                "null" => false
            ],
            "investiment_name" => [
                "type" => "VARCHAR",
                "constraint" => 50,
                "null" => false
            ],
            "income_percentage_per_year" => [
                "type" => "INT",
            ],
            "days_for_withdrawal" => [
                "type" => "INT",
            ],
           
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("general_investiments");
    }

    public function down()
    {
        $this->forge->dropTable("general_investiments");
    }
}
