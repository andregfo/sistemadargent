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
        $this->set('title_for_layout', 'Movimentações');
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
            
            $data = $this->params['url']['data'];
            list($ano, $mes, $dia) = explode('-', $data);
            
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
