<div class="trainingSessions form card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Edit Training Session</h3>
        <?= $this->Html->link('Back', ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
    </div>
    <div class="card-body">
        <?= $this->Form->create($trainingSession) ?>
        <div class="form-row">
            <div class="form-group col-md-6">
                <?= $this->Form->control('title', ['class' => 'form-control']) ?>
            </div>
            <div class="form-group col-md-6">
                <?= $this->Form->control('instructor_id', [
                    'label' => 'Instructor',
                    'options' => $users,
                    'class' => 'form-control'
                ]) ?>
            </div>
        </div>
        <div class="form-group">
            <?= $this->Form->control('description', ['type' => 'textarea', 'class' => 'form-control']) ?>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <?= $this->Form->control('start_date', ['type' => 'datetime', 'class' => 'form-control']) ?>
            </div>
            <div class="form-group col-md-6">
                <?= $this->Form->control('end_date', ['type' => 'datetime', 'class' => 'form-control']) ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <?= $this->Form->control('max_participants', ['class' => 'form-control']) ?>
            </div>
            <div class="form-group col-md-4">
                <?= $this->Form->control('status', [
                    'type' => 'select',
                    'options' => $statuses,
                    'class' => 'form-control'
                ]) ?>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
        <?= $this->Form->postLink('Delete', ['action' => 'delete', $trainingSession->id], [
            'confirm' => 'Are you sure?',
            'class' => 'btn btn-danger'
        ]) ?>
        <div>
            <?= $this->Html->link('Cancel', ['action' => 'index'], ['class' => 'btn btn-secondary mr-2']) ?>
            <?= $this->Form->button('Save Changes', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?= $this->Form->end() ?>
</div>

