<?php
/*
 Language template file for Ampjuke
 Updated: July 2008.
 By: Michael Iversen, http://www.ampjuke.org
 
 Instructions:
 1. This step (1) is _only_ necessary if you're creating a _new_ language:
 Copy this file ("template.php") to a new name within the 'lang' folder (f.ex. "ru.php").

 2. Open the (newly created) language file, and translate all "$ret's", so that - f.ex.:
	case "Track": $ret="Track"; break;
 becomes:
 	case "Track": $ret="whatever in your language"; break;

 3. Save the file.

 4. Should only be done if you're creating a new language:
 Edit "languages.txt" (it's also within the 'lang' folder), and add the line for the new language.
 The entries in "languages.txt" should be pretty self-explaining.

 5. *** PLEASE *** Upload your new/updated language file to the forum on www.ampjuke.org.
 As an alternative, send it as an email attachment to: michael {AT} ampjuke [DOT] org
 Optionally mention a reference to your name and website - I'll include a link in the next update
 of AmpJuke.

Thanks ! Your contribution is highly appreciated !

//Michael.
*/
switch ($key) {
	case "Track": $ret="Piesă"; break;
	case "Tracks": $ret="Piese"; break;
	case "Performer": $ret="Autor"; break;
	case "Performers": $ret="Autori"; break;
	case "Album": $ret="Album"; break;
	case "Albums": $ret="Albume"; break;
	case "Year": $ret="An"; break;
	case "Favorites": $ret="Favorite"; break;
	case "Favorite list": $ret="Listă favorite"; break;
	case "Queue": $ret="Listă redare"; break;
	case "The queue": $ret="Lista de redare"; break;
	case "Random play": $ret="Redare aleatorie"; break;
	case "Settings": $ret="Setări"; break;
	case "Search": $ret="Căutare"; break;
	case "Logout": $ret="Logout"; break;
	case "Admin's options": $ret="Opţiuni administrator"; break;
	case "Scan music..."; $ret="Scanează piesele..."; break;
	case "User adm..."; $ret="Administrare useri"; break;
	case "Configuration..."; $ret="Configuraţie..."; break;
	case "Clear cache": $ret="Şterge cache"; break;
	case "Welcome": $ret="Bun venit"; break;
	case "facts": $ret="Statistici "; break;
	case "Number of users": $ret="Număr de useri "; break;
	case "Number of albums": $ret="Număr de albume "; break;
	case "Number of performers": $ret="Număr de autori "; break;
	case "Number of tracks": $ret="Număr de piese "; break;
	case "Track list": $ret="Listă piese"; break;
	case "Download": $ret="Descarcă"; break;
	case "All": $ret="Toate"; break;
	case "Matches": $ret="Potriviri"; break;
	case "Jump to": $ret="Sari la"; break;
	case "Title": $ret="Titlu"; break;
	case "Duration": $ret="Durată"; break;
	case "Last played": $ret="Ultima redare"; break;
	case "Played": $ret="Redată"; break;
	case "pages in total": $ret="total pagini"; break;
	case "Play all tracks with": $ret="Redă toate piesele cu"; break;
	case "Play all tracks from": $ret="Redă toate piesele de la"; break;
	case "Queue all tracks with": $ret="Adaugă toate piesele cu"; break;
	case "Queue all tracks from": $ret="Adaugă toate piesele de la"; break;
	case "Appears on": $ret="Apare la"; break;
	case "Add all tracks to favorite list": $ret="Adaugă toate piesele la lista mea de favorite"; break;
	case "Add to favorite": $ret="Adaugă la favorite"; break;
	case "Add album to favorite list": $ret="Adaugă albumul la lista mea de favorite"; break;
	case "Search results": $ret="Rezultate căutare"; break;
	case "Personal settings": $ret="Setări personale"; break;
	case "Play tracks from": $ret="Redă piesele de la"; break;
	case "Play tracks from these year(s)": $ret="Redă piesele din anul(anii)";break;
	case "Number of tracks to select": $ret="Număr de piese selectate"; break;
	case "Remove duplicate entries": $ret="Şterge duplicate"; break;
	case "Play & display options": $ret="Opţiuni redare şi afişaj"; break;
	case "When a track is selected": $ret="Când o piesă este selectată"; break;
	case "Put it in the queue": $ret="Adaugă la şirul de redare"; break;
	case "Play it immediately": $ret="Redă imediat"; break;
	case "Number of items per page": $ret="Numărul de piese pe pagină"; break;
	case "Display when a track was played last time": $ret="Arată când a fost redată ultima dată o piesă"; break;
	case "Display how many times a track has been played": $ret="Arată de câte ori a fost redatâ o piesă"; break;
	case "Display ID numbers": $ret="Afişează numerele ID"; break;
	case "Show letters (the 'Jump to' option)": $ret="Afişează iniţiale (opţiunea 'Sari la')"; break;
	case "Create new": $ret="Nou"; break;
	case "Administrator": $ret="Administrator"; break;
	case "Last login": $ret="Ultimul login"; break;
	case "IP-address": $ret="adresă IP"; break;
	case "Username": $ret="Nume user"; break;
	case "Password": $ret="Parolă"; break;
	case "Other options": $ret="Alte opţiuni"; break;
	case "Language": $ret="Limbă"; break;
	case "Delete": $ret="Şterge"; break;
	case "Edit": $ret="Editează"; break;
	case "Copy the queue to the favorite list": $ret="Copiază şirul de redare în lista de favorite"; break;
	case "Select a favorite list": $ret="Selectează o listă de favorite"; break;
	case "There are no tracks in the queue": $ret="Nu există piese în şirul de redare"; break;
	case "Are you sure": $ret="Sunteţi sigur?"; break;
	case "Save & continue": $ret="Salvează şi continuă"; break;
	case "Yes": $ret="Da"; break;
	case "No": $ret="Nu"; break;
	case "Filter": $ret="Filtrează"; break;
	case "Tracks only on albums": $ret="Doar piese din albume"; break;
	case "Tracks not on any album": $ret="Piese din afara vreunui album"; break;
	case "Change password": $ret="Schimbă parola"; break;
	case "Leave field blank to keep current password": $ret="Lăsaţi câmpul liber pentru a păstra parola curentă"; break;
	case "Confirm new password": $ret="Confirmaţi parola nouă"; break;
	case "Confirm deletion": $ret="Confirmaţi ştergerea"; break;
	case "Display duration on tracks": $ret="Afişează durata pieselor"; break;
	case "Display totals": $ret="Afişează totaluri"; break;
	case "Automatic play": $ret="Redare automată"; break;
	case "Show download option": $ret="Arată opţiuni descărcare"; break;
	case "Lyrics": $ret="Versuri"; break;
	case "No lyrics found": $ret="Nu s-au găsit versuri"; break;
	case "Only works with": $ret="Funcţionează doar cu"; break;
	case "selected above": $ret="selectate mai sus"; break;
	case "Theme": $ret="Temă"; break;
	case "day": $ret="zi"; break;
	case "days": $ret="zile"; break;
	case "After login": $ret="După login"; break;
	case "After last track is played": $ret="După redarea ultimei piese"; break;	
	case "More information about": $ret="Mai multe informaţi despre"; break;
	case "Shared";$ret="Partajat"; break;
	case "Display shared favorites";$ret="Arată favrotite partajate"; break;
	case "Also use this setting later on": $ret="Foloseşte şi mai târziu aceste setări"; break;
	case "Give priority to": $ret="Oferă prioritate pentru"; break;
	case "Least played tracks": $ret="Cele mai puţin redate piese"; break;
	case "Most played tracks": $ret="Cele mai redate piese"; break;
	case "Tracks not played recently": $ret="Piesele care nu au fost redate recent"; break;
	case "Tracks played recently": $ret="Piesele redate recent"; break;
	case "Ask for name of favoritelist every time": $ret="Cere numele listei de favorite de fiecare dată"; break;    		case "Upload": $ret="Încarcă"; break;
	case "Upload music": $ret="Încarcă muzică"; break;
	case "Upload to folder": $ret="Încarcă în folderul"; break;
	case "If file exists, overwrite it": $ret="Suprascrie dacă există fişierul"; break;
	case "Display related performers": $ret="Arată autori înrudiţi"; break;
	case "Filename": $ret="Nume fişier"; break;
	case "Welcome page contents": $ret="Prima pagină conţine"; break;
	case "Number of items": $ret="Număr de obiecte"; break;
	case "Related performers": $ret="Autori înrudiţi"; break;
	case "Recently played tracks": $ret="Piese redate recent"; break;
	case "Recently added tracks": $ret="Piese adăugate recent"; break;
	case "Random tracks": $ret="Piese luate la întâmplare"; break;
	case "Recently added performers": $ret="Autori adăugaţi recent"; break;
	case "Random performers": $ret="Autori luaţi la întâmplare"; break;
	case "Recently added albums": $ret="Albume adăugate recent"; break;
	case "Random albums": $ret="Albume luate la întâmplare"; break;
	case "Display what is being played": $ret="Arată ce se redă"; break;
	case "Delay the update of what is being played": $ret="Întârzie afişarea pieselor care se redau"; break;
	case "Display help (links to the AmpJuke FAQ)": $ret="Afişează ajutor (link-uri la AmpJuke FAQ)"; break;
	case "Avoid duplicate entries": $ret="Evită intrări duble"; break;
	case "No icons": $ret="Fără iconiţe"; break;
	case "Transcode": $ret="Reencodează"; break;
	case "Submit streamed tracks to last.fm": $ret="Trimite piesele redate la last.fm"; break;
	case "last.fm username": $ret="user last.fm"; break;
	case "last.fm password": $ret="parola last.fm"; break;
	case "Refresh related performer(s) from last.fm": $ret="Improspătează autori înrudiţi de la last.fm"; break;
	case "Now playing": $ret="In redare acum"; break;
	case "Next track": $ret="Piesa următoare"; break;
	case "Display small images (albums/performers)": $ret="Arată imagini miniatură (albume/autori)"; break;
	case "Show upload option": $ret="Arată opţiune încărcare"; break;
	case "Leave blank to keep current password": $ret="Lăsaţi liber pentru a păstra parola curentă"; break;
	default: $ret='<b><i>'.$key.'</i></b>'; break;	
} // switch
?>