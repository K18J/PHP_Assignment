<div class="trainingSessions view card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><?= h($trainingSession->title) ?></h3>
        <div>
            <?= $this->Html->link('Edit', ['action' => 'edit', $trainingSession->id], ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link('Back', ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">Instructor</dt>
            <dd class="col-sm-9"><?= h($trainingSession->user->name ?? 'N/A') ?></dd>

            <dt class="col-sm-3">Start</dt>
            <dd class="col-sm-9"><?= $trainingSession->start_date ? $trainingSession->start_date->i18nFormat('yyyy-MM-dd HH:mm') : '' ?></dd>

            <dt class="col-sm-3">End</dt>
            <dd class="col-sm-9"><?= $trainingSession->end_date ? $trainingSession->end_date->i18nFormat('yyyy-MM-dd HH:mm') : '' ?></dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9"><span class="badge badge-info"><?= h(ucwords(str_replace('_', ' ', $trainingSession->status))) ?></span></dd>

            <dt class="col-sm-3">Max Participants</dt>
            <dd class="col-sm-9"><?= h($trainingSession->max_participants) ?></dd>

            <dt class="col-sm-3">Duration (hours)</dt>
            <dd class="col-sm-9"><?= $trainingSession->duration !== null ? $trainingSession->duration : 'N/A' ?></dd>

            <dt class="col-sm-3">Is Full?</dt>
            <dd class="col-sm-9"><?= $trainingSession->is_full ? 'Yes' : 'No' ?></dd>
        </dl>

        <?php if (!empty($trainingSession->description)): ?>
            <h5>Description</h5>
            <p><?= nl2br(h($trainingSession->description)) ?></p>
        <?php endif; ?>

        <?php if (!empty($trainingSession->registrations)): ?>
            <h5 class="mt-4">Registrations</h5>
            <ul class="list-group">
                <?php foreach ($trainingSession->registrations as $registration): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= h($registration->user->name ?? 'Unknown user') ?>
                        <span class="text-muted"><?= $registration->created ? $registration->created->i18nFormat('yyyy-MM-dd HH:mm') : '' ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

