                
// <![CDATA[
$(document).ready(function () {
    
    $(".registros").mouseover(function() {
        $(this).css("background-color",'#F2FFE3');
    }).mouseout(function(){
        $(this).css("background-color",'#FFF');
    });
    
    $('#ContaSaldo').maskMoney({
        allowNegative: true,
        decimal: ',',
        thousands: '.',
        symbol: 'R$ ',
        showSymbol: true,
        symbolStay: true,
        defaultZero: true
    });
    
    $('#valorMask').maskMoney({
        symbol: 'R$ ',
        showSymbol: true,
        symbolStay: true,
        decimal: ',',
        thousands: '.',
        defaultZero: true
    });

    $.ajaxSetup({
        type: "GET",
        contentType: "application/x-www-form-urlencoded; charset=utf-8",
        cache: false
    });
    
    $.datepicker.setDefaults({
        dateFormat: 'dd-mm-yy',
        dayNamesMin: ['Dom', 'Sex', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
        dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
        monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
        dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado']});
    
    $('#data-calendario').datepicker({
    });

    
    $('#MoveConfig0').click(function() {           
        if($(this).is(':checked'))  {
            $('#frequencia-a').attr('disabled','disabled');
            $('#numparcelas').attr('disabled','disabled');
        }else{
            
        } 
    });
    
    $('#MoveConfig1').click(function() {           
        if($(this).is(':checked'))  {
            $('#frequencia-a').removeAttr('disabled');
            $('#numparcelas').removeAttr('disabled');
        } 
    });
    
    var m_names = new Array("Janeiro", "Fevereiro", "Março", 
        "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", 
        "Outubro", "Novembro", "Dezembro"
    );
    
    function padLeft(nr, n, str){
        return Array(n-String(nr).length+1).join(str||'0')+nr;
    }

    $('.next-month, .prev-month').click(function(){
        
        var next = $(this).attr('id');  
        var dataobj = next.split('-');        
        var mes = dataobj[0];
        var ano = dataobj[1];

        if(mes <= 9 && mes.length > 1){
            mes = mes.substr(1);
        }
        
        mes = parseInt(mes);
        mes = padLeft(mes,2);

        var d = new Date(ano, mes);
        var proximo = padLeft(d.getMonth()+1,2)+'-'+d.getFullYear(); 

        d.setFullYear(ano, mes-2);
        var anterior = padLeft(d.getMonth()+1,2)+'-'+d.getFullYear(); 

        $('.next-month').attr('id', proximo);
        $('.prev-month').attr('id', anterior);
        $('#mes-movimentacoes').html(m_names[mes-1]+'<br />'+ano);
        
        movimentacoes(mes, ano);
         
        return false;
    });
    
});  


function insereInputCategorias(){
    $.ajax({
        url: '/moves/insereInput',
        beforeSend: function(){
            $('#selectCategoria img').detach();
            $('#selectCategoria').append(' <img src="/img/ajax-loader-p.gif" />');
        },
        success: function(result){    
            $('#selectCategoria').remove();
            $('#categorias_').prepend(result);
        }
    });
    return false;
};

function insereSelectCategorias(){
    $.ajax({
        url: '/moves/insereSelect',
        beforeSend: function(){
            $('#inputCategoria img').detach();
            $('#inputCategoria').append(' <img src="/img/ajax-loader-p.gif" />'); 
        }, 
        success: function(result){
            $('#inputCategoria').remove();
            $('#categorias_').prepend(result);
        }
    });
    return false;
};
    
function movimentacoes(mes, ano){

    $.ajax({
        
        url: '/moves/dados', 
        data: ({ mes: mes, ano: ano }),
        beforeSend: function(){
            $('#table-wrapper').html('<img src="/img/loading.gif" alt="... carregando dados ..." id="loading" />');
        },
        success: function(result){
            
            parent.$('#table-wrapper img').detach();
            parent.$('#table-wrapper').html(result);
        }
    });
}

function disableOrNotInputs(value){
    if(value == 0){
        $('#frequencia-a').attr('disabled','disabled');
        $('#numparcelas').attr('disabled','disabled');
    }
}

// ]]>
