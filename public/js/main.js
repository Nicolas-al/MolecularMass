$(document).ready(function() {
    $('#search_block button').on('click', function(){
        $('.table tbody tr').remove();
        let formula = $('#search_block input').val();
        $.ajax({
            type: "GET",
            url: "/formula/calculate",
            data: {'formula' : formula },
            // dataType : 'json',
            success: function(msg){
                if(JSON.parse(msg) === "error"){
                    $('#error_formula').css({
                        'visibility' : 'inherit'
                    });
                    // $('#search_block input').addClass('errorInput');
                    // $('#result_block').addClass('error-resultBlock');
                    $('#result_block strong').html("");
                    // setTimeout(function() {
                    //     $('#error_formula').css({
                    //         'visibility' : 'hidden'
                    //     });
                    // }, 3000);
                    // .delay(3000).removeClass('errorInput');
                }else{ 
                    
                    let atoms = JSON.parse(msg)[0];
                    let molecularMass = JSON.parse(msg)[1];
                    let massAtoms = JSON.parse(msg)[2];
                    $('#result_block strong').html(JSON.parse(msg)[1] + '(g/mol)');
                    
                    atoms.forEach((atomBlock, i) => {
                        let atomNumber = atomBlock[1];
                        if(!atomBlock[1]){
                            atomNumber = 1;
                        }
                        let subtotalMass = massAtoms[i]*atomNumber;
                        $('table tbody').append('<tr><td>'+atomBlock[0]+'</td><td>'+massAtoms[i]+'</td><td>'+atomNumber+'</td><td>'+Math.round(subtotalMass * 10000)/10000+'</td></tr>');
                    });
                    // $('#search_block input').removeClass('errorInput');
                    // $('#result_block').removeClass('error-resultBlock');
                    $('#error_formula').css({
                        'visibility' : 'hidden'
                    })
                }
            },
        })
    })
});