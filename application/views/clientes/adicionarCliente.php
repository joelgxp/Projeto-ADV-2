<?php
/**
 * View para adicionar novo cliente
 * Usa a view parcial _form_cliente.php
 */
?>
<div class="row-fluid" style="margin-top:0">
    <div class="span12">
        <div class="widget-box">
            <div class="widget-title" style="margin: -20px 0 0">
                <span class="icon">
                    <i class="fas fa-user"></i>
                </span>
                <h5>Cadastro de Cliente</h5>
            </div>
            <?php
            $this->load->view('clientes/_form_cliente', [
                'cliente' => null,
                'action_url' => current_url(),
                'submit_text' => 'Salvar',
                'form_title' => 'Cadastro de Cliente',
                'custom_error' => isset($custom_error) ? $custom_error : ''
            ]);
            ?>
        </div>
    </div>
</div>
