<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TemplateModel;

class TemplateController extends BaseController
{
    public function index()
    {
        // Render the module page
        return view('template', []);
    }

    public function ajax()
    {
        // Example JSON response for DataTables; adapt to your API or DB
        $data = [
            // Provide sample columns/data here or integrate with an API
        ];
        return $this->response->setJSON(['columns' => [], 'data' => $data]);
    }
}
