<?php
namespace App\Model\Table;

use Cake\I18n\FrozenTime;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class TrainingSessionsTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('training_sessions');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'instructor_id',
            'joinType' => 'INNER',
        ]);

        $this->hasMany('Registrations', [
            'foreignKey' => 'training_session_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('title', 'create')
            ->notEmptyString('title', 'Title is required')
            ->dateTime('start_date', [
                'message' => 'Start date must be a valid datetime',
            ])
            ->dateTime('end_date', [
                'message' => 'End date must be a valid datetime',
            ])
            ->integer('max_participants')
            ->greaterThanOrEqual('max_participants', 1, 'Must allow at least one participant')
            ->inList('status', ['scheduled', 'in_progress', 'completed', 'cancelled'], 'Invalid status');

        $validator->add('start_date', 'future', [
            'rule' => function ($value) {
                if (!$value) {
                    return false;
                }
                return new FrozenTime($value) > FrozenTime::now();
            },
            'message' => 'Start date must be in the future',
        ]);

        $validator->add('end_date', 'afterStart', [
            'rule' => function ($value, $context) {
                if (!$value || empty($context['data']['start_date'])) {
                    return false;
                }
                $end = new FrozenTime($value);
                $start = new FrozenTime($context['data']['start_date']);
                return $end > $start;
            },
            'message' => 'End date must be after start date',
        ]);

        return $validator;
    }

    public function findUpcoming(Query $query, array $options)
    {
        return $query
            ->where(['start_date >' => FrozenTime::now()])
            ->orderBy(['start_date' => 'ASC']);
    }

    public function findByInstructor(Query $query, array $options)
    {
        $instructorId = $options['instructor_id'] ?? null;
        if (!$instructorId) {
            return $query;
        }

        return $query->where(['instructor_id' => $instructorId]);
    }
}

