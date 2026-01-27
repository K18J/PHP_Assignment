<div class="trainingSessions form card">
    <div class="card-header">
        <h3 class="card-title">Add Training Session</h3>
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
        <?= $this->Html->link('Cancel', ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
        <?= $this->Form->button('Save Session', ['class' => 'btn btn-primary']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

