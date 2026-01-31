<div id="content">
<!--start-top-serch-->
  <div id="content-header">
   <div></div>
      <div id="breadcrumb">
        <a href="<?= base_url() ?>" title="Dashboard" class="tip-bottom"> Início</a>
        <?php if ($this->uri->segment(1) != null) { ?>
            <a href="<?= base_url() . 'index.php/' . $this->uri->segment(1) ?>" class="tip-bottom" title="<?= ucfirst($this->uri->segment(1)); ?>">
              <?= ucfirst($this->uri->segment(1)); ?>
            </a>
          <?php if ($this->uri->segment(2) != null) { ?>
            <a href="<?= base_url() . 'index.php/' . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) ?>" class="current tip-bottom" title="<?= ucfirst($this->uri->segment(2)); ?>">
              <?= ucfirst($this->uri->segment(2));
          } ?>
            </a>
          <?php } ?>
      </div>
    </div>
    <div class="container-flu">
      <div class="row-fluid">
        <div class="span12">
          <?php if ($var = $this->session->flashdata('error')): ?><script>swal("Falha!", "<?php echo str_replace('"', '', $var); ?>", "error");</script><?php endif; ?>
          <?php if ($var = $this->session->flashdata('success')): ?><script>swal("Sucesso!", "<?php echo str_replace('"', '', $var); ?>", "success");</script><?php endif; ?>
          <?php if (isset($view)) {
              // Passar todas as variáveis do escopo atual (incluindo $this->data) para a view
              $view_data = get_defined_vars();
              
              // CRÍTICO: Mergear os elementos de $this->data que foram passados pelo controller
              if (isset($this->data) && is_array($this->data)) {
                  $view_data = array_merge($view_data, $this->data);
              }
              
              unset($view_data['view_data']); // Evitar recursão
              echo $this->load->view($view, $view_data, true);
          } ?>
        </div>
      </div>
    </div>
  </div>
