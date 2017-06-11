var lines = new Array();

function add_line(pos, namelist)
{
	var n, i, j, elm, elm1, elm2, div, t;
	var inputs = new Array();
	
	count_lines(namelist);
	
	elm = document.getElementById( "div_" + namelist[0] );
	
	div = document.createElement('div');
	div.setAttribute( "id", "div_" + namelist[0] + "_" + lines[namelist[0]] );
	
	elm.appendChild(div);
	
	n = namelist.length / 2;
	for( i=0; i<n; i++ ) {
		inputs[i] = document.createElement('input');
		inputs[i].setAttribute( "type", "text" );
		inputs[i].setAttribute( "name", namelist[i*2] + "_" + lines[namelist[0]] );
		inputs[i].setAttribute( "size", namelist[i*2+1] );
		div.appendChild(inputs[i]);
	}
	
	elm = document.createElement('input');
	elm.setAttribute( "type", "button" );
	elm.setAttribute( "value", "⏎" );
	elm.onclick = (function(p,n){return function(){add_line(p,n)}})(lines[namelist[0]],namelist);
	div.appendChild(elm);
	
	elm = document.createElement('input');
	elm.setAttribute( "type", "button" );
	elm.setAttribute( "value", "×" );
	elm.onclick = (function(p,n){return function(){delete_line(p,n)}})(lines[namelist[0]],namelist);
	div.appendChild(elm);
	
	lines[namelist[0]]++;
	
	for( i=0; i<n; i++ ) {
		for( j = lines[namelist[0]]-1; j > pos+1; j-- ) {
			elm1 = document.editform[ namelist[i*2] + "_" + (j-1) ];
			elm2 = document.editform[ namelist[i*2] + "_" + j ];
			t = elm1.value;
			elm1.value = elm2.value;
			elm2.value = t;
		}
	}
}

function delete_line(pos, namelist)
{
	var n, i, j, obj, obj_parent;
	count_lines(namelist);
	
	n = namelist.length / 2;
	if( lines[namelist[0]] <= 1 ) {
		alert("don't delete");
		return;
	}
	
	for( i=0; i<n; i++ ) {
		for( j=pos; j < lines[namelist[0]]-1; j++ ) {
			elm1 = document.editform[ namelist[i*2] + "_" + j ];
			elm2 = document.editform[ namelist[i*2] + "_" + (j+1) ];
			elm1.value = elm2.value;
		}
	}
	
	obj = document.getElementById( "div_" + namelist[0] + "_" + (lines[namelist[0]]-1) );
	obj_parent = obj.parentNode;
	
    obj_parent.removeChild(obj);
    lines[namelist[0]]--;
}

function count_lines(namelist)
{
	if( lines[namelist[0]] === undefined ) {
		for( i=0; i<999; i++ ) {
			elm = document.getElementById( "div_" + namelist[0] + "_" + i );
			if( ! elm ) {
				lines[namelist[0]] = i;
				break;
			}
		}
	}
}