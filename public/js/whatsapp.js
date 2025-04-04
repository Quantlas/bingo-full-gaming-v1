$(document).ready(function() {
    $('#contact').submit(sendWhatsapp);
    setTimeout(function() {
        $(".button-hide").show();
    }, 2000);

   
});


function sendWhatsapp() {
    var name = $(".contact_name").val();
    var email = $(".contact_email").val();
    var message = $(".contact_message").val();

   
    var phoneNumber = "+34667436900";
    var message = "¡Saludos! Soy " + name +" y necesito ayuda te dejo mis datos. \n \n Nombre: "+ name+" \n Correo electronico: " + email+" \n  Ademas, tengo un mensaje que quiero compartir contigo: "+message+" Agradezco mucho tu ayuda. Espero tu respuesta. ¡Gracias!";

    var whatsappLink = "https://api.whatsapp.com/send?phone=" + phoneNumber + "&text=" + encodeURIComponent(message);

    window.open(whatsappLink, "_blank");
    return false;
}

function enviar_mensaje() {
    var a = document.getElementById("chat-input");
    if ("" != a.value) {
        var b = document.getElementById("get-number").innerHTML,c = document.getElementById("chat-input").value, d = "https://web.whatsapp.com/send", e = b,  f = "&text=" + c;
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) var d = "whatsapp://send";  var g = d + "?phone=" + e + f;  window.open(g, "_blank");
    }
}
 
const whatsapp_chat =document.getElementById("whatsapp-chat");
  
function cerrar_chat(){
    whatsapp_chat.classList.add("hide-wsp");
    whatsapp_chat.classList.remove("show-wsp");
}
  
function mostrar_chat(){
    setTimeout(function() {
        $(".button-float-push").hide();
    }, 1000);
    whatsapp_chat.classList.add("show-wsp");
    whatsapp_chat.classList.remove("hide-wsp");
}
    