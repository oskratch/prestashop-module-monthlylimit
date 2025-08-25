<link href="{$module_dir}assets/css/select2.min.css" rel="stylesheet" />
<script src="{$module_dir}assets/js/select2.min.js"></script>
<div class="alert alert-info">
    <img src="../modules/monthlylimit/logo.png" style="float:left; margin-right:15px;" height="60">
    <p><strong>Important information.</strong></p>
    <p>{l s='If you want to disable a specific limit, enter 0 in the corresponding field and it will be deactivated.'}</p>
</div>
<form action="{$link->getAdminLink('AdminLimitOrders')}" class="defaultForm form-horizontal" method="post">    
    <input type="hidden" name="btnSubmit" value="1" />				
    <div class="panel" id="fieldset_0">												
        <div class="panel-heading">
            <i class="icon-envelope"></i>							
            Set purchase limits
        </div>
        <div class="form-wrapper">											
            <div class="form-group">
                <label class="control-label col-lg-4 required">
                    {l s='Límite en euros'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="monthly_limit_euros" id="monthly_limit_euros" value="{$monthly_limit_euros}" required />
                </div>
            </div>										
            <div class="form-group">
                <label class="control-label col-lg-4 required">
                    {l s='Limit de compras'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="monthly_limit_times" id="monthly_limit_times" value="{$monthly_limit_times}" required />
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1"	id="configuration_form_submit_btn" name="btnSubmit" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> Guardar
            </button>
        </div>
    </div>
    <input type="hidden" name="_token" value="{$_token}" />
</form>
    <!-- Bloque para gestionar clientes excluidos de los límites -->
    <!-- Block to manage customers excluded from limits -->
    <form action="{$link->getAdminLink('AdminLimitOrders')}" method="post" class="defaultForm form-horizontal" style="margin-top:30px;">
        <input type="hidden" name="exclude_customers_submit" value="1" />
        <div class="panel" id="fieldset_excluded_customers">
            <div class="panel-heading">
                <i class="icon-user"></i>
                {l s='Excluir clientes de los límites'}
            </div>
            <div class="form-wrapper">
                <div class="form-group">
                    <label class="control-label col-lg-4">
                        {l s='Busca y selecciona los clientes que quieres excluir'}
                    </label>
                    <div class="col-lg-8">
                        <select name="excluded_customers[]" id="excluded_customers" multiple style="width:100%;height:150px;">
                            {foreach from=$customers item=customer}
                                <option value="{$customer.id_customer}" {if in_array($customer.id_customer, $excluded_customers)}selected{/if}>
                                    {$customer.firstname} {$customer.lastname} ({$customer.email})
                                </option>
                            {/foreach}
                            </select>
                            <p class="help-block">{l s='Los clientes seleccionados no tendrán ningún límite de compra.'}</p>
                        </div>
                    </div>
                </div>
                <div class="panel-footer">
                    <button type="submit" class="btn btn-default pull-right">
                        <i class="process-icon-save"></i> Guardar exclusiones
                    </button>
                </div>
            </div>
            <input type="hidden" name="_token" value="{$_token}" />
        </form>
<script type="text/javascript">
    $(document).ready(function() {
        $('#excluded_customers').select2({
            width: '100%',
            placeholder: 'Buscar clientes...',
            allowClear: true
        });
    });
</script>
                        </select>
                        <p class="help-block">{l s='Selected customers will not have any purchase limit.'}</p>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="_token" value="{$_token}" />
    </form>