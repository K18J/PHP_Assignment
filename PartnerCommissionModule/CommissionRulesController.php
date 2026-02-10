<?php
namespace App\Controller;

use Cake\Datasource\Exception\RecordNotFoundException;

class CommissionRulesController extends AppController
{
    public $paginate = ['limit' => 20];

    public function initialize()
    {
        parent::initialize();
        $this->getEventManager()->off($this->Security);
    }

    public function index()
    {
        $query = $this->CommissionRules->find()
            ->contain(['Partners', 'Products'])
            ->order(['CommissionRules.id' => 'DESC']);

        $commissionRules = $this->paginate($query);
        $this->set(compact('commissionRules'));
    }

    public function view($id = null)
    {
        try {
            $commissionRule = $this->CommissionRules->get($id, [
                'contain' => ['Partners', 'Products']
            ]);
        } catch (RecordNotFoundException $ex) {
            $this->Flash->error(__('Commission rule not found.'));
            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('commissionRule'));
    }

    public function add()
    {
        $commissionRule = $this->CommissionRules->newEntity();

        if ($this->request->is('post')) {
            $commissionRule = $this->CommissionRules->patchEntity($commissionRule, $this->request->getData());

            if ($this->CommissionRules->save($commissionRule)) {
                $this->Flash->success(__('Commission rule created successfully.'));
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Unable to create commission rule. Please check the form.'));
        }

        $partners = $this->getPartnerOptions();
        $productCategories = $this->getProductCategoryOptions();

        $this->set(compact('commissionRule', 'partners', 'productCategories'));
    }

    public function edit($id = null)
    {
        try {
            $commissionRule = $this->CommissionRules->get($id);
        } catch (RecordNotFoundException $ex) {
            $this->Flash->error(__('Commission rule not found.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $commissionRule = $this->CommissionRules->patchEntity($commissionRule, $this->request->getData());

            if ($this->CommissionRules->save($commissionRule)) {
                $this->Flash->success(__('Commission rule updated successfully.'));
                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Unable to update commission rule. Please check the form.'));
        }

        $partners = $this->getPartnerOptions();
        $productCategories = $this->getProductCategoryOptions();

        $this->set(compact('commissionRule', 'partners', 'productCategories'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        try {
            $commissionRule = $this->CommissionRules->get($id);
        } catch (RecordNotFoundException $ex) {
            $this->Flash->error(__('Commission rule not found.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->CommissionRules->delete($commissionRule)) {
            $this->Flash->success(__('Commission rule deleted successfully.'));
        } else {
            $this->Flash->error(__('Unable to delete commission rule.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    private function getPartnerOptions()
    {
        $this->loadModel('Clients');
        $partners = $this->Clients->getRacknapPartners();

        $options = [];
        foreach ($partners as $partner) {
            $label = $partner['companyname'];
            if (empty($label)) {
                $label = trim(($partner['firstname'] ?? '') . ' ' . ($partner['lastname'] ?? ''));
            }

            $options[$partner['id']] = $label !== '' ? $label : __('Partner #{0}', $partner['id']);
        }

        return $options;
    }

    private function getProductCategoryOptions()
    {
        $this->loadModel('ProductType');
        return $this->ProductType->find('list', [
            'keyField' => 'id',
            'valueField' => 'name'
        ])->order(['name' => 'ASC'])->toArray();
    }
}
