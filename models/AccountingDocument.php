<?php defined('APP_NAME') or exit('No direct script access allowed');
require_once __DIR__ . '/BaseModel.php';

class AccountingDocument extends BaseModel {
    protected $table = 'accounting_documents';
}
