function handleClick(Obj,imagen)  {
    if(document.getElementById(Obj).style.display=='none') {
        document.getElementById(Obj).style.display = "";
        document.getElementById(imagen).src="./ampjukeicons/collapse.gif";
        
    }
      else {
          document.getElementById(Obj).style.display = "none";
          document.getElementById(imagen).src="./ampjukeicons/expand.gif";
          
      }
}

function my_expand_collapse(Obj,imagen,v) {
	if (v==1) {
		document.getElementById(Obj).style.display = "";
    	document.getElementById(imagen).src="./ampjukeicons/collapse.gif";
	} else {
		document.getElementById(Obj).style.display = "none";
	    document.getElementById(imagen).src="./ampjukeicons/expand.gif";
	}
}

function cfg_expand_collapse_all(v) {
	my_expand_collapse('to_col1','gif1',v);
	my_expand_collapse('to_col2','gif2',v);	
	my_expand_collapse('to_col3','gif3',v);
	my_expand_collapse('to_col4','gif4',v);
	my_expand_collapse('to_col5','gif5',v);		
	my_expand_collapse('to_col6','gif6',v);
	my_expand_collapse('to_col7','gif7',v);
	my_expand_collapse('to_col8','gif8',v);
	my_expand_collapse('to_col9','gif9',v);
	my_expand_collapse('to_col10','gif10',v);	
	my_expand_collapse('to_col11','gif11',v);
	my_expand_collapse('to_col12','gif12',v);		
}	
