<div class="trainingSessions index card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Training Sessions</h3>
        <?= $this->Html->link('New Session', ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('title', 'Title') ?></th>
                    <th><?= $this->Paginator->sort('Users.name', 'Instructor') ?></th>
                    <th><?= $this->Paginator->sort('start_date', 'Start') ?></th>
                    <th><?= $this->Paginator->sort('end_date', 'End') ?></th>
                    <th><?= $this->Paginator->sort('status', 'Status') ?></th>
                    <th class="actions text-right"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trainingSessions as $trainingSession): ?>
                    <tr>
                        <td><?= h($trainingSession->title) ?></td>
                        <td><?= h($trainingSession->user->name ?? 'N/A') ?></td>
                        <td><?= $trainingSession->start_date ? $trainingSession->start_date->i18nFormat('yyyy-MM-dd HH:mm') : '' ?></td>
                        <td><?= $trainingSession->end_date ? $trainingSession->end_date->i18nFormat('yyyy-MM-dd HH:mm') : '' ?></td>
                        <td>
                            <span class="badge badge-info"><?= h(ucwords(str_replace('_', ' ', $trainingSession->status))) ?></span>
                        </td>
                        <td class="actions text-right">
                            <?= $this->Html->link('View', ['action' => 'view', $trainingSession->id], ['class' => 'btn btn-sm btn-secondary']) ?>
                            <?= $this->Html->link('Edit', ['action' => 'edit', $trainingSession->id], ['class' => 'btn btn-sm btn-primary']) ?>
                            <?= $this->Form->postLink('Delete', ['action' => 'delete', $trainingSession->id], [
                                'confirm' => 'Are you sure?',
                                'class' => 'btn btn-sm btn-danger'
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <div>
            <?= $this->Paginator->counter(['format' => 'Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total']) ?>
        </div>
        <div>
            <ul class="pagination mb-0">
                <?= $this->Paginator->first('<<') ?>
                <?= $this->Paginator->prev('<') ?>
                <?= $this->Paginator->numbers() ?>
                <?= $this->Paginator->next('>') ?>
                <?= $this->Paginator->last('>>') ?>
            </ul>
        </div>
    </div>
</div>

