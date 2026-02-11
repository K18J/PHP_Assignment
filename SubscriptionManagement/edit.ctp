<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-1"><?php echo __('Edit Subscription'); ?></h3>
    <div class="text-muted"><?php echo __('Update subscription details.'); ?></div>
  </div>
  <div>
    <?php echo $this->Html->link(__('Back to list'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary btn-sm']); ?>
  </div>
</div>

<?php echo $this->Flash->render(); ?>

<div class="card shadow-sm">
  <div class="card-body">
    <?php echo $this->Form->create($subscription); ?>
    <div class="row g-3">
      <div class="col-md-6">
        <?php echo $this->Form->control('customer_id', ['label' => __('Customer'), 'options' => $customers, 'class' => 'form-select', 'required' => true]); ?>
      </div>
      <div class="col-md-6">
        <?php echo $this->Form->control('plan_id', ['label' => __('Plan'), 'options' => $plans, 'class' => 'form-select', 'required' => true]); ?>
      </div>
      <div class="col-md-4">
        <?php echo $this->Form->control('status', ['label' => __('Status'), 'options' => $statuses, 'class' => 'form-select', 'required' => true]); ?>
      </div>
      <div class="col-md-4">
        <?php echo $this->Form->control('start_date', ['label' => __('Start Date'), 'type' => 'date', 'class' => 'form-control', 'required' => true]); ?>
      </div>
      <div class="col-md-4">
        <?php echo $this->Form->control('end_date', ['label' => __('End Date'), 'type' => 'date', 'class' => 'form-control', 'required' => true]); ?>
      </div>
      <div class="col-md-4">
        <?php echo $this->Form->control('auto_renew', ['label' => __('Auto Renew'), 'type' => 'checkbox']); ?>
      </div>
    </div>
    <div class="mt-4">
      <button type="submit" class="btn btn-success btn-sm"><?php echo __('Update'); ?></button>
      <?php echo $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary btn-sm ms-2']); ?>
    </div>
    <?php echo $this->Form->end(); ?>
  </div>
</div>
