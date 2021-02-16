/**
 * Created by G on 6/27/2016.
 */

function getval(id){
    if(id){
        return $('#'+id).val();
    }else{
        return swal('','ID cant null in getval function');
    }

}

function setval(id,value){
    if(id){
        return $('#'+id).val(value);
    }else{
        return swal('','ID cant null in setval function');
    }

}

function getRowValue(table){

}