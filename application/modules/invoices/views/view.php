<script type="text/javascript">

    $(function() {

        $('#btn_add_product').click(function() {
            $('#modal-placeholder').load("<?php echo site_url('products/ajax/modal_product_lookups'); ?>/" + Math.floor(Math.random()*1000));
        });

        $('#btn_add_row').click(function() {
            $('#new_row').clone().appendTo('#item_table').removeAttr('id').addClass('item').show();
        });

        <?php if (!$items) { ?>
            $('#new_row').clone().appendTo('#item_table').removeAttr('id').addClass('item').show();
        <?php } ?>

        $('#btn_create_recurring').click(function()
        {
            $('#modal-placeholder').load("<?php echo site_url('invoices/ajax/modal_create_recurring'); ?>", {invoice_id: <?php echo $invoice_id; ?>});
        });

        $('#btn_save_invoice').click(function() {
            var items = [];
            var item_order = 1;
            $('table tr.item').each(function() {
                var row = {};
                $(this).find('input,select,textarea').each(function() {
                    if ($(this).is(':checkbox')) {
                        row[$(this).attr('name')] = $(this).is(':checked');
                    } else {
                        row[$(this).attr('name')] = $(this).val();
                    }
                });
                row['item_order'] = item_order;
                item_order++;
                items.push(row);
            });
            $.post("<?php echo site_url('invoices/ajax/save'); ?>", {
                    invoice_id: <?php echo $invoice_id; ?>,
                    invoice_number: $('#invoice_number').val(),
                    invoice_date_created: $('#invoice_date_created').val(),
                    invoice_date_due: $('#invoice_date_due').val(),
                    invoice_status_id: $('#invoice_status_id').val(),
                    items: JSON.stringify(items),
                    invoice_terms: $('#invoice_terms').val(),
                    custom: $('input[name^=custom]').serializeArray()
                },
                function(data) {
                    var response = JSON.parse(data);
                    if (response.success == '1') {
                        window.location = "<?php echo site_url('invoices/view'); ?>/" + <?php echo $invoice_id; ?>;
                    }
                    else {
					    $('.control-group').removeClass('error');
					    $('div.alert[class*="alert-"]').remove();
					    var resp_errors = response.validation_errors;
					    for (var key in resp_errors) {
					        $('#' + key).parent().parent().addClass('error');
					        $('#invoice_form').prepend('<div class="alert alert-danger">'+resp_errors[key]+'</div>');
					    }
					}
                });
        });

        $('#btn_generate_pdf').click(function() {
            window.location = '<?php echo site_url('invoices/generate_pdf/' . $invoice_id); ?>';
        });

        <?php if ($invoice->is_read_only != 1) { ?>
        var fixHelper = function(e, tr) {
            var $originals = tr.children();
            var $helper = tr.clone();
            $helper.children().each(function(index) {
                $(this).width($originals.eq(index).width())
            });
            return $helper;
        };

        $("#item_table tbody").sortable({
            helper: fixHelper
        });
        <?php } ?>
    });

</script>

<?php echo $modal_delete_invoice; ?>
<?php echo $modal_add_invoice_tax; ?>

