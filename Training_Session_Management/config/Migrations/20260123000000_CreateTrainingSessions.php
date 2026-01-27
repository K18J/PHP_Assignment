<?php
use Migrations\BaseMigration;

class CreateTrainingSessions extends BaseMigration
{
    public function change()
    {
        $table = $this->table('training_sessions');
        $table
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('instructor_id', 'integer', ['null' => false])
            ->addColumn('start_date', 'datetime', ['null' => false])
            ->addColumn('end_date', 'datetime', ['null' => false])
            ->addColumn('max_participants', 'integer', ['default' => 20, 'null' => false])
            ->addColumn('status', 'enum', [
                'values' => ['scheduled', 'in_progress', 'completed', 'cancelled'],
                'null' => false,
                'default' => 'scheduled',
            ])
            ->addColumn('created', 'datetime', ['null' => true])
            ->addColumn('modified', 'datetime', ['null' => true])
            ->addForeignKey('instructor_id', 'users', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
            ])
            ->create();
    }
}

