"use strict";var btn=document.querySelector("#btn"),container=document.querySelector(".card-content");btn.addEventListener("click",function(){var e=document.querySelector("#token").value,t=encodeURIComponent(document.querySelector("#message").value),n=document.querySelector("#expires").value,r=new XMLHttpRequest;r.onreadystatechange=function(){if(r.readyState<4)btn.innerHTML="Loading...";else if(4===r.readyState&&200==r.status&&r.status<300){var e=JSON.parse(r.responseText);if(!e.status)return alert(e.error),!(btn.innerHTML="Generate Message");var t="<div class='row'><div class='input-field col s12'><input value='"+e.url_user+"'></div></div><div class='row'><div class='input-field col s12'><input value='"+e.url_server+"'></div></div><div class='row'><a onClick='window.location.reload()' class='col s12 btn-large waves-effect orange'>Another One</a></div>";container.innerHTML=t}},r.open("POST","/add.php?token="+e+"&message="+t+"&expires="+n),r.setRequestHeader("Content-Type","application/x-www-form-urlencoded"),r.send()});
//# sourceMappingURL=add.js.map