/**
 * @author Florian
 */
function AddDependencyRow(package_json){
	var dependencySelectors = $('select[name="dependencyName[]"]').length;
	var html = $('<div></div>').append('Please choose the dependency: ');
	var select = $('<select></select>').attr("name", "dependencyName[]").attr('onchange', 'checkSelectedValue(' + dependencySelectors + ')');
	$(select).append('<option></option>');
	var packages = $.parseJSON(package_json);
	for(var i=0,j=packages.length; i<j; i++){
		var option = $('<option></optiion>').text(packages[i]);
		$(select).append(option);
	};
	$(html).append(select);
	$("#dependencies").append(html);
}

function checkSelectedValue(changedSelection){
	var dependencySelectors = $('select[name="dependencyName[]"]'); 
	var newValue = dependencySelectors[changedSelection].value;
	for(var i=0,j=dependencySelectors.length; i<j; i++){
	  if(i == changedSelection){
	  	continue;
	  }
	  if(dependencySelectors[i].value != "" && dependencySelectors[i].value == dependencySelectors[changedSelection].value){
	  	alert("Multiple entries for one dependency are not allowed.");
	  	dependencySelectors[changedSelection].value = "";
	  }
	}
}
