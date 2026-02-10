<?php
namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CommissionRulesTable extends Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('commission_rules');
        $this->setPrimaryKey('id');
        $this->setDisplayField('id');

        $this->belongsTo('Partners', [
            'className' => 'Clients',
            'foreignKey' => 'partner_id',
            'joinType' => 'INNER'
        ]);

        $this->belongsTo('Products', [
            'className' => 'ProductType',
            'foreignKey' => 'product_category',
            'joinType' => 'INNER'
        ]);
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('partner_id')
            ->requirePresence('partner_id', 'create')
            ->notEmptyString('partner_id', __('Partner is required.'));

        $validator
            ->integer('product_category')
            ->requirePresence('product_category', 'create')
            ->notEmptyString('product_category', __('Product category is required.'));

        $validator
            ->numeric('rate')
            ->requirePresence('rate', 'create')
            ->notEmptyString('rate', __('Rate is required.'))
            ->add('rate', 'range', [
                'rule' => function ($value) {
                    return is_numeric($value) && $value >= 0 && $value <= 100;
                },
                'message' => __('Rate must be between 0 and 100.')
            ]);

        $validator
            ->numeric('min_amount')
            ->requirePresence('min_amount', 'create')
            ->notEmptyString('min_amount', __('Minimum amount is required.'))
            ->add('min_amount', 'positive', [
                'rule' => function ($value) {
                    return is_numeric($value) && $value >= 0;
                },
                'message' => __('Minimum amount must be positive.')
            ]);

        $validator
            ->numeric('max_amount')
            ->requirePresence('max_amount', 'create')
            ->notEmptyString('max_amount', __('Maximum amount is required.'))
            ->add('max_amount', 'positive', [
                'rule' => function ($value) {
                    return is_numeric($value) && $value > 0;
                },
                'message' => __('Maximum amount must be positive.')
            ])
            ->add('max_amount', 'greaterThanMin', [
                'rule' => function ($value, $context) {
                    if (!isset($context['data']['min_amount']) || $context['data']['min_amount'] === '') {
                        return true;
                    }

                    return is_numeric($value) && is_numeric($context['data']['min_amount']) && $value >= $context['data']['min_amount'];
                },
                'message' => __('Maximum amount must be greater than or equal to minimum amount.')
            ]);

        return $validator;
    }
}
