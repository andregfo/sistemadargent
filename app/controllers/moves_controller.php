<?php


class MovesController extends AppController {


    var $name = "Moves";
    var $components = array('Data', 'Valor');

    function index(){
        
        $this->helpers[] = 'Time';
        
        $mes = $this->Data->retornaNomeDoMes((int)date('m')) . '<br />' . date('Y');
        $this->set('mes', $mes); 

        $anterior = date('m-Y', mktime(0,0,0,date('m')-1,1,date('Y'))); 
        $this->set('anterior', $anterior);
        
        $proximo = date('m-Y', mktime(0,0,0,date('m')+1,1,date('Y'))); 
        $this->set('proximo', $proximo);
    }
    
    
    function dados(){
        
        $mes = $this->params['url']['mes'];
        $ano = $this->params['url']['ano'];
        
        if(!@checkdate($mes,1,$ano)){
            $mes = date('m');
            $ano = date('Y');
        }
        
        $this->set('mes', $mes);
        $this->set('ano', $ano);

        $moves = $this->Move->find('all', 
                        array('conditions' => 
                                array('MONTH(Move.data)' => $mes,
                                      'YEAR(Move.data)' => $ano,
                                      'Move.usuario_id' => $this->user_id),
                              'order' => 'Move.data DESC'));
        $this->set('numero', count($moves));
        $this->set('moves', $moves);
        
        $despesas = $this->Move->despesasNoMes($mes, $ano, $this->user_id);
        $faturamentos = $this->Move->faturamentosNoMes($mes, $ano, $this->user_id);
        $saldo = $faturamentos - $despesas;
        
        $this->set('despesas', $this->Valor->formata($despesas,'humano'));
        $this->set('faturamentos', $this->Valor->formata($faturamentos, 'humano'));
        $this->set('saldo', $this->Valor->formata($saldo, 'humano'));
        
        if($saldo > 0){
            $class = "positivo";
        }else{
            $class = "negativo";
        }
        $this->set('classSaldo', $class);

        $this->layout = 'ajax';
    }
    
     
    function migracao(){
        
        $this->loadModel('Ganho'); 
        $this->loadModel('Fonte'); 
        $this->loadModel('Gasto'); 
        $this->loadModel('Destino'); 
        $this->loadModel('Categoria'); 
        
        
        $this->Move->Behaviors->detach('Modifiable');
         
        $this->Fonte->recursive = -1; 
        $fontes = $this->Fonte->find('all');
        foreach($fontes as $key => $fonte){
            
            array_shift($fonte['Fonte']);
            $categoria['Categoria'] = $fonte['Fonte'];
            
            $this->Categoria->create();
            if($this->Categoria->save($categoria, false)){
                
                echo 'Fonte ' . $categoria['Categoria']['nome'] . ' migrada para as categorias<hr />';
                
                $this->Ganho->Behaviors->detach('Modifiable');
                $ganhos = $this->Ganho->find('all', 
                                array('conditions' =>array('Fonte.id' => $fontes[$key]['Fonte']['id'])));
                foreach($ganhos as $ganho){
                    
                    array_shift($ganho['Ganho']);
                    $registro['Move'] = $ganho['Ganho']; 
                    $registro['Move']['categoria_id'] = $this->Categoria->id;
                    $registro['Move']['tipo'] = 'Faturamento';
                    $registro['Move']['obs'] = $ganho['Ganho']['observacoes'];

                    if(!empty($ganho['Ganho']['datadabaixa'])){
                        $registro['Move']['data'] = $ganho['Ganho']['datadabaixa'];
                    }else{
                        $registro['Move']['data'] = $ganho['Ganho']['datadevencimento'];
                    }
                    
                     
                    $this->Move->create(); 
                    if($this->Move->save($registro, false)){
                        echo 'Ganho migrado com sucesso.<hr />';        
                    }else{
                        echo 'erro na migração de ganhos';
                        exit;
                    }
                
                }
            
            }else{
                echo 'error ao salvar categoria';
                exit;    
            }        
        }
       
        $this->Destino->recursive = -1; 
        $destinos = $this->Destino->find('all');
        foreach($destinos as $key => $destino){
            
            array_shift($destino['Destino']);
            $categoria['Categoria'] = $destino['Destino'];
            
            $this->Categoria->create();
            if($this->Categoria->save($categoria, false)){
                
                echo 'Destino ' . $categoria['Categoria']['nome'] . ' migrada para as categorias<hr />';
                
                $this->Gasto->Behaviors->detach('Modifiable');
                $gastos = $this->Gasto->find('all', 
                                array('conditions' =>array('Destino.id' => $destinos[$key]['Destino']['id'])));
                foreach($gastos as $gasto){
                    
                    array_shift($gasto['Gasto']);
                    $registro['Move'] = $gasto['Gasto']; 
                    $registro['Move']['categoria_id'] = $this->Categoria->id;
                    $registro['Move']['tipo'] = 'Despesa';
                    $registro['Move']['obs'] = $gasto['Gasto']['observacoes'];

                    if(!empty($gasto['Gasto']['datadabaixa'])){
                        $registro['Move']['data'] = $gasto['Gasto']['datadabaixa'];
                    }else{
                        $registro['Move']['data'] = $gasto['Gasto']['datadevencimento'];
                    }

                    $this->Move->create(); 
                    if($this->Move->save($registro, false)){
                        echo 'Gasto migrado com sucesso.<hr />';        
                    }else{
                        echo 'erro na migração de ganhos';
                        exit;
                    }
                
                }
            
            }else{
                echo 'error ao salvar categoria';
                exit;    
            }        
        } 


        $this->autoRender = false;
    }        



