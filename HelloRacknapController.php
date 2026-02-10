<?php
namespace App\Controller;

use Cake\Http\Response;

class HelloRacknapController extends AppController
{
    public function index(): Response
    {
        return $this->response->withStringBody('Hello Racknap');
    }
}