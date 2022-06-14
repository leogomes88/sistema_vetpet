$(document).ready(() => {

    $('#data_nasc').mask('99/99/9999')
    
    $('#cpf').mask('999.999.999-99')

    $('#telefone').mask('(99) 99999-9999')

    $("#nome").on("input", function(){

      var regexp = /[^a-zA-ZáãâéêíîóôõúÁÃÂÉÊÍÎÓÔÕÚ\s]/g;

      if(this.value.match(regexp)){
        $(this).val(this.value.replace(regexp,''))               
      }

      $(this).val(this.value.toUpperCase()) 

    })

    $("#raca").on("input", function(){

      var regexp = /[^a-zA-ZáãâéêíîóôõúÁÃÂÉÊÍÎÓÔÕÚ\s-]/g;

      if(this.value.match(regexp)){
        $(this).val(this.value.replace(regexp,''))
      }

      $(this).val(this.value.toUpperCase())

    })

    $("#especie").on("input", function(){

      var regexp = /[^a-zA-ZáãâéêíîóôõúÁÃÂÉÊÍÎÓÔÕÚ\s-]/g;

      if(this.value.match(regexp)){
        $(this).val(this.value.replace(regexp,''))
      }
      
      $(this).val(this.value.toUpperCase())
      
    })
})