    function add(){
        

        if(!empty($this->data)){

            if ( isset($this->data['Categoria']['nome']) ){
                $this->data['Categoria']['usuario_id'] = $this->user_id;
                unset($this->Move->validate['categoria_id']);
            }

            $this->data['Move']['usuario_id'] = $this->user_id;
            if ( $this->Move->adicionar($this->data) ) {
                                
                $this->Session->setFlash('Registro salvo com sucesso!','flash_success');
                if(!$this->data['Move']['keepon']){
                    $this->redirect(array('action'=>'index'));  
                }else{
                    $this->data = null;
                }
                
            }else{
                $this->Session->setFlash('Confira os dados que você inseriu novamente','flash_error');
                $errors = $this->validateErrors($this->Move->Categoria,$this->Move);
            }    
        }
        
        if(!isset($this->data['Move']['config'])){
            $this->data['Move']['config'] = 0;
        }
         
        $categorias = $this->Move->Categoria->listar($this->user_id);
        $this->set(compact('categorias'));
        
        $contas = $this->Move->Conta->listar($this->user_id);
        $this->set(compact('contas'));
    }
    
    function edit($id){
        
        $this->data = $this->Move->read(null, $id);
        $this->data['Move']['data'] = $this->Data->formata($this->data['Move']['data'], 'diamesano');

        $categorias = $this->Move->Categoria->listar($this->user_id);
        $this->set(compact('categorias')); 
        
        $contas = $this->Move->Conta->listar($this->user_id);
        $this->set(compact('contas'));
        
        $this->layout = "colorbox"; 
    }
    
    function editResponse(){

        
        if( $this->params['isAjax'] ){
            
            $this->data = array_merge($this->params['url']);
            
            $result = $this->Move->editar($this->data, $this->user_id);           
            
            list($dia, $mes, $ano) = explode('-', $this->data['Move']['data']);
            $resposta = array(
                'result' => $result,
                'mes' => $mes,
                'ano' => $ano
            );
            
            $this->set('resposta', json_encode($resposta));  
            $this->layout = 'ajax';        
        }
    }
        
    function delete($id = null){
        
        if( $this->params['isAjax'] ){
            
            $id = $this->params['url']['id'];
            $result = $this->Move->excluir($id, $this->user_id);           

            $this->set('result', json_encode($result));  
            $this->render('delete_response');        
            $this->layout = 'ajax';
        }else{
            $this->helpers[] = 'Time';
            $this->data = $this->Move->read(null,$id);
            $this->layout = 'colorbox';   
        }

    }
     
    function confirmar(){
        
        list($move, $id, $mes, $ano) = explode('-', $this->params['url']['id']);
        $result = $this->Move->confirmar($id, $this->user_id);
        
        
        $despesas = $this->Move->despesasNoMes($mes, $ano, $this->user_id);
        $faturamentos = $this->Move->faturamentosNoMes($mes, $ano, $this->user_id);
        $saldo = $faturamentos - $despesas;
        
        $this->set('despesas', $this->Valor->formata($despesas,'humano'));
        $this->set('faturamentos', $this->Valor->formata($faturamentos, 'humano'));
        $this->set('saldo', $this->Valor->formata($saldo, 'humano'));
        
        if($saldo > 0){
            $class = "positivo";
        }else{
            $class = "negativo";
        }
        
        $this->set('classSaldo', $class);
        
        $this->set('result', $result);
        $this->set('id', $id);
    
        $this->layout = 'ajax';      
    }
         
    function insereInput(){
        $this->layout = 'ajax';
    }
    
    function insereSelect(){
        
        $categorias = $this->Move->Categoria->listar($this->user_id);
        $this->set(compact('categorias'));
        
        $this->layout = 'ajax';
    }


}
