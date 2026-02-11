<?php
namespace App\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;

class MySubscriptionsController extends AppController
{
    public $paginate = ['limit' => 20];

    public function initialize()
    {
        parent::initialize();
        $this->viewBuilder()->setLayout('member_simple');
    }

    public function index()
    {
        $customerId = $this->getRequest()->getSession()->read('Auth.User.id');

        $query = $this->Subscriptions->find()
            ->contain(['SubscriptionPlans'])
            ->where(['Subscriptions.customer_id' => (int)$customerId])
            ->order(['Subscriptions.id' => 'DESC']);

        $subscriptions = $this->paginate($query);
        $this->set(compact('subscriptions'));
    }

    public function view($id = null)
    {
        $customerId = $this->getRequest()->getSession()->read('Auth.User.id');

        try {
            $subscription = $this->Subscriptions->get($id, [
                'contain' => ['SubscriptionPlans'],
                'conditions' => ['Subscriptions.customer_id' => (int)$customerId]
            ]);
        } catch (RecordNotFoundException $ex) {
            $this->Flash->error(__('Subscription not found.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('subscription'));
    }

    public function requestCancel($id = null)
    {
        $this->request->allowMethod(['post']);
        $customerId = $this->getRequest()->getSession()->read('Auth.User.id');

        try {
            $subscription = $this->Subscriptions->get($id, [
                'conditions' => ['Subscriptions.customer_id' => (int)$customerId]
            ]);
        } catch (RecordNotFoundException $ex) {
            $this->Flash->error(__('Subscription not found.'));
            return $this->redirect(['action' => 'index']);
        }

        $subscription->status = 'cancelled';
        if ($this->Subscriptions->save($subscription)) {
            $this->Flash->success(__('Cancellation request submitted.'));
        } else {
            $this->Flash->error(__('Unable to cancel subscription.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    public function toggleAutoRenew($id = null)
    {
        $this->request->allowMethod(['post']);
        $customerId = $this->getRequest()->getSession()->read('Auth.User.id');

        try {
            $subscription = $this->Subscriptions->get($id, [
                'conditions' => ['Subscriptions.customer_id' => (int)$customerId]
            ]);
        } catch (RecordNotFoundException $ex) {
            $this->Flash->error(__('Subscription not found.'));
            return $this->redirect(['action' => 'index']);
        }

        $subscription->auto_renew = $subscription->auto_renew ? 0 : 1;
        if ($this->Subscriptions->save($subscription)) {
            $this->Flash->success(__('Auto-renew updated.'));
        } else {
            $this->Flash->error(__('Unable to update auto-renew.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
