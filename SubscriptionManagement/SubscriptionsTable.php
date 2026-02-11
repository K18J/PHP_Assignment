<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class SubscriptionsTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('subscriptions');
        $this->setPrimaryKey('id');
        $this->setDisplayField('id');

        $this->belongsTo('Customers', [
            'className' => 'Clients',
            'foreignKey' => 'customer_id',
            'joinType' => 'LEFT'
        ]);

        $this->belongsTo('SubscriptionPlans', [
            'foreignKey' => 'plan_id',
            'joinType' => 'LEFT'
        ]);
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('customer_id')
            ->requirePresence('customer_id', 'create')
            ->notEmptyString('customer_id', __('Customer is required.'));

        $validator
            ->integer('plan_id')
            ->requirePresence('plan_id', 'create')
            ->notEmptyString('plan_id', __('Plan is required.'));

        $validator
            ->inList('status', ['trial', 'active', 'suspended', 'cancelled', 'expired'])
            ->requirePresence('status', 'create')
            ->notEmptyString('status', __('Status is required.'));

        $validator
            ->date('start_date')
            ->requirePresence('start_date', 'create')
            ->notEmptyDate('start_date', __('Start date is required.'));

        $validator
            ->date('end_date')
            ->requirePresence('end_date', 'create')
            ->notEmptyDate('end_date', __('End date is required.'));

        $validator
            ->boolean('auto_renew')
            ->requirePresence('auto_renew', 'create')
            ->notEmptyString('auto_renew', __('Auto renew is required.'));

        return $validator;
    }
}
