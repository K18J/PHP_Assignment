<?php
namespace App\Controller;

/**
 * @property \App\Model\Table\TrainingSessionsTable $TrainingSessions
 */
class TrainingSessionsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('Paginator');
    }

    public function index()
    {
        $query = $this->TrainingSessions
            ->find()
            ->contain(['Users'])
            ->orderBy(['start_date' => 'ASC']);

        $trainingSessions = $this->paginate($query, [
            'limit' => 10,
            'sortableFields' => ['title', 'start_date', 'end_date', 'status', 'Users.name'],
        ]);

        $this->set(compact('trainingSessions'));
    }

    public function view($id = null)
    {
        $trainingSession = $this->TrainingSessions->get($id, [
            'contain' => ['Users', 'Registrations.Users'],
        ]);

        $this->set(compact('trainingSession'));
    }

    public function add()
    {
        $trainingSession = $this->TrainingSessions->newEmptyEntity();
        if ($this->request->is('post')) {
            $trainingSession = $this->TrainingSessions->patchEntity($trainingSession, $this->request->getData());
            if ($this->TrainingSessions->save($trainingSession)) {
                $this->Flash->success('The training session has been saved.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Unable to save the training session. Please try again.');
        }

        $users = $this->TrainingSessions->Users->find('list')->all();
        $statuses = [
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $this->set(compact('trainingSession', 'users', 'statuses'));
    }

    public function edit($id = null)
    {
        $trainingSession = $this->TrainingSessions->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $trainingSession = $this->TrainingSessions->patchEntity($trainingSession, $this->request->getData());
            if ($this->TrainingSessions->save($trainingSession)) {
                $this->Flash->success('The training session has been updated.');
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error('Unable to update the training session. Please try again.');
        }

        $users = $this->TrainingSessions->Users->find('list')->all();
        $statuses = [
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        $this->set(compact('trainingSession', 'users', 'statuses'));
    }

    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $trainingSession = $this->TrainingSessions->get($id);

        if ($this->TrainingSessions->delete($trainingSession)) {
            $this->Flash->success('The training session has been deleted.');
        } else {
            $this->Flash->error('Unable to delete the training session. Please try again.');
        }

        return $this->redirect(['action' => 'index']);
    }
}

