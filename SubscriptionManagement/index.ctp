<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-1"><?php echo __('Subscriptions'); ?></h3>
    <div class="text-muted"><?php echo __('Manage customer subscriptions and status changes.'); ?></div>
  </div>
  <div>
    <?php echo $this->Html->link(__('Add Subscription'), ['action' => 'add'], ['class' => 'btn btn-success btn-sm']); ?>
  </div>
</div>

<?php echo $this->Flash->render(); ?>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <?php echo $this->Form->create(null, ['type' => 'get', 'class' => 'row g-2 align-items-end']); ?>
      <div class="col-md-3">
        <?php echo $this->Form->control('status', ['label' => __('Status'), 'options' => $statuses, 'empty' => __('All'), 'class' => 'form-select', 'value' => $filters['status'] ?? '']); ?>
      </div>
      <div class="col-md-3">
        <?php echo $this->Form->control('plan_id', ['label' => __('Plan'), 'options' => $plans, 'empty' => __('All'), 'class' => 'form-select', 'value' => $filters['plan_id'] ?? '']); ?>
      </div>
      <div class="col-md-3">
        <?php echo $this->Form->control('auto_renew', ['label' => __('Auto Renew'), 'options' => ['1' => __('Yes'), '0' => __('No')], 'empty' => __('All'), 'class' => 'form-select', 'value' => $filters['auto_renew'] ?? '']); ?>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary btn-sm w-100"><?php echo __('Filter'); ?></button>
      </div>
    <?php echo $this->Form->end(); ?>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th><?php echo __('ID'); ?></th>
            <th><?php echo __('Customer'); ?></th>
            <th><?php echo __('Plan'); ?></th>
            <th><?php echo __('Status'); ?></th>
            <th><?php echo __('Start'); ?></th>
            <th><?php echo __('End'); ?></th>
            <th><?php echo __('Auto Renew'); ?></th>
            <th class="text-center"><?php echo __('Actions'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($subscriptions)) : ?>
            <?php foreach ($subscriptions as $subscription) : ?>
              <tr>
                <td><?php echo (int)$subscription->id; ?></td>
                <td><?php echo !empty($subscription->customer) ? h($subscription->customer->email ?? $subscription->customer->companyname) : __('N/A'); ?></td>
                <td><?php echo !empty($subscription->subscription_plan) ? h($subscription->subscription_plan->name) : __('N/A'); ?></td>
                <td><?php echo h(ucfirst($subscription->status)); ?></td>
                <td><?php echo h($subscription->start_date); ?></td>
                <td><?php echo h($subscription->end_date); ?></td>
                <td><?php echo $subscription->auto_renew ? __('Yes') : __('No'); ?></td>
                <td class="text-center">
                  <?php echo $this->Html->link(__('View'), ['action' => 'view', $subscription->id], ['class' => 'btn btn-sm btn-primary']); ?>
                  <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $subscription->id], ['class' => 'btn btn-sm btn-warning']); ?>
                  <?php echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $subscription->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete this subscription?')]); ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else : ?>
            <tr>
              <td colspan="8" class="text-center"><?php echo __('No subscriptions found.'); ?></td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php echo $this->element('global/pagination'); ?>
  </div>
</div>
