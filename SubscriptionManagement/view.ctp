<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-1"><?php echo __('Subscription Details'); ?></h3>
    <div class="text-muted"><?php echo __('Review subscription information.'); ?></div>
  </div>
  <div>
    <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $subscription->id], ['class' => 'btn btn-warning btn-sm']); ?>
    <?php echo $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-secondary btn-sm ms-2']); ?>
  </div>
</div>

<?php echo $this->Flash->render(); ?>

<div class="card shadow-sm">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-6">
        <div class="text-muted small mb-1"><?php echo __('Customer'); ?></div>
        <div class="fw-semibold">
          <?php echo !empty($subscription->customer) ? h($subscription->customer->email ?? $subscription->customer->companyname) : __('N/A'); ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="text-muted small mb-1"><?php echo __('Plan'); ?></div>
        <div class="fw-semibold">
          <?php echo !empty($subscription->subscription_plan) ? h($subscription->subscription_plan->name) : __('N/A'); ?>
        </div>
      </div>
      <div class="col-md-4">
        <div class="text-muted small mb-1"><?php echo __('Status'); ?></div>
        <div class="fw-semibold"><?php echo h(ucfirst($subscription->status)); ?></div>
      </div>
      <div class="col-md-4">
        <div class="text-muted small mb-1"><?php echo __('Start Date'); ?></div>
        <div class="fw-semibold"><?php echo h($subscription->start_date); ?></div>
      </div>
      <div class="col-md-4">
        <div class="text-muted small mb-1"><?php echo __('End Date'); ?></div>
        <div class="fw-semibold"><?php echo h($subscription->end_date); ?></div>
      </div>
      <div class="col-md-4">
        <div class="text-muted small mb-1"><?php echo __('Auto Renew'); ?></div>
        <div class="fw-semibold"><?php echo $subscription->auto_renew ? __('Yes') : __('No'); ?></div>
      </div>
    </div>
  </div>
</div>
