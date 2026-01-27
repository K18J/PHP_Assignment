<?php
namespace App\Controller;

use Cake\Controller\Controller;

/**
 * Base application controller.
 *
 * @property \Cake\Controller\Component\FlashComponent $Flash
 * @property \Cake\Controller\Component\RequestHandlerComponent $RequestHandler
 * @property \Cake\Controller\Component\PaginatorComponent $Paginator
 */
class AppController extends Controller
{
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('RequestHandler');
        $this->loadComponent('Flash');
        $this->loadComponent('Paginator');
    }
}

