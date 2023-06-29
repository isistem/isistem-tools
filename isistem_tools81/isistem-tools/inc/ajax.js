// Fun��o obter os limites de um dom�nio(caso tenha)
function carregar_backups( usuario, data ) {

  if(usuario == "" || data == "") {
  alert("Aten��o! Selecione primeiro uma data e depois o dom�nio desejado para carregar a lista de backups dispon�veis.");
  } else {
  
  document.getElementById('status_carregar_backups').innerHTML = 'carregando backups...';
  
  var http = new Ajax();
  http.open("GET", "inc/funcoes.php?acao=carregar_backups&usuario="+usuario+"&data="+data+"" , true);
  http.onreadystatechange = function() {
	
  if(http.readyState == 4) {
  
	resultado = http.responseText;
	
	dados = resultado.split("|");
	
	for(var cont = 0; cont < dados.length; cont++) {
		
	dados_backup = dados[cont].split(":");
	
	if(dados_backup[0] != "" && dados_backup[1] != "") {
	
	linha = document.createElement("option");
	document.getElementById("backup").appendChild(linha);
	linha.value = dados_backup[0];
	linha.text = dados_backup[1];
	
	}

	}	
  }
  
  document.getElementById('status_carregar_backups').innerHTML = '';
  
  }
  http.send(null);
  delete http;
  }
}

// Rotina AJAX
function Ajax() {
var req;

try {
 req = new ActiveXObject("Microsoft.XMLHTTP");
} catch(e) {
 try {
	req = new ActiveXObject("Msxml2.XMLHTTP");
 } catch(ex) {
	try {
	 req = new XMLHttpRequest();
	} catch(exc) {
	 alert("Esse browser n�o tem recursos para uso do Ajax");
	 req = null;
	}
 }
}

return req;
}