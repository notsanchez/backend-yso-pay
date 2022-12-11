<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class GeneralUserMigration extends Migration
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
            "acc_id" => [
                "type" => "INT",
                "unique" => true,
                "null" => false
            ],
            "account_number" => [
                "type" => "INT",
                "unique" => true,
                "null" => false
            ],
            "user_fullName" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "null" => false
            ],
            "user_profilePicture" => [
                "type" => "TEXT",
            ],
            "user_docPicture" => [
                "type" => "TEXT"
            ],
            "user_CPF" => [
                "type" => "VARCHAR",
                "constraint" => 11,
                "unique" => true,
                "null" => false
            ],
            "account_password" => [
                "type" => "VARCHAR",
                "constraint" => 255,
            ],
            "user_email" => [
                "type" => "VARCHAR",
                "constraint" => 255,
                "unique" => true,
                "null" => false
            ],
            "user_phone" => [
                "type" => "INT",
                "constraint" => 20,
                "unique" => true,
                "null" => false
            ],
            "user_randomKey" => [
                "type" => "VARCHAR",
                "constraint" => 16,
                "unique" => true,
                "null" => false
            ],
            "user_balance" => [
                "type" => "FLOAT",
            ],
            "isValid" => [
              "type" => "BOOL"
            ]
        ]);
        $this->forge->addPrimaryKey("id");
        $this->forge->createTable("general_user");
    }

    public function down()
    {
        $this->forge->dropTable("user");
    }
}
