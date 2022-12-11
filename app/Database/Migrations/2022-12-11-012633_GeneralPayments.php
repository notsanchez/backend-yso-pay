<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GeneralPayments extends Migration
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
            "payment_id" => [
                "type" => "VARCHAR",
                "constraint" => 50,
            ],
            "receipt_user_id" => [
                "type" => "INT",
                "null" => false
            ],
            "payment_description" => [
                "type" => "VARCHAR",
                "constraint" => 50,
                "null" => false
            ],
            "payment_value" => [
                "type" => "FLOAT",
                "null" => false
            ],
            "paid_by_id" => [
                "type" => "INT",
                "null" => true
            ],
            "paid_date" => [
                "type" => "VARCHAR",
                "constraint" => 50,
                "null" => true
            ],
            "is_paid" => [
                "type" => "BOOL",
            ],
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("general_payments");
    }

    public function down()
    {
        $this->forge->dropTable("general_payments");
    }
}
