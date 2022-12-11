<?php

namespace App\Models;

use CodeIgniter\Model;

class GeneralUserModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'general_user';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        "acc_id", 
        "account_number", 
        "user_fullName", 
        "user_profilePicture", 
        "user_docPicture", 
        "user_CPF", 
        "account_password", 
        "user_email", 
        "user_phone", 
        "user_randomKey", 
        "user_balance", 
        "isValid"
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
