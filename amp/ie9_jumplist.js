// 2010-10-21: 
// This comes straight off from M$' website: http://msdn.microsoft.com/en-us/library/ms537509%28VS.85%29.aspx
// I've modified the example referred to in the link in order to suit hthe needs in AmpJuke.
// Introduced in AmpJuke version 0.8.4.
// Michael.
function getInternetExplorerVersion()
// Returns the version of Internet Explorer or a -1
// (indicating the use of another browser).
{
  var rv = -1; // Return value assumes failure.
  if (navigator.appName == 'Microsoft Internet Explorer')
  {
    var ua = navigator.userAgent;
    var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
    if (re.exec(ua) != null)
      rv = parseFloat( RegExp.$1 );
  }
  return rv;
}

//var msg = "You're not using Internet Explorer.";
var ver = getInternetExplorerVersion();

  if ( ver >= 8.0 )
  {
		window.external.msSiteModeCreateJumplist('AmpJuke');
//  msg = "OK";
		window.external.msSiteModeAddJumpListItem('Logout', './?what=logout', './ampjukeicons/logout.ico');  
		window.external.msSiteModeAddJumpListItem('Search', './?what=advsearch', './ampjukeicons/search.ico');
		window.external.msSiteModeAddJumpListItem('Settings', './?what=settings', './ampjukeicons/settings.ico');
		window.external.msSiteModeAddJumpListItem('Random play', './?what=random&act=setup', './ampjukeicons/random.ico');
		window.external.msSiteModeAddJumpListItem('Queue', './?what=queue', './ampjukeicons/queue.ico');
		window.external.msSiteModeAddJumpListItem('Favorites', './?what=favorite', './ampjukeicons/favorite.ico');
		window.external.msSiteModeAddJumpListItem('Years', './?what=year', './ampjukeicons/year.ico');
		window.external.msSiteModeAddJumpListItem('Performers/Artists', './?what=performer', './ampjukeicons/performer.ico');
		window.external.msSiteModeAddJumpListItem('Albums', './?what=album', './ampjukeicons/album.ico');
		window.external.msSiteModeAddJumpListItem('Tracks', './?what=track', './ampjukeicons/track.ico');
		window.external.msSiteModeAddJumpListItem('Welcome page', './?what=welcome', './favicon.ico');
  }
//  alert(msg);