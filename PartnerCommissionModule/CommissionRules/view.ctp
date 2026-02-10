<?php
$breadcrumb = [__("Settings"), __("Partners"), ["link" => $this->Url->build(['controller' => 'CommissionRules', 'action' => 'index']), "text" => __("Commission Rules")], __("View")];

echo $this->element("global/breadcrumb", ['breadcrumb' => $breadcrumb]);
?>

<div class="kt-grid__item kt-grid__item--fluid kt-app__content">
  <div class="kt-grid kt-grid--desktop kt-grid--ver kt-grid--ver-desktop kt-app">
    <div class="kt-grid__item kt-grid__item--fluid">
      <div class="row">
        <div class="col-xl-12">
          <?php echo $this->element("Flash/success_error"); ?>
          <div class="kt-portlet mt-3">
            <div class="kt-portlet__head">
              <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title"><?php echo __('Commission Rule'); ?></h3>
              </div>
              <div class="kt-portlet__head-toolbar">
                <?php echo $this->Html->link(__('Edit'), ['action' => 'edit', $commissionRule->id], ['class' => 'btn btn-warning btn-sm']); ?>
                <?php echo $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-secondary btn-sm']); ?>
              </div>
            </div>
            <div class="kt-portlet__body">
              <table class="table table-bordered">
                <tbody>
                  <tr>
                    <th><?php echo __('ID'); ?></th>
                    <td><?php echo (int)$commissionRule->id; ?></td>
                  </tr>
                  <tr>
                    <th><?php echo __('Partner'); ?></th>
                    <td><?php echo !empty($commissionRule->partner) ? h(trim(($commissionRule->partner->companyname ?? '') ?: (($commissionRule->partner->firstname ?? '') . ' ' . ($commissionRule->partner->lastname ?? '')))) : __('N/A'); ?></td>
                  </tr>
                  <tr>
                    <th><?php echo __('Product Category'); ?></th>
                    <td><?php echo !empty($commissionRule->product) ? h($commissionRule->product->name) : __('N/A'); ?></td>
                  </tr>
                  <tr>
                    <th><?php echo __('Rate (%)'); ?></th>
                    <td><?php echo h(number_format((float)$commissionRule->rate, 2)); ?></td>
                  </tr>
                  <tr>
                    <th><?php echo __('Minimum Amount'); ?></th>
                    <td><?php echo h(number_format((float)$commissionRule->min_amount, 2)); ?></td>
                  </tr>
                  <tr>
                    <th><?php echo __('Maximum Amount'); ?></th>
                    <td><?php echo h(number_format((float)$commissionRule->max_amount, 2)); ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
