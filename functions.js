/**
 * @author Florian
 */
function AddDependencyRow(package_json){
	var html = $('<div></div>').append('Please choose the dependency: ');
	var select = $('<select></select>').attr("name", "dependencyName[]");
	var packages = $.parseJSON(package_json);
	for(var i=0,j=packages.length; i<j; i++){
		var option = $('<option></optiion>').text(packages[i]);
		$(select).append(option);
	};
	$(html).append(select);
	$("#dependencies").append(html);
}
