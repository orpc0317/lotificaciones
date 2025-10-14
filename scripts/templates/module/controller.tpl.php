<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\{{Name}}Model;

class {{Name}}Controller extends BaseController
{
    public function index()
    {
        // Render the module page
        return view('{{name}}', []);
    }

    public function ajax()
    {
        // Minimal server-side response for DataTables when scaffolding with API storage.
        // Update this method to fetch actual data from your API or DB.
        $columns = {{columns_php}};
        $data = [];
        return $this->response->setJSON(['columns' => $columns, 'data' => $data]);
    }
}
