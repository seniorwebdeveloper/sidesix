// ********************************************************************************* //
var Forms = Forms ? Forms : new Object();
// ********************************************************************************* //

jQuery(document).ready(function(){	
	
	Forms.Fields = jQuery('.Forms');
	Forms.Fields.tabs();
	
	Forms.Fields.find('select.chzn-select').chosen();
});

//********************************************************************************* //

