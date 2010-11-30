    <div class="destinos index">
        
        <div id="contentHeader">    
            <?php   echo $this->element('destino_menu'); ?>
            <?php   echo $this->Session->flash(); ?>
        </div>
        
        
        <div class="balancoBotoesWraper">

            <div class="balancoBotoes">
                
                <?php   if( isset($paginator) ){    ?>
                
                <span class="pagina">Página</span>
                <p><?php echo $paginator->counter(array('format' => __('%page%',true))); ?></p>
                <span class="pagina">de</span>
                <p><?php echo $paginator->counter(array('format' => __('%pages%',true))); ?></p>
                <p>|</p>
                <p><?php echo $paginator->counter(array('format' => __('%count%',true))); ?></p>
                <span class="pagina">Registros</span>
                <?php   }   ?>
                
                <div class="headeraddlinks">
                    <?php echo $this->Html->link('INSERIR DESPESA',
                                        array('controller' => 'gastos',
                                              'action' => 'add'),
                                        array('class' => 'btnadd')); ?>
                </div>
                <div class="headeraddlinks">
                    <?php echo $this->Html->link('INSERIR NOVO DESTINO',
                                        array('controller' => 'destinos',
                                              'action' => 'add'),
                                        array('class' => 'btnadd')); ?>
                </div>
                
            </div>
            
        </div>
        
        <div class="categoriasWraper">
            <div class="categorias">
            
                <ul id="list-categorias">       
                <?php
        
                foreach ($porcentagens as $key => $porcentagem):
                ?>
                    <li id="destino-<?php echo $destinos[$key]['Destino']['id']; ?>" class="registros">
                        <div style="height: auto; overflow: hidden;">
                            
                            <div class="" style="height:auto; overflow:hidden;	padding: 5px 0;">
                                
                                <span class="categoria_nome" id="nome-<?php echo $destinos[$key]['Destino']['id']; ?>">
                                    <?php echo $destinos[$key]['Destino']['nome']; ?>
                                </span>
                                
                                <span class="valor">
                                    <?php echo $destinos[$key]['Destino']['porcentagem']; ?> % «
                                </span>
                            
                            </div>
                            
                            <div style="float: right;margin-top:-23px;">
                                <?php echo $destinos[$key]['Destino']['modified']; ?>
                            </div>
                            
                            <div style="clear: both;">
                                <?php if( isset($destinos[$key]['Gasto']) ){   ?>
                                Última interação: R$ <?php echo $destinos[$key]['Gasto']['valor']; ?> em <?php echo $destinos[$key]['Gasto']['datadabaixa']; ?>
                                <?php }else{   ?>
                                Não há registros relacionados a essa categoria
                                <?php } ?>
                            </div>                            
                            
                        </div>
                        <div class="categ-actions">
                            <?php
                                echo $html->link('',
                                                    array('action' => 'edit', $destinos[$key]['Destino']['id'], time()),
                                                    array('class' => 'colorbox-edit editar')
                                                    ); 
                                echo $html->link('',
                                                    array('action' => 'delete', $destinos[$key]['Destino']['id'], time()),
                                                    array('class' => 'colorbox-delete excluir')
                                                    );
                            ?>
                        </div>
                    </li>
        
                <?php endforeach; ?>
                </ul>  
            </div>
            
            <?php   if( isset($paginator) ){    ?>
            <div class="paging">
                <?php echo $paginator->prev('<< '.__('previous', true), array(), null, array('class'=>'disabled'));?>
             | 	<?php echo $paginator->numbers();?>
             |  <?php echo $paginator->next(__('next', true).' >>', array(), null, array('class' => 'disabled'));?>
            </div>
            <?php   }   ?>
            
        </div>
    </div>
    
    <script type="text/javascript">
        $(document).ready(function () {
            
            $('.colorbox-delete').colorbox({width:"60%", height: '220', opacity: 0.5, iframe: true});
            $('.colorbox-edit').colorbox({width:"60%", height: "300", opacity: 0.5, iframe: true});
            
            $("li.registros").mouseover(function() {
                $(this).css("background-color",'#F2FFE3');
            }).mouseout(function(){
                $(this).css("background-color",'#FFF');
            });
            
        });   
    </script>
    

