<?php
$breadcrumb = [__("Settings"), __("Partners"), __("Commission Rules")];

echo $this->element("global/breadcrumb", ['breadcrumb' => $breadcrumb]);
?>

<div class="kt-container  kt-grid__item kt-grid__item--fluid" data-sticky-container>
  <div class="kt-grid kt-grid--desktop kt-grid--ver kt-grid--ver-desktop kt-app">
    <div class="kt-grid__item kt-grid__item--fluid kt-app__content">
      <div class="row">
        <div class="col-xl-12">
          <?php echo $this->element("Flash/success_error"); ?>
          <div class="kt-portlet mt-3">
            <div class="kt-portlet__head">
              <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title"><?php echo __('Commission Rules'); ?></h3>
              </div>
              <div class="kt-portlet__head-toolbar">
                <?php echo $this->Html->link(__('Add Rule'), ['action' => 'add'], ['class' => 'btn btn-success btn-sm']); ?>
              </div>
            </div>
            <div class="kt-portlet__body">
              <div class="table-responsive">
                <table class="table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th><?php echo __('ID'); ?></th>
                      <th><?php echo __('Partner'); ?></th>
                      <th><?php echo __('Product Category'); ?></th>
                      <th><?php echo __('Rate (%)'); ?></th>
                      <th><?php echo __('Min Amount'); ?></th>
                      <th><?php echo __('Max Amount'); ?></th>
                      <th class="text-center"><?php echo __('Actions'); ?></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($commissionRules)) : ?>
                      <?php foreach ($commissionRules as $rule) : ?>
                        <tr>
                          <td><?php echo (int)$rule->id; ?></td>
                          <td><?php echo !empty($rule->partner) ? h(trim(($rule->partner->companyname ?? '') ?: (($rule->partner->firstname ?? '') . ' ' . ($rule->partner->lastname ?? '')))) : __('N/A'); ?></td>
                          <td><?php echo !empty($rule->product) ? h($rule->product->name) : __('N/A'); ?></td>
                          <td><?php echo h(number_format((float)$rule->rate, 2)); ?></td>
                          <td><?php echo h(number_format((float)$rule->min_amount, 2)); ?></td>
                          <td><?php echo h(number_format((float)$rule->max_amount, 2)); ?></td>
                          <td class="text-center">
                            <?php echo $this->Html->link(__('View'), ['action' => 'view', $rule->id], ['class' => 'btn btn-sm btn-primary']); ?>
                            <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $rule->id], ['class' => 'btn btn-sm btn-warning']); ?>
                            <?php echo $this->Form->postLink(__('Delete'), ['action' => 'delete', $rule->id], ['class' => 'btn btn-sm btn-danger', 'confirm' => __('Are you sure you want to delete this rule?')]); ?>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else : ?>
                      <tr>
                        <td colspan="7" class="text-center"><?php echo __('No commission rules found.'); ?></td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
              <?php echo $this->element('global/pagination'); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
