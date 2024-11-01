// ======================================================================================
// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or(at your option) any later version.
//
// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.
// ======================================================================================

$(document).ready(function() 
    { 
        $("#files_table").tablesorter({
			headers: { 
				0: { 
					sorter: false 
				} 
			} 
        }); 
    } 
);

function GetCellValue(idTable, RowNumber, ColumnNumber) {
	var oTable = document.getElementById(idTable);
	var oCell = oTable.rows[RowNumber].cells[ColumnNumber];
	var sValue = "";
	if (oCell.innerText=="" || oCell.innerHTML=="") {
		sValue = oCell.childNodes.item(0).value;
	} else {
		if(typeof(oCell.innerText)=='undefined'){
			sValue = oCell.innerHTML;
		} else {
			sValue = oCell.innerText;
		}
	}
	oCell = null;
	return sValue;
}

function Invert_All_Box(idTable) {
    var oTableLength = document.getElementById("table_length");
    nTableLength = oTableLength.value;
    for(var i=0;i<=nTableLength-1;i++){
		tmpCheckbox = document.getElementById("file_"+i);
		tmpCheckbox.checked = !tmpCheckbox.checked;
		var iFileSize = GetCellValue(idTable, i+1, 3);
		Update_Fixed_Div(tmpCheckbox.checked, parseInt(iFileSize));
    }
    Display_Fixed_Div();
}

function Check_All_Box(idTable) {
	total_space = 0;
	total_files = 0;
    var oTableLength = document.getElementById("table_length");
    nTableLength = oTableLength.value;
    for(var i=0;i<=nTableLength-1;i++){
    	Check_Box(i);
		var iFileSize = GetCellValue(idTable, i+1, 3);
		Update_Fixed_Div(true, parseInt(iFileSize));
    }
    Display_Fixed_Div();
}

function Uncheck_All_Box(idTable) {
    var oTableLength = document.getElementById("table_length");
    nTableLength = oTableLength.value;
    for(var i=0;i<=nTableLength-1;i++){
    	Uncheck_Box(i);
    }
	total_space = 0;
	total_files = 0;
    Display_Fixed_Div();
}

function Check_Box(idLine) {
    var oCheckbox = document.getElementById("file_"+idLine);
    oCheckbox.checked = true;

}

function Uncheck_Box(idLine) {
    var oCheckbox = document.getElementById("file_"+idLine);
    oCheckbox.checked = false;
}

function Unused_All(idTable) {
    var oTableLength = document.getElementById("table_length");    
    nTableLength = oTableLength.value;
    for(var i=0;i<=nTableLength-1;i++){
		var tmpCheckbox = document.getElementById("file_"+i);
		var sValue = GetCellValue(idTable, i+1, 4);
		var iFileSize = GetCellValue(idTable, i+1, 3);
		if (sValue.length == 1) {
			if (!tmpCheckbox.checked) {
				tmpCheckbox.checked = true; 
				Update_Fixed_Div(true, parseInt(iFileSize));
			}
		} else {
			if (tmpCheckbox.checked) {
				tmpCheckbox.checked = false;
				Update_Fixed_Div(false, parseInt(iFileSize));
			}
		}
    }
}

function Used_All(idTable) {
    var oTableLength = document.getElementById("table_length");    
    nTableLength = oTableLength.value;
    for(var i=0;i<=nTableLength-1;i++){
		var tmpCheckbox = document.getElementById("file_"+i);
		var sValue = GetCellValue(idTable, i+1, 4);
		var iFileSize = GetCellValue(idTable, i+1, 3);
		if (sValue.length == 1) {
			if (tmpCheckbox.checked) {
				tmpCheckbox.checked = false; 
				Update_Fixed_Div(false, parseInt(iFileSize));
			}
		} else {
			if (!tmpCheckbox.checked) {
				tmpCheckbox.checked = true; 
				Update_Fixed_Div(true, parseInt(iFileSize));
			}
		}
    }
}

function UpdateCheck_Click(idTable, idLine) {
	var tmpCheckbox = document.getElementById("file_"+idLine);
	iFileSize = GetCellValue(idTable, idLine+1, 3);
	Update_Fixed_Div(tmpCheckbox.checked, parseInt(iFileSize));
}

var total_space = 0;
var total_files = 0;

function Update_Fixed_Div(boolStatus, iFileSize) {
	if (boolStatus) {
		total_files++;
		total_space += iFileSize;
	} else {
		total_files--;
		total_space -= iFileSize;
	}
	Display_Fixed_Div();
}

function Display_Fixed_Div() {
    var oFixedDiv = document.getElementById("fixed-div");
	sHTMLText = "<table>";
	sHTMLText = sHTMLText + "<tr><td>Files selected</td><td class=\'fixed-div-right\'>"+total_files+"</td></tr>";
	sHTMLText = sHTMLText + "<tr><td>Total space saved</td><td>"+bytesToSize(total_space,2)+"</td></tr>";
	sHTMLText = sHTMLText + "</table>";
	oFixedDiv.innerHTML = sHTMLText
}

/**
 * Convert number of bytes into human readable format
 *
 * @param integer bytes     Number of bytes to convert
 * @param integer precision Number of digits after the decimal separator
 * @return string
 */
function bytesToSize(bytes, precision)
{	
	var kilobyte = 1024;
	var megabyte = kilobyte * 1024;
	var gigabyte = megabyte * 1024;
	var terabyte = gigabyte * 1024;
	
	if ((bytes >= 0) && (bytes < kilobyte)) {
		return bytes + ' B';

	} else if ((bytes >= kilobyte) && (bytes < megabyte)) {
		return (bytes / kilobyte).toFixed(precision) + ' KB';

	} else if ((bytes >= megabyte) && (bytes < gigabyte)) {
		return (bytes / megabyte).toFixed(precision) + ' MB';

	} else if ((bytes >= gigabyte) && (bytes < terabyte)) {
		return (bytes / gigabyte).toFixed(precision) + ' GB';

	} else if (bytes >= terabyte) {
		return (bytes / terabyte).toFixed(precision) + ' TB';

	} else {
		return bytes + ' B';
	}
}
