<?php
$breadcrumb = [__("Settings"), __("Partners"), ["link" => $this->Url->build(['controller' => 'CommissionRules', 'action' => 'index']), "text" => __("Commission Rules")], __("Edit")];

echo $this->element("global/breadcrumb", ['breadcrumb' => $breadcrumb]);
?>

<div class="kt-grid__item kt-grid__item--fluid kt-app__content">
  <div class="kt-grid kt-grid--desktop kt-grid--ver kt-grid--ver-desktop kt-app">
    <div class="kt-grid__item kt-grid__item--fluid">
      <div class="row">
        <div class="col-xl-12">
          <?php echo $this->element("Flash/success_error"); ?>
          <div class="kt-portlet mt-3">
            <?php echo $this->Form->create($commissionRule, ['class' => 'kt-form kt-form--label-right']); ?>
            <div class="kt-portlet__body">
              <div class="row">
                <div class="form-group col-lg-6 col-md-6">
                  <?php
                    echo $this->Form->control('partner_id', [
                      'label' => __('Partner'),
                      'options' => $partners,
                      'empty' => __('Select Partner'),
                      'class' => 'form-control',
                      'required' => true
                    ]);
                  ?>
                </div>
                <div class="form-group col-lg-6 col-md-6">
                  <?php
                    echo $this->Form->control('product_category', [
                      'label' => __('Product Category'),
                      'options' => $productCategories,
                      'empty' => __('Select Category'),
                      'class' => 'form-control',
                      'required' => true
                    ]);
                  ?>
                </div>
                <div class="form-group col-lg-4 col-md-4">
                  <?php
                    echo $this->Form->control('rate', [
                      'label' => __('Rate (%)'),
                      'class' => 'form-control',
                      'type' => 'number',
                      'step' => '0.01',
                      'min' => '0',
                      'max' => '100',
                      'required' => true
                    ]);
                  ?>
                </div>
                <div class="form-group col-lg-4 col-md-4">
                  <?php
                    echo $this->Form->control('min_amount', [
                      'label' => __('Minimum Amount'),
                      'class' => 'form-control',
                      'type' => 'number',
                      'step' => '0.01',
                      'min' => '0',
                      'required' => true
                    ]);
                  ?>
                </div>
                <div class="form-group col-lg-4 col-md-4">
                  <?php
                    echo $this->Form->control('max_amount', [
                      'label' => __('Maximum Amount'),
                      'class' => 'form-control',
                      'type' => 'number',
                      'step' => '0.01',
                      'min' => '0',
                      'required' => true
                    ]);
                  ?>
                </div>
              </div>
            </div>
            <div class="kt-portlet__foot">
              <div class="kt-form__actions">
                <div class="row">
                  <div class="col-lg-12 text-center">
                    <button type="submit" class="btn btn-success btn-sm"><?php echo __('Update'); ?></button>
                    <?php echo $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary btn-sm']); ?>
                  </div>
                </div>
              </div>
            </div>
            <?php echo $this->Form->end(); ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
