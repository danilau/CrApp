var changeHandStateForUser = function(state, username){
    $('table tr').each(function(k,v){

        var name = $(this).find('.col-md-11').text();
        if(name===username){
            if(state==1)
                $(this).find('.col-md-1').addClass('handup');
            else
                $(this).find('.col-md-1').removeClass('handup');
        }
    });
}

var conn = new ab.Session('ws://localhost:8090',
    function() {
        conn.subscribe('crapp', function(topic, data) {
            // This is where you would add the new article to the DOM (beyond the scope of this tutorial)

            var msg = $.parseJSON(data);

            if(msg.type == 'class_config_changed'){
                var $table = $('table tbody').html('');

                $.each(msg.members, function(k,v){
                    var $row = $('<tr></tr>'),
                        $colName = $('<td></td>').addClass('col-md-11').text(v.name),
                        $colHand = $('<td></td>').addClass('col-md-1');

                    if(v.handState==1)
                        $colHand.addClass('handup');
                    else
                        $colHand.removeClass('handup');

                    var $fullRow = $row.append($colName,$colHand);

                    $table.append($fullRow);
                });
            }else if(msg.type == 'student_state_changed'){
                changeHandStateForUser(msg.student.handState,msg.student.name);
            }

        });
    },
    function() {

    },
    {'skipSubprotocolCheck': true}
);


