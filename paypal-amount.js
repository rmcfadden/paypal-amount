function checkDecimal(el){
    var ex = /^\d*\.?\d{0,2}$/;
    if(ex.test(el.value)==false){
        el.value = el.value.substring(0,el.value.length - 1);
    }
}