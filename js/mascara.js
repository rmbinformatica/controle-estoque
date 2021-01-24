function mascara(o,f){
    v_obj=o;
    v_fun=f;
    setTimeout("execmascara()",1);
}

function execmascara(){
    v_obj.value=v_fun(v_obj.value);
}

function soNumeros(v){
    return v.replace(/\D/g,"");
}

function matri(v){
    v=v.replace(/\D/g,""); //Remove tudo o que n�o � d�gito
    v=v.replace(/(\d{3})(\d)/,"$1.$2"); //Coloca um ponto entre o terceiro e o quarto d�gitos
    v=v.replace(/(\d{3})(\d{1,2})$/,"$1-$2"); //Coloca um h�fen entre o terceiro e o quarto d�gitos
    return v;
}

function bank(v){
    v=v.replace(/\D/g,"");
    if (v.length > 1) {
       v=v.substr(0,v.length-1)+"-"+v.substr(v.length-1,1);
    }
    return v;
}

function reais(v){
    v=v.replace(/\D/g,""); //Remove tudo o que n�o � d�gito
    v=v/100;
    v=v.toFixed(2); //Coloca um ponto entre o terceiro e o quarto d�gitos
    return v;
}

function maiusculas(v){
    return v.toUpperCase();
}
function minusculas(v){
    return v.toLowerCase();
}

function localiza(v){
    v=v.toUpperCase(); // Converte tudo em maiusculas
    v=v.replace(/[^0-9A-Z]/g,""); // Remove tudo nao-alfanumerico
    if (v.length > 3) {
        prefixo=v.substr(0,3); //Separa o prefixo nos tr�s primeiros caracteres.
        posfixo=v.substr(3,v.length-1); //Separa o posfixo no restante.
        posfixo=posfixo.replace(/[^0-9A-F]/g,""); //Retira do posfixo tudo que n�o for hexadecimal.
        v=prefixo+"-"+posfixo; // Coloca um hifen entre terceiro e quarto-caracter
    }
    return v;
}

function altu(v){
    v=v.toUpperCase(); // Converte tudo em maiusculas
    v=v.replace(/[^0-9]/g,""); // Remove tudo nao-numerico
    if (v.length > 1) {
        prefixo=v.substr(0,1); //Separa o prefixo nos primeiro caractere.
        posfixo=v.substr(1,v.length-1); //Separa o posfixo no restante.
        v=prefixo+"."+posfixo; // Coloca um hifen entre terceiro e quarto-caracter
    }
    return v;
}