<div class="headerbar">
    <h1><?php echo lang('invoice'); ?> #<?php echo $invoice->invoice_number; ?></h1>

    <div class="pull-right <?php if ($invoice->is_read_only != 1) { ?>btn-group<?php } ?>">

        <div class="options btn-group pull-left">
            <a class="btn btn-sm btn-default dropdown-toggle"
               data-toggle="dropdown" href="#">
                <i class="fa fa-caret-down no-margin"></i> <?php echo lang('options'); ?>
            </a>
            <ul class="dropdown-menu">
                <?php if ($invoice->is_read_only != 1) { ?>
                <li>
                    <a href="#add-invoice-tax" data-toggle="modal">
                        <i class="fa fa-plus fa-margin"></i> <?php echo lang('add_invoice_tax'); ?>
                    </a>
                </li>
                <?php } ?>
                <li>
                    <a href="#" id="btn_create_credit" data-invoice-id="<?php echo $invoice_id; ?>">
                        <i class="fa fa-minus fa-margin"></i> <?php echo lang('create_credit_invoice'); ?>
                    </a>
                </li>
                <li>
                    <a href="#" class="invoice-add-payment"
                       data-invoice-id="<?php echo $invoice_id; ?>"
                       data-invoice-balance="<?php echo $invoice->invoice_balance; ?>">
                        <i class="fa fa-credit-card fa-margin"></i>
                        <?php echo lang('enter_payment'); ?>
                    </a>
                </li>
                <li>
                    <a href="#" id="btn_generate_pdf"
                       data-invoice-id="<?php echo $invoice_id; ?>">
                        <i class="fa fa-print fa-margin"></i>
                        <?php echo lang('download_pdf'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo site_url('mailer/invoice/' . $invoice->invoice_id); ?>">
                        <i class="fa fa-send fa-margin"></i>
                        <?php echo lang('send_email'); ?>
                    </a>
                </li>
                <li class="divider"></li>
                <?php if ($invoice->is_read_only != 1) { ?>
                <li>
                    <a href="#" id="btn_create_recurring"
                       data-invoice-id="<?php echo $invoice_id; ?>">
                        <i class="fa fa-repeat fa-margin"></i>
                        <?php echo lang('create_recurring'); ?>
                    </a>
                </li>
                <?php } ?>
                <li>
                    <a href="#" id="btn_copy_invoice"
                       data-invoice-id="<?php echo $invoice_id; ?>">
                        <i class="fa fa-copy fa-margin"></i>
                        <?php echo lang('copy_invoice'); ?>
                    </a>
                </li>
                <?php if ($invoice->invoice_status_id == 1 || ($this->config->item('enable_invoice_deletion') === TRUE && $invoice->is_read_only != 1)) { ?>
                <li>
                    <a href="#delete-invoice" data-toggle="modal">
                        <i class="fa fa-trash-o fa-margin"></i>
                        <?php echo lang('delete'); ?>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>

        <?php if ($invoice->is_read_only != 1) { ?>
        <a href="#" class="btn btn-sm btn-default" id="btn_add_row">
            <i class="fa fa-plus"></i> <?php echo lang('add_new_row'); ?>
        </a>
        <a href="#" class="btn btn-sm btn-default" id="btn_add_product">
            <i class="fa fa-database"></i>
            <?php echo lang('add_product'); ?>
        </a>
        <a href="#" class="btn btn-sm btn-success" id="btn_save_invoice">
            <i class="fa fa-check"></i> <?php echo lang('save'); ?>
        </a>
        <?php } ?>
    </div>

    <div class="invoice-labels pull-right">
        <?php if ($invoice->invoice_is_recurring) { ?>
            <span class="label label-info"><?php echo lang('recurring'); ?></span>
        <?php } ?>
        <?php if ($invoice->is_read_only == 1) { ?>
            <span class="label label-danger">
                <i class="fa fa-read-only"></i> <?php echo lang('read_only'); ?>
            </span>
        <?php } ?>
    </div>

</div>

<div class="content">

    <?php echo $this->layout->load_view('layout/alerts'); ?>

    <form id="invoice_form" class="form-horizontal">

        <div class="invoice">

            <div class="cf row">

                <div class="col-xs-12 col-md-8">
                    <div class="pull-left">

                        <h2><a href="<?php echo site_url('clients/view/' . $invoice->client_id); ?>"><?php echo $invoice->client_name; ?></a></h2><br>
                        <span>
                            <?php echo ($invoice->client_address_1) ? $invoice->client_address_1 . '<br>' : ''; ?>
                            <?php echo ($invoice->client_address_2) ? $invoice->client_address_2 . '<br>' : ''; ?>
                            <?php echo ($invoice->client_city) ? $invoice->client_city : ''; ?>
                            <?php echo ($invoice->client_state) ? $invoice->client_state : ''; ?>
                            <?php echo ($invoice->client_zip) ? $invoice->client_zip : ''; ?>
                            <?php echo ($invoice->client_country) ? '<br>' . $invoice->client_country : ''; ?>
                        </span>
                        <br><br>
                        <?php if ($invoice->client_phone) { ?>
                            <span><strong><?php echo lang('phone'); ?>:</strong> <?php echo $invoice->client_phone; ?></span><br>
                        <?php } ?>
                        <?php if ($invoice->client_email) { ?>
                            <span><strong><?php echo lang('email'); ?>:</strong> <?php echo $invoice->client_email; ?></span>
                        <?php } ?>

                    </div>
                </div>

                <div class="col-xs-12 col-md-4">
                    <div class="details-box">

                        <?php if ($invoice->invoice_sign == -1) { ?>
                            <div class="invoice-properties">
                                <span class="label label-warning">
                                    <i class="fa fa-credit-invoice"></i>&nbsp;
                                    <?php echo lang('credit_invoice_for_invoice') . ' ';
                                    echo anchor('/invoices/view/'.$invoice->creditinvoice_parent_id,
                                        $invoice->creditinvoice_parent_id)?>
                                </span>
                            </div>
                        <?php } ?>

                        <div class="invoice-properties">
                            <label><?php echo lang('invoice'); ?> #</label>
                            <div >
                                <input type="text" id="invoice_number"
                                       class="input-sm form-control"
                                       value="<?php echo $invoice->invoice_number; ?>"
                                    <?php if ($invoice->is_read_only == 1) { echo 'disabled="disabled"';} ?>>
                            </div>
                        </div>
                        <div class="invoice-properties has-feedback">
                            <label><?php echo lang('date'); ?></label>
                            <div class="input-group">
                                <input name="invoice_date_created" id="invoice_date_created"
                                       class="form-control datepicker"
                                       value="<?php echo date_from_mysql($invoice->invoice_date_created); ?>"
                                    <?php if ($invoice->is_read_only == 1) { echo 'disabled="disabled"';} ?>>
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar fa-fw"></i>
                                </span>
                            </div>
                        </div>
                        <div class="invoice-properties has-feedback">
                            <label><?php echo lang('due_date'); ?></label>
                            <div class="input-group">
                                <input name="invoice_date_due" id="invoice_date_due"
                                       class="form-control datepicker"
                                       value="<?php echo date_from_mysql($invoice->invoice_date_due); ?>"
                                    <?php if ($invoice->is_read_only == 1) { echo 'disabled="disabled"';} ?>>
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar fa-fw"></i>
                                </span>
                            </div>
                        </div>
                        <div class="invoice-properties">
                            <label><?php echo lang('status'); ?></label>
                            <div>
                                <select name="invoice_status_id" id="invoice_status_id"
                                        class="form-control input-sm"
                                    <?php if ($invoice->is_read_only == 1) { echo 'disabled="disabled"';} ?>>
                                    <?php foreach ($invoice_statuses as $key=>$status) { ?>
                                        <option value="<?php echo $key; ?>" <?php if ($key == $invoice->invoice_status_id) { ?>selected="selected"<?php } ?>>
                                            <?php echo $status['label']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <?php $this->layout->load_view('invoices/partial_item_table'); ?>

            <label><?php echo lang('invoice_terms'); ?></label>
            <textarea id="invoice_terms" name="invoice_terms" class="form-control" rows="3"
                <?php if ($invoice->is_read_only == 1) { echo 'disabled="disabled"';} ?>
                ><?php echo $invoice->invoice_terms; ?></textarea>

            <?php foreach ($custom_fields as $custom_field) { ?>
                <label><?php echo $custom_field->custom_field_label; ?></label>
                <input type="text" class="form-control"
                       name="custom[<?php echo $custom_field->custom_field_column; ?>]"
                       id="<?php echo $custom_field->custom_field_column; ?>"
                       value="<?php echo form_prep($this->mdl_invoices->form_value('custom[' . $custom_field->custom_field_column . ']')); ?>"
                    <?php if ($invoice->is_read_only == 1) { echo 'disabled="disabled"';} ?>>
            <?php } ?>

            <p class="padded">
                <?php if ($invoice->invoice_status_id != 1) { ?>
                    <?php echo lang('guest_url'); ?>: <?php echo auto_link(site_url('guest/view/invoice/' . $invoice->invoice_url_key)); ?>
                <?php } ?>
            </p>
        </div>

    </form>

</div>
