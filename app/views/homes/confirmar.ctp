
    
    <?php //echo $this->element('sql_dump'); ?>
    
    <span class="registro-atualizado">
        Confirmado
    </span>
    
    <?php   echo $html->link(__('Cancelar', true),
                        'javascript:;',
                        array('onclick' => 'cancelar('.$registros['id'].',\''.$registros['tipo'].'\')')); ?>
    