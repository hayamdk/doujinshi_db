var circles = 2;
var guests = 2;
var tags = 2;
var originals = 2;
var originals_sub = 2;
var refs = 1;
var items = 2;

function add_circle() {
	var target = document.getElementById('circle_list');
	var ci, ai, sp, br;
	
	circles++;
	
	ci = document.createElement('input');
	ci.setAttribute("type","text");
	ci.setAttribute("name","circle"+circles);
	ci.setAttribute("size", 40);
	
	ai = document.createElement('input');
	ai.setAttribute("type","text");
	ai.setAttribute("name","author"+circles);
	ai.setAttribute("size", 40);
	
	sp = document.createTextNode(" ");
	
	br = document.createElement('br');
	
	target.appendChild(ci);
	target.appendChild(sp);
	target.appendChild(ai);
	target.appendChild(br);
}


function add_guest() {
	var target = document.getElementById('guest_list');
	var ci, ai, sp, br;
	
	guests++;
	
	ci = document.createElement('input');
	ci.setAttribute("type","text");
	ci.setAttribute("name","guest_circle"+guests);
	ci.setAttribute("size", 40);
	
	ai = document.createElement('input');
	ai.setAttribute("type","text");
	ai.setAttribute("name","guest_author"+guests);
	ai.setAttribute("size", 40);
	
	sp = document.createTextNode(" ");
	
	br = document.createElement('br');
	
	target.appendChild(ci);
	target.appendChild(sp);
	target.appendChild(ai);
	target.appendChild(br);
}

function add_tag() {
	var target = document.getElementById('tag_list');
	var tag, br;
	
	tags++;
	
	tag = document.createElement('input');
	tag.setAttribute("type","text");
	tag.setAttribute("name","tag"+tags);
	tag.setAttribute("size", 50);
	
	br = document.createElement('br');
	
	target.appendChild(tag);
	target.appendChild(br);
}

function add_original() {
	var target = document.getElementById('original_list');
	var original, br;
	
	originals++;
	
	original = document.createElement('input');
	original.setAttribute("type","text");
	original.setAttribute("name","original"+originals);
	original.setAttribute("size", 50);
	
	br = document.createElement('br');
	
	target.appendChild(original);
	target.appendChild(br);
}

function add_original_sub() {
	var target = document.getElementById('original_sub_list');
	var original, br;
	
	originals_sub++;
	
	original = document.createElement('input');
	original.setAttribute("type","text");
	original.setAttribute("name","original_sub"+originals_sub);
	original.setAttribute("size", 50);
	
	br = document.createElement('br');
	
	target.appendChild(original);
	target.appendChild(br);
}

function add_ref() {
	var target = document.getElementById('ref_list');
	var ref, br;
	
	refs++;
	
	ref = document.createElement('input');
	ref.setAttribute("type","text");
	ref.setAttribute( "name","ref" + refs + "_id" );
	
	br = document.createElement('br');
	
	target.appendChild(ref);
	target.appendChild(br);
}

function add_item() {
	var target = document.getElementById('item_list');
	var item, br;
	
	items++;
	
	item = document.createElement('input');
	item.setAttribute("type","text");
	item.setAttribute( "name","item" + items + "_id" );
	
	br = document.createElement('br');
	
	target.appendChild(item);
	target.appendChild(br);
}